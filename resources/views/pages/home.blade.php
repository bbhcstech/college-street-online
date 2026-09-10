@extends('layouts.app')
@section('title', 'College Street Online | Books, Delivered From Kolkata\'s Book Market')
@section('content')
    @php($heroBooks = $newArrivals->concat($bestsellers)->filter(fn ($book) => $book->cover_url)->unique('id')->take(3))
    <section class="hero" id="home">
        <div class="hero-blobs" aria-hidden="true">
            <div class="blob blob-1"></div>
            <div class="blob blob-2"></div>
            <div class="blob blob-3"></div>
        </div>
        <div class="container">
            <div class="hero-grid">
                <div class="reveal in-view">
                    <span class="eyebrow"><span class="dot"></span> Kolkata's Legendary Book Market, Online</span>
                    <h1>Every Book, <em>Every Reader</em>, One Street</h1>
                    <p class="lead">From academic textbooks to Bengali literature, College Street Online connects readers
                        directly with publishers &mdash; genuine books, fair prices, doorstep delivery.</p>
                    <div class="hero-cta">
                        <a href="{{ route('books.index') }}" class="btn btn-primary">Browse Books</a>
                        <a href="{{ route('bulk-orders') }}" class="btn btn-outline">Bulk / Institutional Orders</a>
                    </div>
                </div>
                <div class="hero-visual reveal in-view">
                    <div class="frame hero-book-showcase">
                        <div class="hero-book-covers">
                            @forelse($heroBooks as $book)
                                <a href="{{ route('books.show', $book) }}" class="hero-book-cover">
                                    <img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover">
                                </a>
                            @empty
                                <div class="hero-book-fallback">Books for every kind of reader</div>
                            @endforelse
                        </div>
                        <p>Fresh picks from College Street's publishers</p>
                    </div>
                </div>
            </div>
            <div class="stats-strip">
                <div class="stat-item">
                    <div class="stat-number">12,000+</div>
                    <div class="stat-label">Titles Listed</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">180+</div>
                    <div class="stat-label">Publishers</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">30,000+</div>
                    <div class="stat-label">Orders Delivered</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">4.7★</div>
                    <div class="stat-label">Average Rating</div>
                </div>
            </div>
        </div>
    </section>

    @if($deals->isNotEmpty())
        <section class="section deals-section">
            <div class="container">
                <div class="deals-heading">
                    <div>
                        <span class="eyebrow"><span class="dot"></span> Limited-time savings</span>
                        <h2>Today's Deals</h2>
                    </div>
                    <a href="{{ route('books.index') }}" class="btn btn-outline">View All Books</a>
                </div>
                <div class="grid grid-4 deals-grid">
                    @foreach($deals as $book)
                        @include('partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    <section class="section" id="new-arrivals">
        <div class="container">
            <div class="section-head reveal"><span class="eyebrow"><span class="dot"></span> New Arrivals</span>
                <h2>Fresh Off the Press</h2>
            </div>
            <div class="grid grid-4">
                @forelse($newArrivals as $book)
                    @include('partials.book-card', ['book' => $book])
                @empty
                    <p>No books yet — check back soon.</p>
                @endforelse
            </div>
            <div class="text-center" style="margin-top:36px;"><a href="{{ route('books.index') }}"
                    class="btn btn-outline">Browse All Books</a></div>
        </div>
    </section>

    @auth
        @if(auth()->user()->isCustomer() && $recommendedBooks->isNotEmpty())
            <section class="section section-alt">
                <div class="container">
                    <div class="section-head reveal">
                        <span class="eyebrow"><span class="dot"></span> Picked for you</span>
                        <h2>Recommended for You</h2>
                        <p>Books related to categories you have previously ordered.</p>
                    </div>
                    <div class="grid grid-4">
                        @foreach($recommendedBooks as $book)
                            @include('partials.book-card', ['book' => $book])
                        @endforeach
                    </div>
                </div>
            </section>
        @endif
    @endauth

    <section class="section section-alt" id="bestsellers">
        <div class="container">
            <div class="section-head reveal"><span class="eyebrow"><span class="dot"></span> Bestsellers</span>
                <h2>Reader Favourites</h2>
                <p>Most purchased books from successfully delivered orders.</p>
            </div>
            <div class="grid grid-4">
                @forelse($bestsellers as $book)
                    @include('partials.book-card', ['book' => $book])
                @empty
                    <p>No books yet.</p>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="section-head reveal">
                <span class="eyebrow"><span class="dot"></span> The College Street Promise</span>
                <h2>Why College Street Online?</h2>
                <p>Trusted books and dependable service, directly from Kolkata's legendary book market.</p>
            </div>
            <div class="bento-grid">
                <article class="bento-card reveal">
                    <span class="index-num">01</span>
                    <h3>Genuine Books</h3>
                    <p>Original editions sourced from trusted publishers and verified sellers.</p>
                </article>
                <article class="bento-card featured reveal">
                    <span class="index-num">02</span>
                    <h3>Fair Prices</h3>
                    <p>Clear pricing and meaningful discounts without unexpected charges.</p>
                </article>
                <article class="bento-card reveal">
                    <span class="index-num">03</span>
                    <h3>Fast Delivery</h3>
                    <p>Reliable doorstep delivery that brings College Street directly to you.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="cta-banner reveal">
                <h2>Selling Books? Join College Street Online</h2>
                <p>List your catalogue, manage inventory, and reach thousands of readers across India.</p><a
                    href="{{ route('publisher.login') }}" class="btn btn-gold" style="margin-top:12px;">Become a
                    Publisher</a>
            </div>
        </div>
    </section>
@endsection
