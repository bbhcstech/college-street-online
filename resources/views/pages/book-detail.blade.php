@extends('layouts.app')
@section('title', $book->title . ' | College Street Online')
@section('content')
@php
    $averageRating = $book->reviews->avg('rating');
    $reviewCount = $book->reviews->count();
    $stock = $book->inventory?->quantity ?? 0;
    $discount = $book->mrp && $book->mrp > $book->price
        ? (int) round((($book->mrp - $book->price) / $book->mrp) * 100)
        : null;
@endphp

<div class="container" style="padding-top:24px;">
    <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><a href="{{ route('books.index') }}">Browse Books</a><span class="sep">/</span><span class="current">{{ $book->title }}</span></div>
</div>

<section class="section book-detail-section">
    <div class="container">
        <div class="product-layout">
            <div class="product-visual reveal in-view">
                <div class="product-cover-frame">
                    @if($book->cover_url)
                        <div class="book-cover book-cover-uploaded"><img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="book-cover-image"></div>
                    @else
                        <div class="book-cover"><div class="spine"></div><span class="title-mark">{{ $book->title }}</span></div>
                    @endif
                </div>
                <div class="product-visual-note"><span>&#10003;</span> Genuine listing from an approved publisher</div>
            </div>

            <div class="product-information reveal in-view">
                <div class="product-heading-row"><span class="badge-tag-outline">{{ $book->category->name ?? 'General' }}</span>@if($discount)<span class="product-discount">{{ $discount }}% OFF</span>@endif</div>
                <h1>{{ $book->title }}</h1>
                <p class="product-byline">by <strong>{{ $book->author->name ?? 'Unknown author' }}</strong><span></span>Published by <strong>{{ $book->publisher->business_name ?? 'Independent publisher' }}</strong></p>

                <div class="product-rating-row">
                    <span class="rating-chip">{{ $averageRating ? number_format($averageRating, 1) : 'New' }} @if($averageRating)&#9733;@endif</span>
                    <a href="#reader-reviews">{{ $reviewCount }} {{ Str::plural('verified review', $reviewCount) }}</a>
                    <span>ISBN {{ $book->isbn }}</span>
                </div>

                <div class="product-price-block">
                    <span class="product-price">&#8377;{{ number_format($book->price, 0) }}</span>
                    @if($book->mrp)<span class="product-mrp">M.R.P. &#8377;{{ number_format($book->mrp, 0) }}</span>@endif
                    @if($discount)<span class="product-saving">You save &#8377;{{ number_format($book->mrp - $book->price, 0) }}</span>@endif
                    <small>Inclusive of applicable taxes</small>
                </div>

                <div class="product-stock-row">
                    @if($stock <= 0)
                        <span class="stock-pill low">&#9679; Currently unavailable</span>
                    @elseif($book->inventory->isLowStock())
                        <span class="stock-pill low">&#9679; Only {{ $stock }} left</span>
                    @else
                        <span class="stock-pill in">&#9679; In stock</span>
                    @endif
                </div>

                <div class="product-description"><h2>About this book</h2><p>{{ $book->description ?? 'A detailed description for this title will be available soon.' }}</p></div>

                <div class="product-purchase-box">
                    <form method="POST" action="{{ route('cart.store') }}" class="product-cart-form">
                        @csrf
                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                        <label for="book-quantity">Quantity</label>
                        <input id="book-quantity" type="number" name="quantity" value="1" min="1" @if($stock > 0) max="{{ $stock }}" @endif class="form-control" @disabled($stock <= 0)>
                        <button type="submit" class="btn btn-primary" @disabled($stock <= 0)>{{ $stock > 0 ? 'Add to Cart' : 'Out of Stock' }}</button>
                    </form>
                    @guest<p class="purchase-login-note">You can view every detail as a guest. <a href="{{ route('account.login') }}">Log in to purchase</a>.</p>@endguest
                </div>

                <div class="product-assurances">
                    <div><span>&#8635;</span><strong>Easy support</strong><small>Help with your order</small></div>
                    <div><span>&#9635;</span><strong>Secure checkout</strong><small>Protected details</small></div>
                    <div><span>&#10003;</span><strong>Verified seller</strong><small>Approved publisher</small></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="section section-alt" id="reader-reviews">
    <div class="container" style="max-width:900px;">
        <div class="section-head">
            <div><span class="eyebrow"><span class="dot"></span> Reader Feedback</span><h2>Reviews &amp; Ratings</h2></div>
            <div class="review-average"><strong>{!! $averageRating ? number_format($averageRating, 1) : '&mdash;' !!}</strong><span class="review-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span><small>{{ $reviewCount }} {{ Str::plural('review', $reviewCount) }}</small></div>
        </div>

        @if($eligibleOrder)
            <div class="card review-form-card">
                <h3 style="margin-top:0;">Review your purchase</h3>
                <form method="POST" action="{{ route('books.reviews.store', $book) }}">
                    @csrf
                    <input type="hidden" name="order_id" value="{{ $eligibleOrder->id }}">
                    <div class="form-group"><label>Rating</label><select name="rating" required class="form-control"><option value="">Choose a rating</option>@foreach([5 => '5 - Excellent', 4 => '4 - Good', 3 => '3 - Average', 2 => '2 - Fair', 1 => '1 - Poor'] as $value => $label)<option value="{{ $value }}" {{ old('rating') == $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
                    <div class="form-group"><label>Review (optional)</label><textarea name="review" maxlength="2000" class="form-control" style="min-height:100px;">{{ old('review') }}</textarea></div>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </form>
            </div>
        @endif

        <div class="review-list">
            @forelse($book->reviews->sortByDesc('created_at') as $review)
                <article class="card review-card">
                    <div class="review-card-head"><div><strong>{{ $review->customer->name }}</strong><small>Verified purchase</small></div><span class="review-stars">{!! str_repeat('&#9733;', $review->rating) !!}{!! str_repeat('&#9734;', 5 - $review->rating) !!}</span></div>
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
<section class="section">
    <div class="container">
        <div class="section-head reveal"><div><span class="eyebrow"><span class="dot"></span> You May Also Like</span><h2>Related Titles</h2></div><a href="{{ route('books.index', ['category' => $book->category?->slug]) }}">View category &rarr;</a></div>
        <div class="grid grid-4">@foreach($related as $r) @include('partials.book-card', ['book' => $r]) @endforeach</div>
    </div>
</section>
@endif
@endsection
