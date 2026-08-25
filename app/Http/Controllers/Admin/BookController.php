<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::query()
            ->with(['publisher', 'category', 'author', 'inventory'])
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
            ->when($request->query('deleted') === 'with', fn ($query) => $query->withTrashed())
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.books.index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
            'publishers' => Publisher::orderBy('business_name')->get(),
        ]);
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
}
