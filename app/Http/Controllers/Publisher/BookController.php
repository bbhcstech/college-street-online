<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use App\Services\PublicImageStorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $books = $this->filteredQuery($request)->latest()->paginate($perPage)->withQueryString();
        return view('publisher.books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'activeCount' => auth()->user()->publisher->books()->where('status', 'active')->count(),
            'lowStockCount' => auth()->user()->publisher->books()->whereHas('inventory', fn ($query) => $query->whereColumn('quantity', '<=', 'low_stock_threshold'))->count(),
        ]);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $books = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('books.id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->orderBy('title')->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($books) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Title', 'ISBN', 'Author', 'Category', 'Price INR', 'Stock', 'Status']);
                foreach ($books as $book) fputcsv($output, [$book->title, $book->isbn, $book->author?->name, $book->category?->name, $book->price, $book->inventory?->quantity ?? 0, $book->status]);
                fclose($output);
            }, 'my-books-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('publisher.books.report', compact('books') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="my-books-'.now()->format('Y-m-d').'.xls"');
        }

        return view('publisher.books.report', compact('books') + ['mode' => $type]);
    }

    public function updateStatus(Request $request, Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $request->validate(['status' => 'required|in:active,inactive']);
        $book->update($data);
        return back()->with('success', 'Book status updated.');
    }

    private function filteredQuery(Request $request)
    {
        return auth()->user()->publisher->books()->with(['category', 'author', 'inventory'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $query->where(function ($search) use ($term) {
                    $search->where('title', 'like', "%{$term}%")
                        ->orWhere('isbn', 'like', "%{$term}%")
                        ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('category_id'), fn ($query) => $query->where('category_id', $request->query('category_id')))
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->stock === 'low', fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->whereColumn('quantity', '<=', 'low_stock_threshold')))
            ->when($request->stock === 'out', fn ($query) => $query->whereHas('inventory', fn ($inventory) => $inventory->where('quantity', 0)));
    }

    public function create()
    {
        return $this->formView(new Book());
    }

    /** FR-2: ISBN validated for format/uniqueness; cover image validated by MIME/size before storage. */
    public function store(Request $request, PublicImageStorageService $images)
    {
        $data = $this->validated($request);
        unset($data['cover_image']);
        $data['publisher_id'] = auth()->user()->publisher->id;
        $cover = null;
        if ($request->hasFile('cover_image')) {
            $cover = $images->storeBookCover($request->file('cover_image'));
            $data['cover_image_url'] = $cover;
        }

        try {
            DB::transaction(function () use ($data, $request) {
                $data['author_id'] = $this->resolveAuthorId($request, $data['author_id'] ?? null);
                unset($data['new_author_name']);
                $book = Book::create($data);
                Inventory::create(['book_id' => $book->id, 'quantity' => (int) $request->input('initial_stock', 0)]);
            });
        } catch (\Throwable $exception) {
            if ($cover) $images->delete($cover);
            throw $exception;
        }

        return redirect()->route('publisher.books.index')->with('success', 'Book added.');
    }

    public function edit(Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403); // ownership check, Section 4.2
        return $this->formView($book);
    }

    public function update(Request $request, Book $book, PublicImageStorageService $images)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $this->validated($request, $book->id);
        unset($data['cover_image']);
        $oldUrl = $book->cover_image_url;
        $cover = null;

        if ($request->hasFile('cover_image')) {
            $cover = $images->storeBookCover($request->file('cover_image'));
            $data['cover_image_url'] = $cover;
        }

        try {
            DB::transaction(function () use ($request, $book, $data) {
                $data['author_id'] = $this->resolveAuthorId($request, $data['author_id'] ?? null);
                unset($data['new_author_name']);
                $book->update($data);
            });
        } catch (\Throwable $exception) {
            if ($cover) $images->delete($cover);
            throw $exception;
        }

        if ($cover) {
            try {
                $images->delete($oldUrl);
            } catch (\Throwable $exception) {
                report($exception);
            }
        }

        return redirect()->route('publisher.books.index')->with('success', 'Book updated.');
    }

    /** FR-2: soft-delete to preserve historical order-line integrity, never hard-delete. */
    public function destroy(Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $book->delete();
        return back()->with('success', 'Book removed from catalogue.');
    }

    protected function validated(Request $request, ?int $bookId = null): array
    {
        return $request->validate([
            'title' => 'required|string|max:250',
            'isbn' => 'required|string|max:20|unique:books,isbn' . ($bookId ? ",{$bookId}" : ''),
            'category_id' => 'nullable|exists:categories,id',
            'author_id' => 'nullable|required_without:new_author_name|exists:authors,id',
            'new_author_name' => 'nullable|required_without:author_id|string|max:150',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'status' => 'nullable|in:active,inactive',
        ]);
    }

    protected function resolveAuthorId(Request $request, ?int $authorId): int
    {
        $newAuthorName = trim((string) $request->input('new_author_name'));
        if ($newAuthorName === '') return (int) $authorId;

        $author = Author::whereRaw('LOWER(name) = ?', [mb_strtolower($newAuthorName)])->first();
        return ($author ?? Author::create(['name' => $newAuthorName]))->id;
    }

    protected function formView(Book $book)
    {
        return view('publisher.books.form', [
            'book' => $book,
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }
}
