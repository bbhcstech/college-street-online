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
@if($related->count())
<section class="section section-alt">
    <div class="container">
        <div class="section-head reveal"><span class="eyebrow"><span class="dot"></span> You May Also Like</span><h2>Related Titles</h2></div>
        <div class="grid grid-4">@foreach($related as $r) @include('partials.book-card', ['book' => $r]) @endforeach</div>
    </div>
</section>
@endif
@endsection
