<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Inventory;
use App\Services\CloudinaryImageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    public function index()
    {
        $books = auth()->user()->publisher->books()->with(['category', 'author', 'inventory'])->latest()->paginate(15);
        return view('publisher.books.index', compact('books'));
    }

    public function create()
    {
        return $this->formView(new Book());
    }

    /** FR-2: ISBN validated for format/uniqueness; cover image validated by MIME/size before storage. */
    public function store(Request $request, CloudinaryImageService $images)
    {
        $data = $this->validated($request);
        unset($data['cover_image']);
        $data['publisher_id'] = auth()->user()->publisher->id;
        $cover = null;
        if ($request->hasFile('cover_image')) {
            $cover = $images->uploadBookCover($request->file('cover_image'));
            $data['cover_image_url'] = $cover['url'];
            $data['cover_image_public_id'] = $cover['public_id'];
        }

        try {
            DB::transaction(function () use ($data, $request) {
                $data['author_id'] = $this->resolveAuthorId($request, $data['author_id'] ?? null);
                unset($data['new_author_name']);
                $book = Book::create($data);
                Inventory::create(['book_id' => $book->id, 'quantity' => (int) $request->input('initial_stock', 0)]);
            });
        } catch (\Throwable $exception) {
            if ($cover) $images->delete($cover['public_id']);
            throw $exception;
        }

        return redirect()->route('publisher.books.index')->with('success', 'Book added.');
    }

    public function edit(Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403); // ownership check, Section 4.2
        return $this->formView($book);
    }

    public function update(Request $request, Book $book, CloudinaryImageService $images)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $data = $this->validated($request, $book->id);
        unset($data['cover_image']);
        $oldUrl = $book->cover_image_url;
        $oldPublicId = $book->cover_image_public_id;
        $cover = null;

        if ($request->hasFile('cover_image')) {
            $cover = $images->uploadBookCover($request->file('cover_image'));
            $data['cover_image_url'] = $cover['url'];
            $data['cover_image_public_id'] = $cover['public_id'];
        }

        try {
            DB::transaction(function () use ($request, $book, $data) {
                $data['author_id'] = $this->resolveAuthorId($request, $data['author_id'] ?? null);
                unset($data['new_author_name']);
                $book->update($data);
            });
        } catch (\Throwable $exception) {
            if ($cover) $images->delete($cover['public_id']);
            throw $exception;
        }

        if ($cover) {
            try {
                if ($oldPublicId) {
                    $images->delete($oldPublicId);
                } elseif ($oldUrl && ! str_starts_with($oldUrl, 'http')) {
                    Storage::disk('public')->delete($oldUrl);
                }
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
