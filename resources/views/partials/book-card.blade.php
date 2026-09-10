@php 
    $inv = $book->inventory;
    $currencyService = app(\App\Services\CurrencyService::class);
    $priceData = $currencyService->resolveBookPrice($book);
    $discount = $priceData['mrp'] && $priceData['mrp'] > $priceData['price']
        ? (int) round((($priceData['mrp'] - $priceData['price']) / $priceData['mrp']) * 100)
        : null;
@endphp
<article class="book-card reveal">
    <a href="{{ route('books.show', $book) }}">
        @if($book->cover_url)
            <div class="book-cover book-cover-uploaded">
                <img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="book-cover-image">
            </div>
        @else
            <div class="book-cover">
                <div class="spine"></div>
                <span class="title-mark">{{ $book->title }}</span>
            </div>
        @endif
    </a>
    <div class="book-card-body">
        <div class="book-card-meta">
            <span class="badge-tag-outline">{{ $book->category->name ?? 'General' }}</span>
            @if($discount)
                <span class="discount-badge">{{ $discount }}% off</span>
            @endif</div>
        <a href="{{ route('books.show', $book) }}" class="title">{{ $book->title }}</a>
        <span class="author">by {{ $book->author->name ?? 'Unknown' }}</span>
        <div class="price-row">
            <span class="price">{{ $priceData['symbol'] }}{{ number_format($priceData['price'], $priceData['currency'] === 'INR' ? 0 : 2) }}</span>
            @if($priceData['mrp'])
                <span class="price-strike">{{ $priceData['symbol'] }}{{ number_format($priceData['mrp'], $priceData['currency'] === 'INR' ? 0 : 2) }}</span>
            @endif
        </div>
        @if($inv)
            @if($inv->isLowStock())
                <span class="stock-pill low">&#9679; Low Stock</span>
            @else
                <span class="stock-pill in">&#9679; In Stock</span>
            @endif
        @endif
        <a href="{{ route('books.show', $book) }}" class="book-card-action">View details <span>→</span></a>
    </div>
</article>