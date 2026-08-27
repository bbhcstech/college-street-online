@php $inv = $book->inventory; @endphp
<div class="book-card reveal">
    <a href="{{ route('books.show', $book) }}">
        @if($book->cover_url)
            <div class="book-cover book-cover-uploaded"><img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover"
                    class="book-cover-image"></div>
        @else
            <div class="book-cover">
                <div class="spine"></div><span class="title-mark">{{ $book->title }}</span>
            </div>
        @endif
    </a>
    <div class="book-card-body">
        <span class="badge-tag-outline" style="margin-bottom:0;">{{ $book->category->name ?? 'General' }}</span>
        <a href="{{ route('books.show', $book) }}" class="title">{{ $book->title }}</a>
        <span class="author">by {{ $book->author->name ?? 'Unknown' }}</span>
        <div class="price-row">
            <span class="price">&#8377;{{ number_format($book->price, 0) }}</span>
            @if($book->mrp)<span class="price-strike">&#8377;{{ number_format($book->mrp, 0) }}</span>@endif
        </div>
        @if($inv)
            @if($inv->isLowStock())
                <span class="stock-pill low">&#9679; Low Stock</span>
            @else
                <span class="stock-pill in">&#9679; In Stock</span>
            @endif
        @endif
    </div>
</div>