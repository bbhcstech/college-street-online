<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use App\Models\OrderItem;
use App\Models\Publisher;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $books = $this->filteredQuery($request)
            ->latest()->paginate($perPage)->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('business_name')->get(),
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
                fputcsv($output, ['Title', 'ISBN', 'Author', 'Publisher', 'Category', 'Price INR', 'Stock', 'Status']);
                foreach ($books as $book) fputcsv($output, [$book->title, $book->isbn, $book->author?->name, $book->publisher?->business_name, $book->category?->name, $book->price, $book->inventory?->quantity ?? 0, $book->trashed() ? 'Removed' : ucfirst($book->status)]);
                fclose($output);
            }, 'books-' . now()->format('Y-m-d') . '.csv');
        }

        if ($type === 'excel') {
            return response()->view('admin.books.report', compact('books') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="books-' . now()->format('Y-m-d') . '.xls"');
        }

        return view('admin.books.report', compact('books') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        return Book::query()->with(['publisher', 'category', 'author', 'inventory'])
            ->when($request->query('q'), function ($query, $term) {
                $query->where(function ($search) use ($term) {
                    $search->where('title', 'like', "%{$term}%")
                        ->orWhere('isbn', 'like', "%{$term}%")
                        ->orWhereHas('author', fn ($author) => $author->where('name', 'like', "%{$term}%"))
                        ->orWhereHas('publisher', fn ($publisher) => $publisher->where('business_name', 'like', "%{$term}%"));
                });
            })
            ->when($request->query('status'), fn ($query, $status) => $query->where('status', $status))
            ->when($request->query('category_id'), fn ($query, $category) => $query->where('category_id', $category))
            ->when($request->query('publisher_id'), fn ($query, $publisher) => $query->where('publisher_id', $publisher))
            ->when($request->query('deleted') === 'only', fn ($query) => $query->onlyTrashed())
            ->when($request->query('deleted') === 'with', fn ($query) => $query->withTrashed());
    }

    public function create()
    {
        return view('admin.books.create', [
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::where('approval_status', 'approved')->orderBy('business_name')->get(),
        ]);
    }

    public function store(Request $request, CloudinaryImageService $images)
    {
        $data = $request->validate([
            'publisher_id' => ['required', Rule::exists('publishers', 'id')->where('approval_status', 'approved')],
            'title' => 'required|string|max:250',
            'isbn' => 'required|string|max:20|unique:books,isbn',
            'author_id' => 'nullable|required_without:new_author_name|exists:authors,id',
            'new_author_name' => 'nullable|required_without:author_id|string|max:150',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
            'initial_stock' => 'required|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $cover = null;
        if ($request->hasFile('cover_image')) {
            $cover = $images->uploadBookCover($request->file('cover_image'));
            $data['cover_image_url'] = $cover['url'];
            $data['cover_image_public_id'] = $cover['public_id'];
        }

        try {
            DB::transaction(function () use ($request, $data) {
                $authorId = $this->resolveAuthorId($request, $data['author_id'] ?? null);
                $initialStock = (int) $data['initial_stock'];
                unset($data['author_id'], $data['new_author_name'], $data['initial_stock'], $data['cover_image']);
                $data['author_id'] = $authorId;
                $book = Book::create($data);
                Inventory::create(['book_id' => $book->id, 'quantity' => $initialStock]);
            });
        } catch (\Throwable $exception) {
            if ($cover) $images->delete($cover['public_id']);
            throw $exception;
        }

        return redirect()->route('admin.books.index')->with('success', 'Book created.');
    }

    public function edit(Book $book)
    {
        return view('admin.books.edit', [
            'book' => $book,
            'authors' => Author::orderBy('name')->get(),
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $data = $request->validate([
            'title' => 'required|string|max:250',
            'isbn' => ['required', 'string', 'max:20', Rule::unique('books', 'isbn')->ignore($book->id)],
            'author_id' => 'required|exists:authors,id',
            'category_id' => 'nullable|exists:categories,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $book->update($data);
        return redirect()->route('admin.books.index')->with('success', 'Book updated.');
    }

    public function updateStatus(Request $request, Book $book)
    {
        $data = $request->validate(['status' => 'required|in:active,inactive']);
        $book->update($data);
        return back()->with('success', 'Book status updated.');
    }

    public function destroy(Book $book)
    {
        $book->delete();
        return back()->with('success', 'Book removed from the catalogue.');
    }

    public function restore(int $id)
    {
        $book = Book::onlyTrashed()->findOrFail($id);
        $book->restore();
        return back()->with('success', 'Book restored.');
    }

    public function forceDestroy(int $id, CloudinaryImageService $images)
    {
        $book = Book::onlyTrashed()->findOrFail($id);
        $ongoingStatuses = [
            'pending_payment', 'confirmed', 'processing', 'packed', 'shipped',
            'delivered', 'return_requested',
        ];

        $hasOngoingOrder = OrderItem::where('book_id', $book->id)
            ->whereHas('order', fn ($order) => $order->whereIn('status', $ongoingStatuses))
            ->exists();

        if ($hasOngoingOrder) {
            return back()->withErrors(['book' => 'This book cannot be permanently deleted because it has an ongoing order.']);
        }

        if (OrderItem::where('book_id', $book->id)->exists()) {
            return back()->withErrors(['book' => 'This book cannot be permanently deleted because order history must be preserved.']);
        }

        $coverUrl = $book->cover_image_url;
        $publicId = $book->cover_image_public_id;
        $book->forceDelete();

        try {
            if ($publicId) {
                $images->delete($publicId);
            } elseif ($coverUrl && ! str_starts_with($coverUrl, 'http')) {
                Storage::disk('public')->delete($coverUrl);
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        return back()->with('success', 'Book permanently deleted.');
    }

    protected function resolveAuthorId(Request $request, ?int $authorId): int
    {
        $newAuthorName = trim((string) $request->input('new_author_name'));
        if ($newAuthorName === '') return (int) $authorId;

        $author = Author::whereRaw('LOWER(name) = ?', [mb_strtolower($newAuthorName)])->first();
        return ($author ?? Author::create(['name' => $newAuthorName]))->id;
    }
}
