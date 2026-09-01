@extends('layouts.app')
@section('title', 'Browse Books | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                class="current">Browse Books</span></div>
    </div>
    <section class="catalogue-hero">
        <div class="container"><span class="eyebrow"><span class="dot"></span> Catalogue</span>
            <h1>Find your next book</h1>
            <p class="lead">Search trusted publishers, authors, ISBNs, and titles in one place.</p>
        </div>
    </section>
    <section class="section catalogue-section">
        <div class="container">
            <div class="catalogue-layout">
                <aside class="catalogue-filters reveal">
                    <div class="filter-heading">
                        <div><span class="filter-kicker">Refine results</span>
                            <h2>Filters</h2>
                        </div>@if(request()->hasAny(['q', 'category', 'min_price', 'max_price', 'sort']))<a
                        href="{{ route('books.index') }}">Clear</a>@endif
                    </div>
                    <form method="GET" action="{{ route('books.index') }}">
                        <label class="catalogue-label" for="catalogue-search">Search</label>
                        <input id="catalogue-search" class="catalogue-input" type="search" name="q"
                            value="{{ request('q') }}" placeholder="Book, author or publisher">

                        <label class="catalogue-label" for="catalogue-category">Category</label>
                        <select id="catalogue-category" class="catalogue-input" name="category">
                            <option value="">All categories</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}
                                </option>
                            @endforeach
                        </select>

                        <span class="catalogue-label">Price range</span>
                        <div class="price-filter-grid">
                            <input class="catalogue-input" type="number" min="0" name="min_price"
                                value="{{ request('min_price') }}" placeholder="Min ₹">
                            <input class="catalogue-input" type="number" min="0" name="max_price"
                                value="{{ request('max_price') }}" placeholder="Max ₹">
                        </div>
                        <input type="hidden" name="sort" value="{{ request('sort', 'title') }}">
                        <button class="btn btn-primary catalogue-filter-button" type="submit">Apply filters</button>
                    </form>
                </aside>

                <div class="catalogue-results">
                    <div class="catalogue-toolbar reveal">
                        <div><strong>{{ number_format($books->total()) }} books</strong>@if(request('q'))<span> matching
                        “{{ request('q') }}”</span>@else<span> available to browse</span>@endif</div>
                        <form method="GET" action="{{ route('books.index') }}">
                            @foreach(request()->except(['sort', 'page']) as $key => $value)
                                @if(is_scalar($value))<input type="hidden" name="{{ $key }}" value="{{ $value }}">@endif
                            @endforeach
                            <label for="catalogue-sort">Sort by</label>
                            <select id="catalogue-sort" class="catalogue-input" name="sort" onchange="this.form.submit()">
                                <option value="title" @selected(request('sort', 'title') === 'title')>Title A–Z</option>
                                <option value="newest" @selected(request('sort') === 'newest')>Newest</option>
                                <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High
                                </option>
                                <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low
                                </option>
                            </select>
                        </form>
                    </div>

                    <div class="catalogue-grid">
                        @forelse($books as $book)
                            @include('partials.book-card', ['book' => $book])
                        @empty
                            <div class="catalogue-empty"><span>⌕</span>
                                <h2>No books found</h2>
                                <p>Try another title, author, publisher, or price range.</p><a class="btn btn-primary"
                                    href="{{ route('books.index') }}">View all books</a>
                            </div>
                        @endforelse
                    </div>
                    <div class="catalogue-pagination">{{ $books->links() }}</div>
                </div>
            </div>
        </div>
    </section>
@endsection