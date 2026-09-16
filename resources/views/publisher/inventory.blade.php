@extends('layouts.dashboard')
@php $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout'); @endphp
@section('title', 'Inventory')
@section('nav')@include('publisher.partials.nav', ['active' => 'inventory'])@endsection
@section('content')
<style>
    html.dark .publisher-page-head {
        background: var(--a-surface-alt, #12314e) !important;
        border: 1px solid var(--a-border, #1d3e5c) !important;
        border-radius: 14px;
        padding: 16px 18px;
    }
    html.dark .publisher-inventory-summary > div {
        background: var(--a-surface, #0f2a44) !important;
        border-color: var(--a-border, #1d3e5c) !important;
    }
    html.dark .publisher-inventory-summary span {
        color: var(--a-text-muted, #93a3be) !important;
    }
    html.dark .publisher-inventory-summary strong {
        color: var(--a-text, #edf1fa) !important;
    }
    html.dark .inventory-row-low {
        background: rgba(245, 158, 11, 0.08) !important;
    }
    html.dark .status-pill.status-success {
        background: rgba(16, 185, 129, 0.18) !important;
        color: #34d399 !important;
    }
    html.dark .status-pill.status-muted {
        background: rgba(148, 163, 184, 0.18) !important;
        color: #94a3b8 !important;
    }
</style>
<div class="publisher-page-head">
    <div>
        <span class="analytics-eyebrow">Stock control</span>
        <h2>Book inventory</h2>
        <p>Monitor stock, export records, and apply safe adjustments.</p>
    </div>
    <a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add my book</a>
</div>
<div class="publisher-book-summary publisher-inventory-summary">
    <div>
        <span>Total books</span>
        <strong>{{ $totalBooks }}</strong>
    </div>
    <div>
        <span>In stock</span>
        <strong>{{ $inStockCount }}</strong>
    </div>
    <div>
        <span>Low stock</span>
        <strong>{{ $lowStockCount }}</strong>
    </div>
    <div>
        <span>Out of stock</span>
        <strong>{{ $outOfStockCount }}</strong>
    </div>
</div>

<div class="a-card publisher-table-card" data-inventory-table
    data-export-base="{{ route('publisher.inventory.export', 'csv') }}">
    <form method="GET" class="inventory-data-toolbar">
        <div class="publisher-search">
            <span>⌕</span>
            <input name="q" value="{{ request('q') }}" placeholder="Search title or ISBN">
        </div>
        <select name="stock" class="a-select">
            <option value="">All stock levels</option>
            <option value="healthy" @selected(request('stock') === 'healthy')>Healthy stock</option>
            <option value="low" @selected(request('stock') === 'low')>Low stock</option>
            <option value="out" @selected(request('stock') === 'out')>Out of stock</option>
        </select>
        <select name="status" class="a-select">
            <option value="">All book statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <select name="per_page" class="a-select">
            <option value="10" @selected($inventory->perPage() === 10)>10 entries</option>
            <option value="25" @selected($inventory->perPage() === 25)>25 entries</option>
            <option value="50" @selected($inventory->perPage() === 50)>50 entries</option>
            <option value="100" @selected($inventory->perPage() === 100)>100 entries</option>
        </select>
        <button class="btn btn-primary btn-sm">Filter</button>
        @if(request()->query())
            <a href="{{ route('publisher.inventory.index') }}" class="btn btn-outline btn-sm">Reset</a>
        @endif
    </form>
    <div class="publisher-export-bar">
        <div>
            <strong data-selection-count>0 selected</strong>
            <span>
                Exports use selected rows, or all filtered inventory when none are selected.
            </span>
        </div>
        <div class="publisher-export-buttons">
            <button type="button" class="btn btn-outline btn-sm" data-copy>Copy</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="excel">Excel</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="pdf">PDF</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="print">Print</button>
            <button type="button" class="btn btn-outline btn-sm" data-export="csv">CSV</button>
        </div>
    </div>  
    <div class="publisher-table-scroll">
        <table class="a-table inventory-data-table">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" data-select-all aria-label="Select all inventory rows">
                    </th>
                    <th>Cover</th>
                    <th>ISBN</th>
                    <th>Title</th>
                    <th>Author</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock quantity</th>
                    <th>Status</th>
                    <th>Published date</th>
                    <th>Stock update</th>
                </tr>
            </thead>
            <tbody>
                @forelse($inventory as $book)
                @php($quantity = $book->inventory?->quantity ?? 0)
                @php($threshold = $book->inventory?->low_stock_threshold ?? 5)
                @php($isLow = $quantity <= $threshold)
                <tr data-export-row data-id="{{ $book->id }}" class="{{ $isLow ? 'inventory-row-low' : '' }}">
                    <td>
                        <input type="checkbox" data-row-select aria-label="Select {{ $book->title }}">
                    </td>
                    <td>@if($book->cover_url)<img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="a-book-cover-thumb">@else<div class="a-book-cover-thumb a-book-cover-placeholder">📖</div>@endif</td>
                    <td data-cell>{{ $book->isbn }}</td>
                    <td data-cell><strong>{{ $book->title }}</strong></td>
                    <td data-cell>{{ $book->author?->name ?? '—' }}</td>
                    <td data-cell>{{ $book->category?->name ?? '—' }}</td>
                    <td data-cell>₹{{ number_format($book->price, 2) }}</td>
                    <td data-cell><strong class="inventory-count">{{ $quantity }}</strong><small>{{ $quantity === 0 ? 'Out of stock' : ($isLow ? 'Low stock' : 'In stock') }}</small></td>
                    <td><span class="status-pill {{ $book->status === 'active' ? 'status-success' : 'status-muted' }}" data-cell>{{ ucfirst($book->status) }}</span></td>
                    <td data-cell>{{ optional($book->published_at ?? $book->created_at)->format('d M Y') ?? '—' }}</td>
                    <td>
                        <form method="POST" action="{{ route('publisher.inventory.adjust', $book) }}"
                            class="inventory-inline-adjust">@csrf<input type="number" name="quantity" class="a-input"
                                placeholder="± Qty" required title="Use a positive number to add stock or a negative number to reduce it"
                                aria-label="Stock adjustment for {{ $book->title }}"><button
                                class="btn btn-primary btn-sm">Save</button></form>
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="11">
                        <div class="analytics-empty">No inventory records match your filters.</div>
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="publisher-table-footer"><span>Showing
            {{ $inventory->firstItem() ?? 0 }}–{{ $inventory->lastItem() ?? 0 }}
            of {{ $inventory->total() }} titles</span>@if($inventory->hasPages())
                <nav class="order-pagination">@if($inventory->onFirstPage())<span class="disabled">Previous</span>@else<a
                href="{{ $inventory->previousPageUrl() }}">Previous</a>@endif
                    @foreach(range(1, $inventory->lastPage()) as $page)<a href="{{ $inventory->url($page) }}"
                    class="{{ $inventory->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                    @if($inventory->hasMorePages())<a href="{{ $inventory->nextPageUrl() }}">Next</a>@else<span
                    class="disabled">Next</span>@endif
            </nav>@endif
    </div>
</div>
<script>(() => { const root = document.querySelector('[data-inventory-table]'), rows = [...root.querySelectorAll('[data-export-row]')], all = root.querySelector('[data-select-all]'), count = root.querySelector('[data-selection-count]'); const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked), update = () => { const n = selected().length; count.textContent = `${n} selected`; if (all) { all.checked = n === rows.length && n > 0; all.indeterminate = n > 0 && n < rows.length } }; all?.addEventListener('change', () => { rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); update() }); rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); const ids = () => selected().map(r => r.dataset.id).join(','); root.querySelector('[data-copy]')?.addEventListener('click', async e => { const chosen = selected().length ? selected() : rows, text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); await navigator.clipboard.writeText(text); e.target.textContent = 'Copied'; setTimeout(() => e.target.textContent = 'Copy', 1200) }); root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => { const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin), params = new URLSearchParams(location.search); params.delete('page'); if (ids()) params.set('ids', ids()); url.search = params; b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url })); })();</script>
@endsection
