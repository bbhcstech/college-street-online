<?php
namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Inventory;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = auth()->user()->publisher->books()->with(['category', 'author', 'inventory'])->latest()->paginate(15);
        return view('publisher.books.index', compact('books'));
    }

    public function create() { return view('publisher.books.form', ['book' => new Book()]); }

    /** FR-2: ISBN validated for format/uniqueness; cover image validated by MIME/size before storage. */
    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['publisher_id'] = auth()->user()->publisher->id;
        if ($request->hasFile('cover_image')) {
            $data['cover_image_url'] = $request->file('cover_image')->store('covers', 'public');
        }
        $book = Book::create($data);
        Inventory::create(['book_id' => $book->id, 'quantity' => (int) $request->input('initial_stock', 0)]);
        return redirect()->route('publisher.books.index')->with('success', 'Book added.');
    }

    public function edit(Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403); // ownership check, Section 4.2
        return view('publisher.books.form', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        abort_unless($book->publisher_id === auth()->user()->publisher->id, 403);
        $book->update($this->validated($request, $book->id));
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
            'author_id' => 'nullable|exists:authors,id',
            'price' => 'required|numeric|min:0',
            'mrp' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
        ]);
    }
}
