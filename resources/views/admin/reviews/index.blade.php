@extends('layouts.dashboard')
@php 
        $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout'); 
@endphp

@section('title', 'Customer Reviews')
@section('nav')@include('admin.partials.nav', ['active' => 'reviews'])@endsection

@section('content')
    <!-- Page Header -->
    <div class="publisher-page-head review-page-head"
        style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <div>
            <p style="color: var(--a-text-muted); font-size: 0.85rem; margin: 0;">
                Monitor, filter, and moderate verified customer book reviews and ratings.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div
            style="padding: 10px 14px; background: var(--a-success-bg, #ecfdf5); border: 1px solid color-mix(in srgb, var(--a-success) 30%, transparent); border-radius: 6px; color: var(--a-success); margin-bottom: 16px; font-weight: 500; display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
            <span style="font-size: 1rem;">✓</span> {{ session('success') }}
        </div>
    @endif

    <!-- Summary KPI Cards -->
    <div class="review-kpi-grid"
        style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;">
        <div class="a-card"
            style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #3b82f6; background: var(--a-surface); border: 1px solid var(--a-border); border-left-width: 3.5px; box-shadow: var(--a-shadow-sm);">
            <span
                style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: var(--a-text-muted); letter-spacing: 0.5px;">Total
                Reviews</span>
            <strong
                style="display: block; font-size: 1.4rem; font-weight: 700; color: var(--a-text); margin-top: 2px;">{{ number_format($totalReviews) }}</strong>
        </div>
        <div class="a-card"
            style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #f59e0b; background: var(--a-surface); border: 1px solid var(--a-border); border-left-width: 3.5px; box-shadow: var(--a-shadow-sm);">
            <span
                style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #f59e0b; letter-spacing: 0.5px;">Average
                Rating</span>
            <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: var(--a-text); margin-top: 2px;">⭐
                {{ $avgRating }} / 5.0</strong>
        </div>
        <div class="a-card"
            style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #10b981; background: var(--a-surface); border: 1px solid var(--a-border); border-left-width: 3.5px; box-shadow: var(--a-shadow-sm);">
            <span
                style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #10b981; letter-spacing: 0.5px;">5-Star
                Reviews</span>
            <strong
                style="display: block; font-size: 1.4rem; font-weight: 700; color: var(--a-text); margin-top: 2px;">{{ number_format($fiveStarCount) }}</strong>
        </div>
        <div class="a-card"
            style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #ef4444; background: var(--a-surface); border: 1px solid var(--a-border); border-left-width: 3.5px; box-shadow: var(--a-shadow-sm);">
            <span
                style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #ef4444; letter-spacing: 0.5px;">Low
                Ratings (≤ 2★)</span>
            <strong
                style="display: block; font-size: 1.4rem; font-weight: 700; color: var(--a-text); margin-top: 2px;">{{ number_format($lowRatingCount) }}</strong>
        </div>
    </div>

    <!-- Main Table Card Container -->
    <div class="a-card review-table-card"
        style="border-radius: 8px; overflow: hidden; background: var(--a-surface); border: 1px solid var(--a-border); box-shadow: var(--a-shadow-sm);">

        <!-- SINGLE ROW COMPACT FILTER BAR -->
        <form method="GET" class="review-filter-bar"
            style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; width: 100%; flex-wrap: nowrap; overflow-x: auto; background: var(--a-surface); border-bottom: 1px solid var(--a-border);">
            <!-- Search Input -->
            <div
                style="flex: 0 1 260px; min-width: 180px; display: flex; align-items: center; background: var(--a-surface-alt); border: 1px solid var(--a-border); border-radius: 6px; padding: 0 8px; height: 34px;">
                <span style="color: var(--a-text-muted); margin-right: 6px; font-size: 0.85rem;">🔍</span>
                <input name="q" value="{{ request('q') }}" placeholder="Search book, review text, customer..."
                    style="border: none; background: transparent; width: 100%; height: 100%; outline: none; font-size: 0.82rem; color: var(--a-text);">
            </div>

            <!-- Rating Filter Dropdown -->
            <select name="rating"
                style="width: 140px; height: 34px; padding: 0 8px; border: 1px solid var(--a-border); border-radius: 6px; background: var(--a-surface-alt); font-size: 0.82rem; color: var(--a-text); outline: none; cursor: pointer;">
                <option value="">All Ratings</option>
                <option value="5" @selected(request('rating') === '5')>⭐ 5 Stars</option>
                <option value="4" @selected(request('rating') === '4')>⭐ 4 Stars</option>
                <option value="3" @selected(request('rating') === '3')>⭐ 3 Stars</option>
                <option value="2" @selected(request('rating') === '2')>⭐ 2 Stars</option>
                <option value="1" @selected(request('rating') === '1')>⭐ 1 Star</option>
            </select>

            <!-- Per Page Dropdown -->
            <select name="per_page"
                style="width: 110px; height: 34px; padding: 0 8px; border: 1px solid var(--a-border); border-radius: 6px; background: var(--a-surface-alt); font-size: 0.82rem; color: var(--a-text); outline: none; cursor: pointer;">
                <option value="15" @selected($reviews->perPage() === 15)>15 entries</option>
                <option value="25" @selected($reviews->perPage() === 25)>25 entries</option>
                <option value="50" @selected($reviews->perPage() === 50)>50 entries</option>
                <option value="100" @selected($reviews->perPage() === 100)>100 entries</option>
            </select>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 6px; align-items: center;">
                <button type="submit" class="btn btn-primary"
                    style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                    Filter
                </button>
                @if(request()->hasAny(['q', 'rating', 'per_page']))
                    <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline"
                        style="height: 34px; padding: 0 10px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                        Reset
                    </a>
                @endif
            </div>
        </form>

        <!-- DATA TABLE -->
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="a-table review-data-table"
                style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.84rem;">
                <thead>
                    <tr
                        style="background: var(--a-surface-alt); border-bottom: 1px solid var(--a-border); color: var(--a-text-muted); font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <th style="padding: 10px 12px; width: 220px;">Book Title</th>
                        <th style="padding: 10px 12px; width: 110px;">Rating</th>
                        <th style="padding: 10px 12px; width: 180px;">Customer</th>
                        <th style="padding: 10px 12px;">Review Content</th>
                        <th style="padding: 10px 12px; width: 110px;">Date</th>
                        <th style="padding: 10px 12px; text-align: right; width: 90px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reviews as $rev)
                        <tr style="border-bottom: 1px solid var(--a-border); transition: background 0.15s ease;"
                            onmouseover="this.style.background='var(--a-surface-alt)'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 10px 12px;">
                                <div style="display: flex; gap: 8px; align-items: center;">
                                    <img src="{{ $rev->book?->cover_url ?? asset('images/book-cover-placeholder.png') }}"
                                        alt="{{ $rev->book->title ?? 'Book' }}"
                                        style="width: 32px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid var(--a-border); background: var(--a-surface-alt);">
                                    <div>
                                        <strong
                                            style="display: block; font-size: 0.85rem; color: var(--a-text); line-height: 1.25;">{{ $rev->book->title ?? 'Deleted Book' }}</strong>
                                        <span style="font-size: 0.72rem; color: var(--a-text-muted);">ISBN:
                                            {{ $rev->book->isbn ?? 'N/A' }}</span>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 10px 12px;">
                                <div style="display: flex; align-items: center; gap: 2px;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span
                                            style="font-size: 0.85rem; color: {{ $i <= $rev->rating ? '#f59e0b' : 'var(--a-border)' }};">★</span>
                                    @endfor
                                    <span
                                        style="font-size: 0.78rem; font-weight: 600; color: var(--a-text); margin-left: 4px;">{{ $rev->rating }}.0</span>
                                </div>
                            </td>
                            <td style="padding: 10px 12px;">
                                <strong
                                    style="display: block; font-size: 0.84rem; color: var(--a-text);">{{ $rev->customer->name ?? 'Anonymous Customer' }}</strong>
                                <span style="font-size: 0.75rem; color: var(--a-text-muted);">{{ $rev->customer->email ?? '' }}</span>
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.82rem; color: var(--a-text); line-height: 1.35;">
                                {{ $rev->review ?: 'No written review text provided.' }}
                            </td>
                            <td style="padding: 10px 12px; font-size: 0.80rem; color: var(--a-text-muted);">
                                {{ $rev->created_at ? $rev->created_at->format('d M Y') : 'N/A' }}
                            </td>
                            <td style="padding: 10px 12px; text-align: right;">
                                <a href="{{ route('admin.reviews.show', $rev) }}" class="btn btn-outline btn-sm"
                                    style="height:28px;padding:0 9px;font-size:.75rem;margin-right:5px;">View</a>
                                <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}"
                                    onsubmit="return confirm('Delete this review permanently?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm"
                                        style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;"
                                        title="Remove review">
                                        Remove
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 32px; color: var(--a-text-muted);">
                                No book reviews found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- PAGINATION FOOTER -->
        @if($reviews->hasPages())
            <div style="padding: 10px 14px; border-top: 1px solid var(--a-border); background: var(--a-surface);">
                {{ $reviews->links() }}
            </div>
        @endif
    </div>
@endsection