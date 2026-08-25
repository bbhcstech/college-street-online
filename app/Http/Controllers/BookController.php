<?php
namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\Category;
use App\Models\Order;
use Illuminate\Http\Request;

class BookController extends Controller
{
    /** FR-4: keyword + Bengali-transliteration search, with category/price filters (planned improvements from the SRS). */
    public function index(Request $request)
    {
        $books = Book::active()
            ->with(['author', 'category'])
            ->search($request->query('q'))
            ->when($request->query('category'), fn ($q, $cat) => $q->whereHas('category', fn ($c) => $c->where('slug', $cat)))
            ->when($request->query('min_price'), fn ($q, $v) => $q->where('price', '>=', $v))
            ->when($request->query('max_price'), fn ($q, $v) => $q->where('price', '<=', $v))
            ->orderBy('title')
            ->paginate(16)
            ->withQueryString();

        return view('pages.books-index', [
            'books' => $books,
            'categories' => Category::orderBy('name')->get(),
        ]);
    }

    public function show(Book $book)
    {
        $book->load(['author', 'category', 'publisher', 'inventory', 'reviews.customer']);
        $related = Book::active()->where('category_id', $book->category_id)->where('id', '!=', $book->id)->limit(4)->get();
        $eligibleOrder = null;

        if (auth()->check() && auth()->user()->isCustomer()) {
            $reviewedOrderIds = BookReview::where('book_id', $book->id)
                ->where('customer_id', auth()->id())
                ->pluck('order_id');

            $eligibleOrder = Order::where('customer_id', auth()->id())
                ->whereIn('status', ['delivered', 'completed'])
                ->whereHas('items', fn ($query) => $query->where('book_id', $book->id))
                ->whereNotIn('id', $reviewedOrderIds)
                ->latest()
                ->first();
        }

        return view('pages.book-detail', compact('book', 'related', 'eligibleOrder'));
    }
}
