@extends('layouts.app')
@section('title', 'College Street Online | Books, Delivered From Kolkata\'s Book Market')
@section('content')
    @php
        $heroBooks = $newArrivals->concat($bestsellers)->filter(fn ($book) => $book->cover_url)->unique('id')->take(3);
    @endphp
    @auth
        @if(auth()->user()->isCustomer())
            <div class="customer-welcome">
                <div class="container customer-welcome-row">
                    <div class="customer-welcome-left">
                        <div class="welcome-text-group">
                            <span class="welcome-greeting">Welcome back, <strong>{{ auth()->user()->name }}</strong> <span class="wave-emoji" aria-hidden="true">&#128075;</span></span>
                            <span class="welcome-subtext">Here is your live shopping dashboard</span>
                        </div>
                    </div>
                    @if($activeOrders->isNotEmpty())
                        <a href="{{ route('account.orders') }}" class="active-orders-pill">
                            <span class="pulse-dot"></span>
                            <span><strong>{{ $activeOrders->count() }}</strong> active {{ Str::plural('order', $activeOrders->count()) }}</span>
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    @endif
                </div>
            </div>
            @if($activeOrders->isNotEmpty())
                <section class="active-orders-bar">
                    <div class="container">
                        <div class="active-orders-heading">
                            <div class="heading-title">
                                <span class="active-orders-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
                                </span>
                                <div>
                                    <strong>Active Purchases</strong>
                                    <small>Live status updates for your current orders</small>
                                </div>
                            </div>
                            <a href="{{ route('account.orders') }}" class="view-history-link">
                                <span>View order history</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                        <div class="active-orders-list">
                            @foreach($activeOrders as $order)
                                @php
                                    $statusStep = match($order->status) {
                                        'pending_payment' => 1,
                                        'confirmed', 'processing', 'packed' => 2,
                                        'shipped' => 3,
                                        'delivered', 'completed' => 4,
                                        default => 1,
                                    };
                                    $stepWidth = match($statusStep) {
                                        1 => '15%',
                                        2 => '50%',
                                        3 => '80%',
                                        4 => '100%',
                                        default => '15%',
                                    };
                                    $statusClass = match($order->status) {
                                        'pending_payment' => 'status-pending',
                                        'confirmed', 'processing', 'packed' => 'status-processing',
                                        'shipped' => 'status-shipped',
                                        'delivered', 'completed' => 'status-delivered',
                                        'cancelled', 'returned', 'return_requested' => 'status-cancelled',
                                        default => 'status-default',
                                    };
                                @endphp
                                <a href="{{ route('account.orders') }}" class="active-order-card">
                                    <div class="active-order-header">
                                        <div class="order-id-tag">
                                            <span class="label">ORDER</span>
                                            <span class="id">#{{ $order->id }}</span>
                                        </div>
                                        <span class="status-badge {{ $statusClass }}">
                                            <span class="status-dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </div>

                                    <div class="order-mini-stepper">
                                        <div class="stepper-track">
                                            <div class="stepper-progress" style="width: {{ $stepWidth }}"></div>
                                        </div>
                                        <div class="stepper-steps">
                                            <div class="step {{ $statusStep >= 1 ? 'is-complete' : '' }}">
                                                <span class="dot"></span>
                                                <span class="step-label">Placed</span>
                                            </div>
                                            <div class="step {{ $statusStep >= 2 ? 'is-complete' : '' }}">
                                                <span class="dot"></span>
                                                <span class="step-label">Processing</span>
                                            </div>
                                            <div class="step {{ $statusStep >= 3 ? 'is-complete' : '' }}">
                                                <span class="dot"></span>
                                                <span class="step-label">Shipped</span>
                                            </div>
                                            <div class="step {{ $statusStep >= 4 ? 'is-complete' : '' }}">
                                                <span class="dot"></span>
                                                <span class="step-label">Delivered</span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($order->items && $order->items->isNotEmpty())
                                        <div class="order-items-preview">
                                            @foreach($order->items->take(2) as $item)
                                                <div class="order-item-snippet">
                                                    @if($item->book?->cover_url)
                                                        <img src="{{ $item->book->cover_url }}" alt="{{ $item->book->title }}" class="item-cover-thumb">
                                                    @else
                                                        <div class="item-cover-placeholder">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                        </div>
                                                    @endif
                                                    <div class="item-snippet-details">
                                                        <span class="item-title">{{ Str::limit($item->book?->title ?? 'Book Item', 28) }}</span>
                                                        <span class="item-sub">Qty: {{ $item->quantity }} &bull; {{ $order->getCurrencySymbolAttribute() }}{{ number_format($item->unit_price, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($order->items->count() > 2)
                                                <div class="more-items-count">+{{ $order->items->count() - 2 }} more {{ Str::plural('item', $order->items->count() - 2) }}</div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="active-order-body">
                                        <div class="meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            <span>{{ $order->created_at->format('d M Y') }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                            <span>{{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</span>
                                        </div>
                                        <div class="order-price">
                                            <span>{{ $order->getCurrencySymbolAttribute() }}{{ number_format($order->total_amount, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="active-order-footer">
                                        <span>Track order details</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @elseif(isset($recentOrders) && $recentOrders->isNotEmpty())
                <section class="active-orders-bar">
                    <div class="container">
                        <div class="active-orders-heading">
                            <div class="heading-title">
                                <span class="active-orders-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                </span>
                                <div>
                                    <strong>My Recent Orders</strong>
                                    <small>Review your previous purchases &amp; track history</small>
                                </div>
                            </div>
                            <a href="{{ route('account.orders') }}" class="view-history-link">
                                <span>View all orders</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>
                        <div class="active-orders-list">
                            @foreach($recentOrders as $order)
                                @php
                                    $statusClass = match($order->status) {
                                        'pending_payment' => 'status-pending',
                                        'confirmed', 'processing', 'packed' => 'status-processing',
                                        'shipped' => 'status-shipped',
                                        'delivered', 'completed' => 'status-delivered',
                                        'cancelled', 'returned', 'return_requested' => 'status-cancelled',
                                        default => 'status-default',
                                    };
                                @endphp
                                <a href="{{ route('account.orders') }}" class="active-order-card">
                                    <div class="active-order-header">
                                        <div class="order-id-tag">
                                            <span class="label">ORDER</span>
                                            <span class="id">#{{ $order->id }}</span>
                                        </div>
                                        <span class="status-badge {{ $statusClass }}">
                                            <span class="status-dot"></span>
                                            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
                                        </span>
                                    </div>

                                    @if($order->items && $order->items->isNotEmpty())
                                        <div class="order-items-preview">
                                            @foreach($order->items->take(2) as $item)
                                                <div class="order-item-snippet">
                                                    @if($item->book?->cover_url)
                                                        <img src="{{ $item->book->cover_url }}" alt="{{ $item->book->title }}" class="item-cover-thumb">
                                                    @else
                                                        <div class="item-cover-placeholder">
                                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                                                        </div>
                                                    @endif
                                                    <div class="item-snippet-details">
                                                        <span class="item-title">{{ Str::limit($item->book?->title ?? 'Book Item', 28) }}</span>
                                                        <span class="item-sub">Qty: {{ $item->quantity }} &bull; {{ $order->getCurrencySymbolAttribute() }}{{ number_format($item->unit_price, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                            @if($order->items->count() > 2)
                                                <div class="more-items-count">+{{ $order->items->count() - 2 }} more {{ Str::plural('item', $order->items->count() - 2) }}</div>
                                            @endif
                                        </div>
                                    @endif

                                    <div class="active-order-body">
                                        <div class="meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                                            <span>{{ $order->created_at->format('d M Y') }}</span>
                                        </div>
                                        <div class="meta-item">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                                            <span>{{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</span>
                                        </div>
                                        <div class="order-price">
                                            <span>{{ $order->getCurrencySymbolAttribute() }}{{ number_format($order->total_amount, 2) }}</span>
                                        </div>
                                    </div>

                                    <div class="active-order-footer">
                                        <span>View order details</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif
        @endif
    @endauth
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

    @if(isset($availableCoupons) && $availableCoupons->isNotEmpty())
        <section class="section customer-coupons-section" style="padding:48px 0;background:var(--surface-alt);">
            <div class="container">
                <div class="section-head reveal" style="margin-bottom:24px;">
                    <span class="eyebrow"><span class="dot"></span> Promo Codes &amp; Discounts</span>
                    <h2>Coupons &amp; Offers for You</h2>
                    <p>Apply promo codes during checkout to enjoy instant savings on your orders.</p>
                </div>
                <div class="coupons-dash-grid">
                    @foreach($availableCoupons as $coupon)
                        <div class="coupon-dash-card">
                            <div class="coupon-card-header">
                                <span class="coupon-badge-discount">
                                    @if($coupon->discount_type === 'percentage')
                                        {{ (int) $coupon->discount_value }}% OFF
                                    @else
                                        ₹{{ number_format($coupon->discount_value, 0) }} OFF
                                    @endif
                                </span>
                                <span class="coupon-code-pill">{{ $coupon->code }}</span>
                            </div>
                            <div class="coupon-card-body">
                                @if($coupon->min_order_value)
                                    <span class="coupon-condition">Min spend: ₹{{ number_format($coupon->min_order_value, 0) }}</span>
                                @else
                                    <span class="coupon-condition">No min order value</span>
                                @endif
                                @if($coupon->valid_to)
                                    <span class="coupon-expiry">Valid till {{ $coupon->valid_to->format('d M Y') }}</span>
                                @else
                                    <span class="coupon-expiry">Limited time deal</span>
                                @endif
                            </div>
                        </div>
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

    @if(isset($recentlyViewedBooks) && $recentlyViewedBooks->isNotEmpty())
        <section class="section section-alt" id="recently-viewed" style="padding: 24px 0 28px 0;">
            <div class="container">
                <div class="section-head reveal" style="margin-bottom: 14px;">
                    <span class="eyebrow" style="margin-bottom:2px;"><span class="dot"></span> Browsing history</span>
                    <h2 style="font-size:1.35rem; margin:2px 0 2px 0;">Recently Viewed</h2>
                    <p style="font-size:0.82rem; margin:0; color:var(--text-secondary);">Books you have recently viewed on College Street Online.</p>
                </div>
                <div class="recently-viewed-grid">
                    @foreach($recentlyViewedBooks as $book)
                        @include('partials.book-card', ['book' => $book])
                    @endforeach
                </div>
            </div>
        </section>
        <style>
            #recently-viewed .recently-viewed-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(165px, 185px));
                gap: 12px;
            }
            #recently-viewed .book-card {
                border-radius: 10px !important;
                box-shadow: 0 2px 8px rgba(0,0,0,0.03) !important;
            }
            #recently-viewed .book-cover {
                height: 145px !important;
                min-height: 145px !important;
                padding: 6px !important;
            }
            #recently-viewed .book-cover-image {
                max-height: 132px !important;
                object-fit: contain !important;
            }
            #recently-viewed .book-card-body {
                padding: 8px 10px !important;
            }
            #recently-viewed .book-card-meta {
                margin-bottom: 2px !important;
            }
            #recently-viewed .badge-tag-outline {
                font-size: 0.65rem !important;
                padding: 1px 5px !important;
            }
            #recently-viewed .book-card .title {
                font-size: 0.82rem !important;
                margin-bottom: 2px !important;
                line-height: 1.25 !important;
            }
            #recently-viewed .book-card .author {
                font-size: 0.72rem !important;
                margin-bottom: 4px !important;
            }
            #recently-viewed .book-card .price {
                font-size: 0.88rem !important;
            }
            #recently-viewed .book-card .price-row {
                margin-bottom: 2px !important;
            }
            #recently-viewed .stock-pill {
                font-size: 0.68rem !important;
                padding: 1px 6px !important;
            }
            #recently-viewed .book-card-action {
                font-size: 0.72rem !important;
                margin-top: 4px !important;
            }
        </style>
    @endif

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
