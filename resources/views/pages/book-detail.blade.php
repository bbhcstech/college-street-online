@extends('layouts.app')
@section('title', $book->title . ' | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;">
    <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><a href="{{ route('books.index') }}">Browse Books</a><span class="sep">/</span><span class="current">{{ $book->title }}</span></div>
</div>
<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="grid grid-2" style="gap:48px;align-items:start;">
            <div class="reveal in-view">
                <div class="book-cover" style="border-radius:var(--radius-lg);aspect-ratio:3/4;max-width:380px;"><div class="spine"></div><span class="title-mark" style="font-size:1.4rem;">{{ $book->title }}</span></div>
            </div>
            <div class="reveal in-view">
                <span class="badge-tag-outline">{{ $book->category->name ?? 'General' }}</span>
                <h1 style="margin-top:10px;">{{ $book->title }}</h1>
                <p style="color:var(--text-secondary);">by <strong>{{ $book->author->name ?? 'Unknown' }}</strong> &middot; Publisher: {{ $book->publisher->business_name ?? '—' }}</p>
                <div class="price-row" style="margin:16px 0;"><span class="price" style="font-size:1.8rem;">&#8377;{{ number_format($book->price, 0) }}</span>@if($book->mrp)<span class="price-strike">&#8377;{{ number_format($book->mrp, 0) }}</span>@endif</div>
                @if($book->inventory)
                    @if($book->inventory->isLowStock())
                        <span class="stock-pill low">&#9679; Low Stock &mdash; {{ $book->inventory->quantity }} left</span>
                    @else
                        <span class="stock-pill in">&#9679; In Stock</span>
                    @endif
                @endif
                <p style="margin-top:20px;">{{ $book->description ?? 'No description available yet.' }} ISBN: {{ $book->isbn }}</p>
                <form method="POST" action="{{ route('cart.store') }}" class="flex gap-2" style="margin-top:24px;flex-wrap:wrap;">
                    @csrf
                    <input type="hidden" name="book_id" value="{{ $book->id }}">
                    <input type="number" name="quantity" value="1" min="1" class="form-control" style="width:80px;">
                    <button type="submit" class="btn btn-primary">Add to Cart</button>
                </form>
            </div>
        </div>
    </div>
</section>
<section class="section section-alt">
    <div class="container" style="max-width:900px;">
        @php($averageRating = $book->reviews->avg('rating'))
        <div class="section-head">
            <div>
                <span class="eyebrow"><span class="dot"></span> Reader Feedback</span>
                <h2>Reviews &amp; Ratings</h2>
            </div>
            <div class="review-average">
                <strong>{{ $averageRating ? number_format($averageRating, 1) : '—' }}</strong>
                <span class="review-stars">★★★★★</span>
                <small>{{ $book->reviews->count() }} {{ Str::plural('review', $book->reviews->count()) }}</small>
            </div>
        </div>

        @if($eligibleOrder)
            <div class="card review-form-card">
                <h3 style="margin-top:0;">Review your purchase</h3>
                <form method="POST" action="{{ route('books.reviews.store', $book) }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $eligibleOrder->id }}">
                    <div class="form-group">
                        <label>Rating</label>
                        <select name="rating" required class="form-control">
                            <option value="">Choose a rating</option>
                            @foreach([5 => '5 — Excellent', 4 => '4 — Good', 3 => '3 — Average', 2 => '2 — Fair', 1 => '1 — Poor'] as $value => $label)
                                <option value="{{ $value }}" {{ old('rating') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group"><label>Review (optional)</label><textarea name="review" maxlength="2000" class="form-control" style="min-height:100px;">{{ old('review') }}</textarea></div>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        @endif

        <div class="review-list">
            @forelse($book->reviews->sortByDesc('created_at') as $review)
                <article class="card review-card">
                    <div class="review-card-head">
                        <div><strong>{{ $review->customer->name }}</strong><small>Verified purchase</small></div>
                        <span class="review-stars">{{ str_repeat('★', $review->rating) }}{{ str_repeat('☆', 5 - $review->rating) }}</span>
                    </div>
                    @if($review->review)<p>{{ $review->review }}</p>@endif
                    <small>{{ $review->created_at->format('d M Y') }}</small>
                </article>
            @empty
                <p style="color:var(--text-secondary);">No reviews yet. Delivered customers can be the first to review this book.</p>
            @endforelse
        </div>
    </div>
</section>
@if($related->count())
<section class="section section-alt">
    <div class="container">
        <div class="section-head reveal"><span class="eyebrow"><span class="dot"></span> You May Also Like</span><h2>Related Titles</h2></div>
        <div class="grid grid-4">@foreach($related as $r) @include('partials.book-card', ['book' => $r]) @endforeach</div>
    </div>
</section>
@endif
@endsection
