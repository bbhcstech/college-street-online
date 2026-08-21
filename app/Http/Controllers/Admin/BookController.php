<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::with(['publisher', 'category', 'author'])->latest()->paginate(20);
        return view('admin.books.index', compact('books'));
    }

    public function destroy(Book $book)
    {
        $book->delete(); // soft delete, per FR-2
        return back()->with('success', 'Book removed.');
    }
}
