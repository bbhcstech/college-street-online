@extends('layouts.app')
@section('title', $book->title . ' | College Street Online')
@section('content')
    @php
        $reviews = $book->reviews->sortByDesc('created_at');
        $reviewCount = $reviews->count();
        $averageRating = $reviewCount > 0 ? $reviews->avg('rating') : null;

        // Star counts & percentage breakdown for Flipkart/Amazon rating bar
        $starCounts = [
            5 => $reviews->where('rating', 5)->count(),
            4 => $reviews->where('rating', 4)->count(),
            3 => $reviews->where('rating', 3)->count(),
            2 => $reviews->where('rating', 2)->count(),
            1 => $reviews->where('rating', 1)->count(),
        ];
        $starPercents = [];
        foreach ($starCounts as $star => $count) {
            $starPercents[$star] = $reviewCount > 0 ? (int) round(($count / $reviewCount) * 100) : 0;
        }

        $stock = $book->inventory?->quantity ?? 0;
        $currencyService = app(\App\Services\CurrencyService::class);
        $priceData = $currencyService->resolveBookPrice($book);
        $discount = $priceData['mrp'] && $priceData['mrp'] > $priceData['price']
            ? (int) round((($priceData['mrp'] - $priceData['price']) / $priceData['mrp']) * 100)
            : null;
    @endphp

    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row">
            <a href="{{ route('home') }}">Home</a><span class="sep">/</span>
            <a href="{{ route('books.index') }}">Browse Books</a><span class="sep">/</span>
            <span class="current">{{ $book->title }}</span>
        </div>
    </div>

    {{-- Book Main Details Section --}}
    <section class="section book-detail-section">
        <div class="container">
            <div class="product-layout">
                <div class="product-visual reveal in-view">
                    <div class="product-cover-frame">
                        @if($book->cover_url)
                            <div class="book-cover book-cover-uploaded"><img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="book-cover-image"></div>
                        @else
                            <div class="book-cover">
                                <div class="spine"></div><span class="title-mark">{{ $book->title }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="product-visual-note"><span>&#10003;</span> Genuine listing from an approved publisher</div>
                </div>

                <div class="product-information reveal in-view">
                    <div class="product-heading-row">
                        <span class="badge-tag-outline">{{ $book->category->name ?? 'General' }}</span>
                        @if($discount)<span class="product-discount">{{ $discount }}% OFF</span>@endif
                    </div>
                    <h1>{{ $book->title }}</h1>
                    <p class="product-byline">
                        by <strong>{{ $book->author->name ?? 'Unknown author' }}</strong> &bull; Published by <strong>{{ $book->publisher->business_name ?? 'Independent publisher' }}</strong>
                    </p>

                    <div class="product-rating-row">
                        <span class="rating-chip">{{ $averageRating ? number_format($averageRating, 1) : 'New' }} @if($averageRating)&#9733;@endif</span>
                        <a href="#reader-reviews">{{ $reviewCount }} {{ Str::plural('verified review', $reviewCount) }}</a>
                        <span>ISBN {{ $book->isbn }}</span>
                    </div>

                    <div class="product-price-block">
                        <span class="product-price">{{ $priceData['symbol'] }}{{ number_format($priceData['price'], $priceData['currency'] === 'INR' ? 0 : 2) }}</span>
                        @if($priceData['mrp'])
                            <span class="product-mrp">M.R.P. {{ $priceData['symbol'] }}{{ number_format($priceData['mrp'], $priceData['currency'] === 'INR' ? 0 : 2) }}</span>
                        @endif
                        @if($discount)
                            <span class="product-saving">You save {{ $priceData['symbol'] }}{{ number_format($priceData['mrp'] - $priceData['price'], $priceData['currency'] === 'INR' ? 0 : 2) }}</span>
                        @endif
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

                    <div class="product-description">
                        <h2>About this book</h2>
                        <p>{{ $book->description ?? 'A detailed description for this title will be available soon.' }}</p>
                    </div>

                    <div class="product-purchase-box">
                        <form method="POST" action="{{ route('cart.store') }}" class="product-cart-form">
                            @csrf
                            <input type="hidden" name="book_id" value="{{ $book->id }}">
                            <label for="book-quantity">Quantity</label>
                            <input id="book-quantity" type="number" name="quantity" value="1" min="1" @if($stock > 0) max="{{ $stock }}" @endif class="form-control" @disabled($stock <= 0)>
                            <button type="submit" class="btn btn-primary" @disabled($stock <= 0)>{{ $stock > 0 ? 'Add to Cart' : 'Out of Stock' }}</button>
                        </form>
                        @guest
                            <p class="purchase-login-note">You can view every detail as a guest. <a href="{{ route('account.login') }}">Log in to purchase</a>.</p>
                        @endguest
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

    {{-- Amazon & Flipkart Style Reviews & Ratings Manager --}}
    <section class="section section-alt" id="reader-reviews" style="padding: 24px 0 32px 0;">
        <div class="container" style="max-width:860px;">
            <div style="margin-bottom:20px;">
                <span class="eyebrow" style="margin-bottom:2px;"><span class="dot"></span> Reader Feedback</span>
                <h2 style="font-size:1.35rem; margin:2px 0 0 0; color:var(--text-primary);">Customer Reviews &amp; Ratings</h2>
            </div>

            {{-- Rating Summary & Star Breakdown Box --}}
            <div style="background:#ffffff; border:1px solid var(--border, #e2e8f0); border-radius:14px; padding:20px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.02);">
                <div style="display:grid; grid-template-columns:220px 1fr; gap:24px; align-items:center;">
                    {{-- Overall Rating Box --}}
                    <div style="text-align:center; padding-right:20px; border-right:1px solid var(--border, #e2e8f0);">
                        <div style="font-size:2.5rem; font-weight:800; color:var(--text-primary); line-height:1;">
                            {{ $averageRating ? number_format($averageRating, 1) : '0.0' }}
                        </div>
                        <div style="color:#f59e0b; font-size:1.1rem; margin:4px 0;">
                            ★ ★ ★ ★ ★
                        </div>
                        <div style="font-size:0.82rem; font-weight:700; color:var(--text-secondary);">
                            {{ $reviewCount }} {{ Str::plural('verified rating', $reviewCount) }}
                        </div>
                        <small style="font-size:0.72rem; color:var(--text-muted); display:block; margin-top:2px;">100% Verified Purchases</small>
                    </div>

                    {{-- Star Breakdown Progress Bars --}}
                    <div style="display:flex; flex-direction:column; gap:6px;">
                        @foreach([5, 4, 3, 2, 1] as $star)
                            <div style="display:flex; align-items:center; gap:10px; font-size:0.82rem;">
                                <span style="font-weight:700; color:var(--text-secondary); width:42px; white-space:nowrap;">{{ $star }} ★</span>
                                <div style="flex:1; height:8px; background:#f1f5f9; border-radius:10px; overflow:hidden;">
                                    <div style="height:100%; width:{{ $starPercents[$star] }}%; background:{{ $star >= 4 ? '#15803d' : ($star === 3 ? '#eab308' : '#ef4444') }}; border-radius:10px; transition:width 0.4s ease;"></div>
                                </div>
                                <span style="font-weight:600; color:var(--text-muted); width:36px; text-align:right;">{{ $starPercents[$star] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Review Form (for verified buyers) --}}
            @if($eligibleOrder)
                <div class="card review-form-card" style="padding:16px 20px; margin-bottom:20px; border-radius:12px; background:#ffffff; border:1px solid var(--border, #e2e8f0);">
                    <h3 style="margin:0 0 12px 0; font-size:1.02rem; color:var(--text-primary);">Write a Review for your Purchase</h3>
                    <form method="POST" action="{{ route('books.reviews.store', $book) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="order_id" value="{{ $eligibleOrder->id }}">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px;">
                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary);">Overall Rating *</label>
                                <select name="rating" required class="form-control" style="padding:8px 10px; font-size:0.85rem;">
                                    <option value="">Select rating</option>
                                    @foreach([5 => '5 ★ - Excellent', 4 => '4 ★ - Good', 3 => '3 ★ - Average', 2 => '2 ★ - Fair', 1 => '1 ★ - Poor'] as $value => $label)
                                        <option value="{{ $value }}" {{ old('rating') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group" style="margin:0;">
                                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary);">Photos (optional, up to 4)</label>
                                <input type="file" name="images[]" class="form-control" accept="image/jpeg,image/png,image/webp" multiple style="padding:5px 8px; font-size:0.82rem;">
                            </div>
                        </div>
                        <div class="form-group" style="margin-bottom:12px;">
                            <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary);">Review Details</label>
                            <textarea name="review" maxlength="2000" class="form-control" rows="3" style="font-size:0.85rem; padding:8px 10px;" placeholder="What did you like or dislike about this book?">{{ old('review') }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary" style="padding:8px 18px; font-size:0.85rem; font-weight:700; border-radius:6px;">Submit Review</button>
                    </form>
                </div>
            @endif

            {{-- Reviews List with Initial Top 3 Limit & Show More Toggle --}}
            <div class="review-list" style="display:flex; flex-direction:column; gap:12px;">
                @forelse($reviews as $index => $review)
                    <article class="single-review-card" 
                             style="background:#ffffff; border:1px solid var(--border, #e2e8f0); border-radius:12px; padding:14px 16px; box-shadow:0 2px 6px rgba(0,0,0,0.02); {{ $index >= 3 ? 'display:none;' : '' }}"
                             data-review-index="{{ $index }}">
                        <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <span style="width:30px; height:30px; border-radius:50%; background:#f1f5f9; color:var(--text-primary); font-weight:700; font-size:0.8rem; display:grid; place-items:center;">
                                    {{ strtoupper(substr($review->customer->name, 0, 1)) }}
                                </span>
                                <div>
                                    <strong style="font-size:0.88rem; color:var(--text-primary); display:block; line-height:1.2;">{{ $review->customer->name }}</strong>
                                    <small style="font-size:0.72rem; color:#16a34a; font-weight:700;">✔ Verified Purchase</small>
                                </div>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                <span style="background:#15803d; color:#ffffff; font-size:0.75rem; font-weight:800; padding:2px 7px; border-radius:4px;">
                                    {{ $review->rating }}.0 ★
                                </span>
                                <small style="font-size:0.72rem; color:var(--text-muted);">{{ $review->created_at->format('d M Y') }}</small>
                            </div>
                        </div>

                        @if($review->review)
                            <p style="margin:6px 0 8px 0; font-size:0.86rem; color:var(--text-secondary); line-height:1.45;">{{ $review->review }}</p>
                        @endif

                        {{-- Amazon / Flipkart Style Small 72px Image Thumbnails --}}
                        @if($review->images && count($review->images) > 0)
                            <div style="display:flex; flex-wrap:wrap; gap:8px; margin:8px 0 6px 0;">
                                @foreach($review->images as $image)
                                    <div class="review-thumb-wrapper" onclick="openImageModal('{{ asset('storage/'.$image) }}')" 
                                         style="width:72px; height:72px; border-radius:8px; border:1px solid var(--border, #cbd5e1); overflow:hidden; cursor:pointer; background:#f8fafc; transition:transform 0.15s ease;"
                                         title="Click to zoom photo">
                                        <img src="{{ asset('storage/'.$image) }}" alt="Customer review photo" style="width:100%; height:100%; object-fit:cover;">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if($review->admin_response)
                            <div style="margin-top:8px; padding:10px 12px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 4%, #ffffff); border:1px solid color-mix(in srgb, var(--brand-primary, #1e3a8a) 15%, #ffffff); border-radius:8px; font-size:0.82rem;">
                                <strong style="color:var(--brand-primary, #1e3a8a); display:block; margin-bottom:2px;">Response from College Street Online</strong>
                                <p style="margin:0; color:var(--text-secondary); line-height:1.4;">{{ $review->admin_response }}</p>
                            </div>
                        @endif
                    </article>
                @empty
                    <div style="background:#ffffff; border:1px dashed var(--border, #cbd5e1); border-radius:12px; padding:20px; text-align:center;">
                        <p style="color:var(--text-secondary); margin:0; font-size:0.86rem;">No customer reviews yet. Delivered customers can be the first to review this book.</p>
                    </div>
                @endforelse
            </div>

            {{-- Show More Reviews Button --}}
            @if($reviewCount > 3)
                <button type="button" id="loadMoreReviewsBtn" onclick="toggleMoreReviews()" class="btn btn-outline" 
                        style="width:100%; margin-top:14px; padding:10px; font-size:0.86rem; font-weight:700; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:6px;">
                    <span>Show More Reviews ({{ $reviewCount - 3 }} more)</span> &darr;
                </button>
            @endif
        </div>
    </section>

    {{-- Review Image Lightbox Modal --}}
    <div id="reviewImageModal" style="display:none; position:fixed; inset:0; z-index:9999; background:rgba(0,0,0,0.8); place-items:center; padding:20px;" onclick="closeImageModal(event)">
        <div style="position:relative; max-width:90vw; max-height:90vh;" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" style="position:absolute; top:-14px; right:-14px; width:32px; height:32px; border-radius:50%; background:#ffffff; border:none; font-weight:800; font-size:1.1rem; cursor:pointer; box-shadow:0 4px 12px rgba(0,0,0,0.3); display:grid; place-items:center;">&times;</button>
            <img id="reviewModalImg" src="" alt="Enlarged review image" style="max-width:90vw; max-height:85vh; border-radius:12px; object-fit:contain; box-shadow:0 10px 30px rgba(0,0,0,0.5);">
        </div>
    </div>

    <script>
        function openImageModal(url) {
            const modal = document.getElementById('reviewImageModal');
            const img = document.getElementById('reviewModalImg');
            if (modal && img) {
                img.src = url;
                modal.style.display = 'grid';
            }
        }
        function closeImageModal() {
            const modal = document.getElementById('reviewImageModal');
            if (modal) {
                modal.style.display = 'none';
            }
        }
        function toggleMoreReviews() {
            const extraReviews = document.querySelectorAll('.single-review-card[data-review-index]');
            const btn = document.getElementById('loadMoreReviewsBtn');
            let isExpanded = btn.getAttribute('data-expanded') === 'true';

            extraReviews.forEach(card => {
                const idx = parseInt(card.getAttribute('data-review-index'), 10);
                if (idx >= 3) {
                    card.style.display = isExpanded ? 'none' : 'block';
                }
            });

            if (isExpanded) {
                btn.innerHTML = '<span>Show More Reviews ({{ $reviewCount - 3 }} more)</span> &darr;';
                btn.setAttribute('data-expanded', 'false');
            } else {
                btn.innerHTML = '<span>Collapse Reviews</span> &uarr;';
                btn.setAttribute('data-expanded', 'true');
            }
        }
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeImageModal();
        });
    </script>

    {{-- Related Titles --}}
    @if($related->count())
        <section class="section">
            <div class="container">
                <div class="section-head reveal">
                    <div>
                        <span class="eyebrow"><span class="dot"></span> You May Also Like</span>
                        <h2>Related Titles</h2>
                    </div>
                    <a href="{{ route('books.index', ['category' => $book->category?->slug]) }}">View category &rarr;</a>
                </div>
                <div class="grid grid-4">
                    @foreach($related as $r)
                        @include('partials.book-card', ['book' => $r])
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
