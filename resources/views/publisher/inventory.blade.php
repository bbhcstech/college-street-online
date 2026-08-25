@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout');
@endphp
@section('title', 'Inventory')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link active"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<div class="a-grid a-grid-3 inventory-stats">
    <div class="stat-box"><div class="num">{{ $totalBooks }}</div><div class="label">Total Titles</div></div>
    <div class="stat-box inventory-stat-warning"><div class="num">{{ $lowStockCount }}</div><div class="label">Need Restocking</div></div>
    <div class="stat-box"><div class="num">{{ max(0, $totalBooks - $lowStockCount) }}</div><div class="label">Healthy Stock</div></div>
</div>

<form method="GET" class="inventory-toolbar">
    <div class="topbar-search inventory-search"><input type="text" name="q" value="{{ request('q') }}" placeholder="Search by title or ISBN..."></div>
    <label class="inventory-low-filter"><input type="checkbox" name="low_only" value="1" {{ request()->boolean('low_only') ? 'checked' : '' }}> Low stock only</label>
    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
    @if(request()->hasAny(['q', 'low_only']))<a href="{{ route('publisher.inventory.index') }}" class="btn btn-outline btn-sm">Clear</a>@endif
</form>

<div class="a-card inventory-table-card">
    <table class="a-table"><thead><tr><th>Book</th><th>Stock Level</th><th>Threshold</th><th>Status</th><th>Adjust Stock</th></tr></thead><tbody>
    @forelse($inventory as $book)
        @php
            $quantity = $book->inventory?->quantity ?? 0;
            $threshold = $book->inventory?->low_stock_threshold ?? 5;
            $isLow = ! $book->inventory || $book->inventory->isLowStock();
        @endphp
        <tr class="{{ $isLow ? 'inventory-row-low' : '' }}">
            <td>
                <div class="a-book-title">
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="a-book-cover-thumb">
                    @else
                        <div class="a-book-cover-thumb a-book-cover-placeholder" aria-hidden="true">&#128214;</div>
                    @endif
                    <div><span>{{ $book->title }}</span><small>ISBN: {{ $book->isbn }}</small></div>
                </div>
            </td>
            <td><strong class="inventory-quantity">{{ $quantity }}</strong> copies</td>
            <td>{{ $threshold }} copies</td>
            <td><span class="badge {{ $isLow ? 'badge-gold' : 'badge-success' }}">{{ $isLow ? 'Low stock' : 'In stock' }}</span></td>
            <td>
                <div class="inventory-adjust-actions">
                    <form method="POST" action="{{ route('publisher.inventory.adjust', $book) }}" class="inventory-restock-form">
                        @csrf
                        <input type="number" name="quantity" placeholder="+10 or -3" class="a-input" required aria-label="Stock adjustment for {{ $book->title }}">
                        <button class="btn btn-primary btn-sm">Update</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="5"><div class="inventory-empty">No books match your filters.</div></td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $inventory->links() }}</div>
</div>
@endsection
