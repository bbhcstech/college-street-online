@extends('layouts.dashboard')
@php 
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Inventory Management';
    $logoutRoute = route('admin.logout'); 
@endphp
@section('title', 'Inventory Management')
@section('nav')@include('admin.partials.nav', ['active' => 'inventory'])@endsection

@section('content')
<!-- Page Header -->
<div class="publisher-page-head" style="margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
    <div>
        <span class="analytics-eyebrow" style="color: #6366f1; font-weight: 600; font-size: 0.75rem; letter-spacing: 0.5px; text-transform: uppercase;">Stock Control & Tracking</span>
        <h2 style="font-size: 1.35rem; font-weight: 700; color: #111827; margin: 2px 0;">Inventory Management</h2>
        <p style="color: #6b7280; font-size: 0.82rem; margin: 0;">Monitor book stock levels across all publishers, adjust quantities, set low-stock thresholds, and export inventory data.</p>
    </div>
    <div style="display: flex; gap: 8px; align-items: center;">
        <a href="{{ route('admin.books.index') }}" class="btn btn-outline" style="height: 34px; padding: 0 12px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; border-radius: 6px;">📚 All Books</a>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary" style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; gap: 5px; font-size: 0.82rem; border-radius: 6px;">+ Add New Book</a>
    </div>
</div>

@if(session('success'))
    <div style="padding: 10px 14px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; margin-bottom: 16px; font-weight: 500; display: flex; align-items: center; gap: 6px; font-size: 0.85rem;">
        <span style="font-size: 1rem;">✓</span> {{ session('success') }}
    </div>
@endif

<!-- Summary KPI Cards (Compact) -->
<div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 18px;">
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #3b82f6; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #6b7280; letter-spacing: 0.5px;">Total Catalogue Titles</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #1e293b; margin-top: 2px;">{{ number_format($totalBooks) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #10b981; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #047857; letter-spacing: 0.5px;">In Stock Titles</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #065f46; margin-top: 2px;">{{ number_format($inStockCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #f59e0b; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b45309; letter-spacing: 0.5px;">Low Stock Alert</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #92400e; margin-top: 2px;">{{ number_format($lowStockCount) }}</strong>
    </div>
    <div class="a-card" style="padding: 12px 16px; border-radius: 8px; border-left: 3.5px solid #ef4444; background: #ffffff; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
        <span style="font-size: 0.72rem; text-transform: uppercase; font-weight: 600; color: #b91c1c; letter-spacing: 0.5px;">Out of Stock</span>
        <strong style="display: block; font-size: 1.4rem; font-weight: 700; color: #991b1b; margin-top: 2px;">{{ number_format($outOfStockCount) }}</strong>
    </div>
</div>

<!-- Main Table Container Card -->
<div class="a-card" style="border-radius: 8px; overflow: hidden; background: #ffffff; border: 1px solid var(--a-border, #e5e7eb); box-shadow: 0 1px 3px rgba(0,0,0,0.04);" data-inventory-table data-export-base="{{ route('admin.inventory.export', 'csv') }}">
    
    <!-- SINGLE ROW COMPACT FILTER BAR -->
    <form method="GET" style="display: flex; align-items: center; gap: 8px; padding: 10px 14px; width: 100%; flex-wrap: nowrap; overflow-x: auto; background: #ffffff; border-bottom: 1px solid #e5e7eb;">
        <!-- Search Input -->
        <div style="flex: 0 1 240px; min-width: 180px; display: flex; align-items: center; background: #f9fafb; border: 1px solid #d1d5db; border-radius: 6px; padding: 0 8px; height: 34px;">
            <span style="color: #9ca3af; margin-right: 6px; font-size: 0.85rem;">🔍</span>
            <input name="q" value="{{ request('q') }}" placeholder="Search title, ISBN, author..." 
                   style="border: none; background: transparent; width: 100%; height: 100%; outline: none; font-size: 0.82rem; color: #1f2937;">
        </div>

        <!-- Publisher Dropdown -->
        <select name="publisher_id" style="width: 140px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="">All Publishers</option>
            @foreach($publishers as $pub)
                <option value="{{ $pub->id }}" @selected((string) request('publisher_id') === (string) $pub->id)>
                    {{ $pub->business_name ?: $pub->name }}
                </option>
            @endforeach
        </select>

        <!-- Stock Status Dropdown -->
        <select name="stock" style="width: 130px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="">All Stock Levels</option>
            <option value="healthy" @selected(request('stock') === 'healthy')>Healthy Stock</option>
            <option value="low" @selected(request('stock') === 'low')>Low Stock Alert</option>
            <option value="out" @selected(request('stock') === 'out')>Out of Stock</option>
        </select>

        <!-- Book Status Dropdown -->
        <select name="status" style="width: 130px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="">All Book Statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>

        <!-- Per Page Dropdown -->
        <select name="per_page" style="width: 110px; height: 34px; padding: 0 8px; border: 1px solid #d1d5db; border-radius: 6px; background: #ffffff; font-size: 0.82rem; color: #374151; outline: none; cursor: pointer;">
            <option value="10" @selected($books->perPage() === 10)>10 entries</option>
            <option value="25" @selected($books->perPage() === 25)>25 entries</option>
            <option value="50" @selected($books->perPage() === 50)>50 entries</option>
            <option value="100" @selected($books->perPage() === 100)>100 entries</option>
        </select>

        <!-- Action Buttons -->
        <div style="display: flex; gap: 6px; align-items: center;">
            <button type="submit" class="btn btn-primary" style="height: 34px; padding: 0 14px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                Filter
            </button>
            @if(request()->query())
                <a href="{{ route('admin.inventory.index') }}" class="btn btn-outline" style="height: 34px; padding: 0 10px; display: inline-flex; align-items: center; font-size: 0.82rem; white-space: nowrap; border-radius: 6px;">
                    Reset
                </a>
            @endif
        </div>
    </form>

    <!-- EXPORT TOOLBAR -->
    <div class="publisher-export-bar" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 14px; background: #f9fafb; border-bottom: 1px solid #e5e7eb;">
        <div>
            <strong data-selection-count style="font-size: 0.82rem; color: #374151;">0 selected</strong>
            <span style="font-size: 0.78rem; color: #6b7280; margin-left: 6px;">
                Export filtered results or selected books.
            </span>
        </div>
        <div class="publisher-export-buttons" style="display: flex; gap: 4px;">
            <button type="button" class="btn btn-outline btn-sm" data-copy style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Copy</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="excel" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Excel</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="pdf" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">PDF</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="print" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">Print</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="csv" style="height: 28px; padding: 0 8px; font-size: 0.75rem;">CSV</button>
        </div>
    </div>

    <!-- DATA TABLE (Compact Padding) -->
    <div class="table-responsive" style="overflow-x: auto;">
        <table class="a-table" style="width: 100%; text-align: left; border-collapse: collapse; font-size: 0.84rem;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #475569; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">
                    <th style="width: 36px; text-align: center; padding: 10px 6px;"><input type="checkbox" data-select-all></th>
                    <th style="padding: 10px 12px;">Book Details</th>
                    <th style="padding: 10px 12px;">Publisher</th>
                    <th style="padding: 10px 12px;">Stock Status</th>
                    <th style="padding: 10px 12px; min-width: 230px;">Stock Quantity &amp; Low Alert Threshold</th>
                    <th style="padding: 10px 12px; text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                    @php
                        $qty = $book->inventory?->quantity ?? 0;
                        $thresh = $book->inventory?->low_stock_threshold ?? 5;
                        if ($qty <= 0) {
                            $badgeBg = '#fef2f2';
                            $badgeColor = '#991b1b';
                            $badgeText = 'Out of Stock';
                        } elseif ($qty <= $thresh) {
                            $badgeBg = '#fffbeb';
                            $badgeColor = '#92400e';
                            $badgeText = 'Low Stock Alert';
                        } else {
                            $badgeBg = '#ecfdf5';
                            $badgeColor = '#065f46';
                            $badgeText = 'Healthy Stock';
                        }
                    @endphp
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s ease;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="text-align: center; padding: 10px 6px;">
                            <input type="checkbox" data-select-id="{{ $book->id }}">
                        </td>
                        <td style="padding: 10px 12px;">
                            <div style="display: flex; gap: 10px; align-items: center;">
                                <img src="{{ $book->cover_url ?? asset('images/book-cover-placeholder.png') }}" 
                                     alt="{{ $book->title }}" 
                                     style="width: 36px; height: 48px; object-fit: cover; border-radius: 4px; border: 1px solid #e2e8f0; background: #f8fafc;">
                                <div>
                                    <strong style="display: block; font-size: 0.88rem; color: #0f172a; line-height: 1.25;">{{ $book->title }}</strong>
                                    <span style="font-size: 0.75rem; color: #64748b;">ISBN: {{ $book->isbn ?: 'N/A' }} | {{ $book->author?->name ?? 'Unknown Author' }}</span>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 10px 12px;">
                            <span style="font-size: 0.82rem; font-weight: 500; color: #334155;">
                                {{ $book->publisher?->business_name ?: ($book->publisher?->name ?? 'Direct System Catalog') }}
                            </span>
                        </td>
                        <td style="padding: 10px 12px;">
                            <span style="display: inline-flex; align-items: center; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: 600; background: {{ $badgeBg }}; color: {{ $badgeColor }};">
                                {{ $badgeText }}
                            </span>
                        </td>
                        <td style="padding: 10px 12px;">
                            <form action="{{ route('admin.inventory.update', $book->id) }}" method="POST" style="display: flex; align-items: center; gap: 8px;">
                                @csrf
                                @method('PUT')
                                <div style="display: flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; padding: 1px 5px;">
                                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Qty:</span>
                                    <input type="number" name="quantity" value="{{ $qty }}" min="0" required
                                           style="width: 50px; border: none; background: transparent; font-size: 0.82rem; font-weight: 600; color: #0f172a; text-align: center; outline: none;">
                                </div>
                                <div style="display: flex; align-items: center; gap: 4px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 5px; padding: 1px 5px;">
                                    <span style="font-size: 0.7rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Alert:</span>
                                    <input type="number" name="low_stock_threshold" value="{{ $thresh }}" min="0" required
                                           style="width: 45px; border: none; background: transparent; font-size: 0.82rem; font-weight: 600; color: #0f172a; text-align: center; outline: none;">
                                </div>
                                <button type="submit" class="btn btn-primary btn-sm" style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;" title="Save stock updates">
                                    Save
                                </button>
                            </form>
                        </td>
                        <td style="padding: 10px 12px; text-align: right;">
                            <a href="{{ route('admin.books.edit', $book->id) }}" class="btn btn-outline btn-sm" style="height: 28px; padding: 0 8px; font-size: 0.75rem; border-radius: 5px;" title="Edit book details">
                                ✏ Edit Title
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 30px; color: #64748b;">
                            No inventory items found matching your filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- PAGINATION FOOTER -->
    <div class="publisher-table-footer">
        <span>Showing {{ $books->firstItem() ?? 0 }}–{{ $books->lastItem() ?? 0 }} of {{ $books->total() }} books</span>
        @if($books->hasPages())
            <nav class="order-pagination" aria-label="Inventory pages">
                @if($books->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $books->previousPageUrl() }}">Previous</a>@endif
                @foreach(range(1, max(1, $books->lastPage())) as $page)<a href="{{ $books->url($page) }}" class="{{ $books->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                @if($books->hasMorePages())<a href="{{ $books->nextPageUrl() }}">Next</a>@else<span class="disabled">Next</span>@endif
            </nav>
        @endif
    </div>
</div>
@endsection
