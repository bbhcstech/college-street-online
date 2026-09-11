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
<div class="publisher-page-head" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <span class="analytics-eyebrow" style="color: #6366f1; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Customer Feedback &amp; Ratings</span>
        <h2 style="font-size: 1.35rem; font-weight: 700; color: #111827; margin: 2px 0;">Book Reviews &amp; Moderation</h2>
        <p style="color: #6b7280; font-size: 0.82rem; margin: 0;">Monitor, filter, and moderate verified customer book reviews and ratings.</p>
    </div>
</div>

@if(session('success'))
    <div style="padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; margin-bottom: 16px; font-weight: 500; display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
        <span style="font-size: 1rem;">✓</span> {{ session('success') }}
    </div>
@endif

<!-- Summary KPI Cards -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;">
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #3b82f6; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #6b7280; letter-spacing: 0.5px;">Total Reviews</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ number_format($totalReviews) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #f59e0b; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b45309; letter-spacing: 0.5px;">Average Rating</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #92400e; margin-top: 2px;">⭐ {{ $avgRating }} / 5.0</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #10b981; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #047857; letter-spacing: 0.5px;">5-Star Reviews</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #065f46; margin-top: 2px;">{{ number_format($fiveStarCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #ef4444; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b91c1c; letter-spacing: 0.5px;">Low Ratings (≤ 2★)</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-top: 2px;">{{ number_format($lowRatingCount) }}</strong>
    </div>
</div>

<!-- Main Table Card Container -->
<div class="a-card" style="border-radius: 8px; overflow: hidden; background: #ffffff; border: 1px solid var(--a-border, #e5e7eb); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
    
    <!-- SINGLE ROW COMPACT FILTER BAR -->
    <form method="GET" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; width: 100%; flex-wrap: nowrap; overflow-x: auto; background: #ffffff; border-bottom: 1px solid #e5e7eb;">
        <!-- Search Input -->
        <div style="flex: 0 1 260px; min-width: 180px; display: flex; align-items: center; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 8px; height: 34px;">
            <span style="color: #9ca3af; margin-right: 6px; font-size: 0.85rem;">🔍</span>
            <input name="q" value="{{ request('q') }}" placeholder="Search book, review text, customer..." 
                   style="border: none; background: transparent; width: 100%; height: 100%; outline: none; font-size: 0.82rem; color: #1f2937;">
        </div>

        <!-- Rating Filter Dropdown -->
        <select name="rating" style="width: 140px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="">All Ratings</option>
            <option value="5" @selected(request('rating') === '5')>⭐ 5 Stars</option>
            <option value="4" @selected(request('rating') === '4')>⭐ 4 Stars</option>
            <option value="3" @selected(request('rating') === '3')>⭐ 3 Stars</option>
            <option value="2" @selected(request('rating') === '2')>⭐ 2 Stars</option>
            <option value="1" @selected(request('rating') === '1')>⭐ 1 Star</option>
        </select>

        <!-- Per Page Dropdown -->
        <select name="per_page" style="width: 110px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="15" @selected($reviews->perPage() === 15)>15 entries</option>
            <option value="25" @selected($reviews->perPage() === 25)>25 entries</option>
            <option value="50" @selected($reviews->perPage() === 50)>50 entries</option>
            <option value="100" @selected($reviews->perPage() === 100)>100 entries</option>
        </select>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="submit" class="btn btn-primary" style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                Filter
            </button>
            @if(request()->hasAny(['q', 'rating', 'per_page']))
                <a href="{{ route('admin.reviews.index') }}" class="btn btn-outline" style="height: 34px; padding: 0 10px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- DATA TABLE -->
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="a-table" style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.84rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
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
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <img src="{{ $rev->book->cover_image_url ?? asset('images/book-cover-placeholder.png') }}" 
                                     alt="{{ $rev->book->title ?? 'Book' }}" 
                                     style="width: 32px; height: 44px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                <div>
                                    <strong style="display: block; font-size: 0.85rem; color: #0f172a; line-height: 1.25;">{{ $rev->book->title ?? 'Deleted Book' }}</strong>
                                    <span style="font-size: 0.72rem; color: #64748b;">ISBN: {{ $rev->book->isbn ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; align-items: center; gap: 2px;">
                                @for($i = 1; $i <= 5; $i++)
                                    <span style="font-size: 0.85rem; color: {{ $i <= $rev->rating ? '#f59e0b' : '#e2e8f0' }};">★</span>
                                @endfor
                                <span style="font-size: 0.78rem; font-weight: 600; color: #334155; margin-left: 4px;">{{ $rev->rating }}.0</span>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;">
                            <strong style="display: block; font-size: 0.84rem; color: #1e293b;">{{ $rev->customer->name ?? 'Anonymous Customer' }}</strong>
                            <span style="font-size: 0.75rem; color: #64748b;">{{ $rev->customer->email ?? '' }}</span>
                        </td>
                        <td style="padding: 10px 12px; font-size: 0.82rem; color: #334155; line-height: 1.35;">
                            {{ $rev->review ?: 'No written review text provided.' }}
                        </td>
                        <td style="padding: 10px 12px; font-size: 0.80rem; color: #64748b;">
                            {{ $rev->created_at ? $rev->created_at->format('d M Y') : 'N/A' }}
                        </td>
                        <td style="padding: 10px 12px; text-align: right;">
                            <form method="POST" action="{{ route('admin.reviews.destroy', $rev) }}" onsubmit="return confirm('Delete this review permanently?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;" title="Remove review">
                                    Remove
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 32px; color: #64748b;">
                            No book reviews found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    @if($reviews->hasPages())
        <div style="padding: 10px 14px; border-top: 1px solid #e2e8f0; background: #ffffff;">
            {{ $reviews->links() }}
        </div>
    @endif
</div>
@endsection

