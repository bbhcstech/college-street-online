<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\Order;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:2000',
        ]);

        $order = Order::whereKey($data['order_id'])
            ->where('customer_id', auth()->id())
            ->whereIn('status', ['delivered', 'completed'])
            ->whereHas('items', fn ($query) => $query->where('book_id', $book->id))
            ->first();

        if (! $order) {
            return back()->withErrors(['review' => 'Only delivered purchases can be reviewed.']);
        }

        $review = BookReview::firstOrCreate(
            [
                'book_id' => $book->id,
                'customer_id' => auth()->id(),
                'order_id' => $order->id,
            ],
            [
                'rating' => $data['rating'],
                'review' => $data['review'] ?? null,
            ]
        );

        if (! $review->wasRecentlyCreated) {
            return back()->withErrors(['review' => 'You already reviewed this purchase.']);
        }

        return back()->with('success', 'Thank you for reviewing this book.');
    }
}
