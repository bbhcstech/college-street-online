<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookReview;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 15;

        $reviews = BookReview::with(['book', 'customer'])
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $query->where(function ($q) use ($term) {
                    $q->where('review', 'like', "%{$term}%")
                      ->orWhereHas('book', fn ($b) => $b->where('title', 'like', "%{$term}%"))
                      ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->rating))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        $totalReviews = BookReview::count();
        $avgRating = BookReview::avg('rating') ? number_format(BookReview::avg('rating'), 1) : '0.0';
        $fiveStarCount = BookReview::where('rating', 5)->count();
        $lowRatingCount = BookReview::where('rating', '<=', 2)->count();

        return view('admin.reviews.index', compact('reviews', 'totalReviews', 'avgRating', 'fiveStarCount', 'lowRatingCount'));
    }

    public function destroy(BookReview $review)
    {
        $review->delete();

        return back()->with('success', 'Customer review removed successfully.');
    }
}

