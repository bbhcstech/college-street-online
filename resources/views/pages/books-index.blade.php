@extends('layouts.app')
@section('title', 'Browse Books | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;">
    <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Browse Books</span></div>
</div>
<section class="page-hero"><div class="container"><span class="eyebrow"><span class="dot"></span> Catalogue</span><h1>Browse All Books</h1><p class="lead">{{ $books->total() }} titles across academics, literature, and competitive exams.</p></div></section>
<section class="section" style="padding-top:0;">
    <div class="container">
        <form method="GET" class="filter-row reveal">
            <button type="submit" name="category" value="" class="filter-pill {{ !request('category') ? 'active' : '' }}">All Categories</button>
            @foreach($categories as $cat)
                <button type="submit" name="category" value="{{ $cat->slug }}" class="filter-pill {{ request('category')==$cat->slug ? 'active' : '' }}">{{ $cat->name }}</button>
            @endforeach
        </form>
        <div class="grid grid-4">
            @forelse($books as $book)
                @include('partials.book-card', ['book' => $book])
            @empty
                <p>No books match your search. Try a different keyword.</p>
            @endforelse
        </div>
        <div style="margin-top:32px;">{{ $books->links() }}</div>
    </div>
</section>
@endsection
