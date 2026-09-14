<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReview;
use App\Models\Order;
use App\Models\ReviewReport;
use Illuminate\Http\Request;

class BookReviewController extends Controller
{
    public function store(Request $request, Book $book)
    {
        $data = $request->validate([
            'order_id' => 'required|integer',
            'rating' => 'required|integer|between:1,5',
            'review' => 'nullable|string|max:2000',
            'images' => 'nullable|array|max:4',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:5120',
        ]);

        $order = Order::whereKey($data['order_id'])
            ->where('customer_id', auth()->id())
            ->whereIn('status', ['delivered', 'completed'])
            ->whereHas('items', fn ($query) => $query->where('book_id', $book->id))
            ->first();

        if (! $order) {
            return back()->withErrors(['review' => 'Only delivered purchases can be reviewed.']);
        }

        $identity = ['book_id' => $book->id, 'customer_id' => auth()->id(), 'order_id' => $order->id];
        if (BookReview::where($identity)->exists()) {
            return back()->withErrors(['review' => 'You already reviewed this purchase.']);
        }

        $images = collect($request->file('images', []))
            ->map(fn ($image) => $image->store('review-images', 'public'))->all();

        BookReview::create($identity + [
            'rating' => $data['rating'],
            'review' => $data['review'] ?? null,
            'images' => $images ?: null,
        ]);

        return back()->with('success', 'Thank you for reviewing this book.');
    }

    public function report(Request $request, BookReview $review)
    {
        abort_if($review->customer_id === auth()->id(), 403);
        $data = $request->validate(['reason' => 'required|string|max:500']);
        ReviewReport::firstOrCreate(
            ['book_review_id' => $review->id, 'reporter_id' => auth()->id()],
            ['reason' => $data['reason']]
        );

        return back()->with('success', 'The review was reported for moderation.');
    }
}
