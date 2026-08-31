@extends('layouts.app')
@section('title', 'College Street Online | Books, Delivered From Kolkata\'s Book Market')
@section('content')
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
                    <div class="frame" style="transform:rotate(2deg);">
                        <div
                            style="aspect-ratio:4/3;background:linear-gradient(160deg,var(--brand-primary),var(--brand-secondary));border-radius:var(--radius-md);display:flex;align-items:center;justify-content:center;color:#fff;font-family:var(--font-display);font-style:italic;font-size:1.3rem;text-align:center;padding:30px;">
                            &ldquo;College Street Online &mdash; Where every page finds its reader.&rdquo;
                        </div>
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

    <section class="section section-alt" id="bestsellers">
        <div class="container">
            <div class="section-head reveal"><span class="eyebrow"><span class="dot"></span> Bestsellers</span>
                <h2>Reader Favourites</h2>
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
            <div class="cta-banner reveal">
                <h2>Selling Books? Join College Street Online</h2>
                <p>List your catalogue, manage inventory, and reach thousands of readers across India.</p><a
                    href="{{ route('publisher.login') }}" class="btn btn-gold" style="margin-top:12px;">Become a
                    Publisher</a>
            </div>
        </div>
    </section>
@endsection