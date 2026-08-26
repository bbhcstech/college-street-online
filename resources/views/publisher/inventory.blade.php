@extends('layouts.dashboard')
@php $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
$logoutRoute = route('publisher.logout'); @endphp
@section('title', 'Inventory')
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span
                class="nav-icon">▣</span><span>Dashboard</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Catalogue</div><a href="{{ route('publisher.books.index') }}" class="nav-link"><span
                class="nav-icon">📖</span><span>My Books</span></a><a href="{{ route('publisher.inventory.index') }}"
            class="nav-link active"><span class="nav-icon">📦</span><span>Inventory</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span
                class="nav-icon">🚚</span><span>Orders</span></a>
    </div>
@endsection
@section('content')
<div class="publisher-page-head">
    <div><span class="analytics-eyebrow">Stock control</span>
        <h2>Book inventory</h2>
        <p>Monitor stock, export records, and apply safe adjustments.</p>
    </div><a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add my book</a>
</div>
<div class="publisher-book-summary">
    <div><span>Total titles</span><strong>{{ $totalBooks }}</strong></div>
    <div><span>Need restocking</span><strong>{{ $lowStockCount }}</strong></div>
    <div><span>Healthy stock</span><strong>{{ max(0, $totalBooks - $lowStockCount) }}</strong></div>
</div>

<div class="a-card publisher-table-card" data-inventory-table
    data-export-base="{{ route('publisher.inventory.export', 'csv') }}">
    <form method="GET" class="inventory-data-toolbar">
        <div class="publisher-search"><span>⌕</span><input name="q" value="{{ request('q') }}"
                placeholder="Search title or ISBN"></div><select name="stock" class="a-select">
            <option value="">All stock levels</option>
            <option value="healthy" @selected(request('stock') === 'healthy')>Healthy stock</option>
            <option value="low" @selected(request('stock') === 'low')>Low stock</option>
            <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
        </select><select name="status" class="a-select">
            <option value="">All book statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select><select name="per_page" class="a-select">
            <option value="10" @selected($inventory->perPage() === 10)>10 entries</option>
            <option value="25" @selected($inventory->perPage() === 25)>25 entries</option>
            <option value="50" @selected($inventory->perPage() === 50)>50 entries</option>
            <option value="100" @selected($inventory->perPage() === 100)>100 entries</option>
        </select><button class="btn btn-primary btn-sm">Filter</button>@if(request()->query())<a
        href="{{ route('publisher.inventory.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
    </form>
    <div class="publisher-export-bar">
        <div><strong data-selection-count>0 selected</strong><span>Exports use selected rows, or all filtered inventory
                when none are selected.</span></div>
        <div class="publisher-export-buttons"><button type="button" class="btn btn-outline btn-sm"
                data-copy>Copy</button><button type="button" class="btn btn-outline btn-sm"
                data-export="excel">Excel</button><button type="button" class="btn btn-outline btn-sm"
                data-export="pdf">PDF</button><button type="button" class="btn btn-outline btn-sm"
                data-export="print">Print</button><button type="button" class="btn btn-outline btn-sm"
                data-export="csv">CSV</button></div>
    </div>
    <div class="publisher-table-scroll">
        <table class="a-table inventory-data-table">
            <thead>
                <tr>
                    <th><input type="checkbox" data-select-all aria-label="Select all inventory rows"></th>
                    <th>Book</th>
                    <th>Current stock</th>
                    <th>Threshold</th>
                    <th>Stock status</th>
                    <th>Adjustment</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $book)@php($quantity = $book->inventory?->quantity ?? 0)@php($threshold = $book->inventory?->low_stock_threshold ?? 5)@php($isLow = $quantity <= $threshold)
                <tr data-export-row data-id="{{ $book->id }}" class="{{ $isLow ? 'inventory-row-low' : '' }}">
                    <td><input type="checkbox" data-row-select aria-label="Select {{ $book->title }}"></td>
                    <td>
                        <div class="a-book-title">@if($book->cover_url)<img src="{{ $book->cover_url }}"
                        alt="{{ $book->title }} cover" class="a-book-cover-thumb">@else<div
                                class="a-book-cover-thumb a-book-cover-placeholder">📖</div>@endif<div><strong
                                    data-cell>{{ $book->title }}</strong><small data-cell>ISBN:
                                    {{ $book->isbn }}</small></div>
                        </div>
                    </td>
                    <td data-cell><strong class="inventory-count">{{ $quantity }}</strong><small>copies
                            available</small></td>
                    <td data-cell>{{ $threshold }} copies</td>
                    <td><span class="status-pill {{ $isLow ? 'inventory-status-low' : 'status-success' }}"
                            data-cell>{{ $quantity === 0 ? 'Out of stock' : ($isLow ? 'Low stock' : 'Healthy') }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('publisher.inventory.adjust', $book) }}"
                            class="inventory-inline-adjust">@csrf<input type="number" name="quantity" class="a-input"
                                placeholder="+10 or -3" required
                                aria-label="Stock adjustment for {{ $book->title }}"><button
                                class="btn btn-primary btn-sm">Apply</button></form><small
                            class="inventory-adjust-help">Positive adds · negative reduces</small>
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="6">
                        <div class="analytics-empty">No inventory records match your filters.</div>
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="publisher-table-footer"><span>Showing {{ $inventory->firstItem() ?? 0 }}–{{ $inventory->lastItem() ?? 0 }}
            of {{ $inventory->total() }} titles</span>@if($inventory->hasPages())
                <nav class="order-pagination">@if($inventory->onFirstPage())<span class="disabled">Previous</span>@else<a
                href="{{ $inventory->previousPageUrl() }}">Previous</a>@endif
                    @foreach(range(1, $inventory->lastPage()) as $page)<a href="{{ $inventory->url($page) }}"
                    class="{{ $inventory->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                    @if($inventory->hasMorePages())<a href="{{ $inventory->nextPageUrl() }}">Next</a>@else<span
            class="disabled">Next</span>@endif</nav>@endif
    </div>
</div>
<script>(() => { const root = document.querySelector('[data-inventory-table]'), rows = [...root.querySelectorAll('[data-export-row]')], all = root.querySelector('[data-select-all]'), count = root.querySelector('[data-selection-count]'); const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked), update = () => { const n = selected().length; count.textContent = `${n} selected`; if (all) { all.checked = n === rows.length && n > 0; all.indeterminate = n > 0 && n < rows.length } }; all?.addEventListener('change', () => { rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); update() }); rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); const ids = () => selected().map(r => r.dataset.id).join(','); root.querySelector('[data-copy]')?.addEventListener('click', async e => { const chosen = selected().length ? selected() : rows, text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); await navigator.clipboard.writeText(text); e.target.textContent = 'Copied'; setTimeout(() => e.target.textContent = 'Copy', 1200) }); root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => { const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin), params = new URLSearchParams(location.search); params.delete('page'); if (ids()) params.set('ids', ids()); url.search = params; b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url })); })();</script>
@endsection