@extends('layouts.dashboard')
@php $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Sales';
    $logoutRoute = route('publisher.logout');
$statuses = ['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled', 'return_requested', 'returned']; @endphp
@section('title', 'Orders')
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span
                class="nav-icon">&#9635;</span><span>Dashboard</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Catalogue</div><a href="{{ route('publisher.books.index') }}" class="nav-link"><span
                class="nav-icon">&#128214;</span><span>My Books</span></a><a href="{{ route('publisher.inventory.index') }}"
            class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Marketing</div><a href="{{ route('publisher.coupons.index') }}" class="nav-link"><span
                class="nav-icon">&#127991;</span><span>Coupons & Offers</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}"
            class="nav-link active"><span class="nav-icon">&#128666;</span><span>Orders</span></a><a
            href="{{ route('publisher.payments.index') }}" class="nav-link"><span
                class="nav-icon">&#8377;</span><span>Payments & Invoices</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Reports</div><a href="{{ route('publisher.analytics.index') }}" class="nav-link"><span
                class="nav-icon">&#128200;</span><span>Analytics & Reports</span></a>
    </div>
@endsection
@section('content')
<style>
    .publisher-order-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 18px
    }

    .publisher-order-summary>div {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 17px 20px;
        background: var(--a-surface);
        border: 1px solid var(--a-border);
        border-radius: 12px
    }

    .publisher-order-summary span {
        color: var(--a-text-muted);
        font-size: .73rem;
        font-weight: 700
    }

    .publisher-order-summary strong {
        font: italic 600 1.5rem var(--font-display)
    }

    .publisher-order-toolbar {
        display: grid;
        grid-template-columns: repeat(12, minmax(0, 1fr));
        gap: 10px;
        padding: 18px 20px;
        background: var(--a-surface);
        border-bottom: 1px solid var(--a-border)
    }

    .publisher-order-toolbar>* {
        min-width: 0
    }

    .publisher-order-toolbar .publisher-search {
        grid-column: 1/5;
        grid-row: 1
    }

    .publisher-order-toolbar select[name=status] {
        grid-column: 5/7;
        grid-row: 1
    }

    .publisher-order-toolbar select[name=fulfillment] {
        grid-column: 7/9;
        grid-row: 1
    }

    .publisher-order-toolbar select[name=payment] {
        grid-column: 9/11;
        grid-row: 1
    }

    .publisher-order-toolbar select[name=per_page] {
        grid-column: 11/13;
        grid-row: 1
    }

    .publisher-order-toolbar input[name=date_from] {
        grid-column: 1/4;
        grid-row: 2
    }

    .publisher-order-toolbar input[name=date_to] {
        grid-column: 4/7;
        grid-row: 2
    }

    .publisher-order-toolbar button {
        grid-column: 7/9;
        grid-row: 2
    }

    .publisher-order-toolbar>a {
        grid-column: 9/11;
        grid-row: 2;
        text-align: center
    }

    .publisher-order-table {
        min-width: 1240px
    }

    .publisher-order-table th,
    .publisher-order-table td {
        padding: 13px 12px
    }

    .publisher-order-table tbody tr:hover {
        background: color-mix(in srgb, var(--a-primary) 4%, var(--a-surface))
    }

    .publisher-order-table td small {
        display: block;
        color: var(--a-text-muted);
        font-size: .65rem;
        margin-top: 3px
    }

    .publisher-order-table th:last-child,
    .publisher-order-table td:last-child {
        position: sticky;
        right: 0;
        z-index: 2;
        background: var(--a-surface);
        box-shadow: -8px 0 14px rgba(20, 45, 70, .07);
        min-width: 145px
    }

    .publisher-order-table th:last-child {
        z-index: 3
    }

    .publisher-order-table tbody tr:hover td:last-child {
        background: color-mix(in srgb, var(--a-primary) 4%, var(--a-surface))
    }

    .publisher-order-table th:first-child,
    .publisher-order-table td:first-child {
        width: 42px;
        text-align: center
    }

    .fulfillment-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 99px;
        background: #e9eef4;
        color: #53677c;
        font-size: .67rem;
        font-weight: 800
    }

    .fulfillment-processing {
        background: #eee8fb;
        color: #6745ae
    }

    .fulfillment-packed {
        background: #fff0dc;
        color: #a85f08
    }

    .fulfillment-shipped {
        background: #e3f6ed;
        color: #078657
    }

    @media(max-width:1050px) {
        .publisher-order-toolbar .publisher-search {
            grid-column: 1/7
        }

        .publisher-order-toolbar select[name=status] {
            grid-column: 7/10
        }

        .publisher-order-toolbar select[name=fulfillment] {
            grid-column: 10/13
        }

        .publisher-order-toolbar select[name=payment] {
            grid-column: 1/4;
            grid-row: 2
        }

        .publisher-order-toolbar input[name=date_from] {
            grid-column: 4/7;
            grid-row: 2
        }

        .publisher-order-toolbar input[name=date_to] {
            grid-column: 7/10;
            grid-row: 2
        }

        .publisher-order-toolbar select[name=per_page] {
            grid-column: 10/13;
            grid-row: 2
        }

        .publisher-order-toolbar button {
            grid-column: 1/4;
            grid-row: 3
        }

        .publisher-order-toolbar>a {
            grid-column: 4/7;
            grid-row: 3
        }
    }

    @media(max-width:700px) {
        .publisher-order-summary {
            grid-template-columns: 1fr
        }

        .publisher-order-toolbar {
            grid-template-columns: 1fr
        }

        .publisher-order-toolbar>* {
            grid-column: 1 !important;
            grid-row: auto !important
        }
    }
</style>
<div class="publisher-page-head">
    <div><span class="analytics-eyebrow">Order fulfillment</span>
        <h2>Orders containing your books</h2>
        <p>Search, filter, export, and safely progress each book fulfillment.</p>
    </div>
</div>
<div class="publisher-order-summary">
    <div><span>Order lines</span><strong>{{ $totalItems }}</strong></div>
    <div><span>Pending fulfillment</span><strong>{{ $pendingItems }}</strong></div>
    <div><span>Total units ordered</span><strong>{{ $unitsOrdered }}</strong></div>
</div>
<div class="a-card publisher-table-card" data-publisher-orders
    data-export-base="{{ route('publisher.orders.export', 'csv') }}">
    <form method="GET" class="publisher-order-toolbar">
        <div class="publisher-search"><span>⌕</span><input name="q" value="{{ request('q') }}"
                placeholder="Search order, customer or book"></div><select name="status" class="a-select">
            <option value="">All order statuses</option>@foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>
                    {{ str($status)->replace('_', ' ')->title() }}
            </option>@endforeach
        </select><select name="fulfillment" class="a-select">
            <option value="">All fulfillment</option>@foreach(['pending', 'processing', 'packed', 'shipped'] as $status)
                <option value="{{ $status }}" @selected(request('fulfillment') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select><select name="payment" class="a-select">
            <option value="">All payments</option>@foreach(['pending', 'verified', 'rejected'] as $status)
                <option value="{{ $status }}" @selected(request('payment') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select><input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input"
            title="From date"><input type="date" name="date_to" value="{{ request('date_to') }}" class="a-input"
            title="To date"><select name="per_page" class="a-select">@foreach([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" @selected($items->perPage() === $size)>{{ $size }} entries</option>@endforeach
        </select><button class="btn btn-primary btn-sm">Apply</button>@if(request()->query())<a
        href="{{ route('publisher.orders.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
    </form>
    <div class="publisher-export-bar">
        <div><strong data-selection-count>0 selected</strong><span>Export selected rows, or every filtered result when
                none are selected.</span></div>
        <div class="publisher-export-buttons"><button type="button" class="btn btn-outline btn-sm"
                data-copy>Copy</button>@foreach(['excel' => 'Excel', 'pdf' => 'PDF', 'print' => 'Print', 'csv' => 'CSV'] as $type => $label)<button
                type="button" class="btn btn-outline btn-sm" data-export="{{ $type }}">{{ $label }}</button>@endforeach
        </div>
    </div>
    <div class="publisher-table-scroll">
        <table class="a-table publisher-order-table">
            <thead>
                <tr>
                    <th><input type="checkbox" data-select-all></th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Book</th>
                    <th>Date</th>
                    <th>Qty</th>
                    <th>Gross</th>
                    <th>Payment</th>
                    <th>Overall</th>
                    <th>My fulfillment</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item) @php($price = $item->base_unit_price ?? $item->unit_price)
                @php($next = ['pending' => 'processing', 'processing' => 'packed', 'packed' => 'shipped'][$item->fulfillment_status] ?? null)
                @php($canUpdate = $next && in_array($item->order->status, ['confirmed', 'processing', 'packed', 'shipped'], true))
                <tr data-export-row data-id="{{ $item->id }}">
                    <td><input type="checkbox" data-row-select></td>
                    <td><strong data-cell>#CSO{{ $item->order_id }}</strong></td>
                    <td><strong data-cell>{{ $item->order->customer?->name ?? '—' }}</strong><small
                            data-cell>{{ $item->order->customer?->email }}</small></td>
                    <td><strong data-cell>{{ $item->book?->title ?? 'Book unavailable' }}</strong></td>
                    <td data-cell>
                        {{ $item->order->created_at->format('d M Y') }}<small>{{ $item->order->created_at->format('h:i A') }}</small>
                    </td>
                    <td data-cell>{{ $item->quantity }}</td>
                    <td data-cell>₹{{ number_format($item->quantity * $price, 2) }}</td>
                    <td><span class="order-payment payment-{{ $item->order->payment?->verified_status ?? 'none' }}"
                            data-cell>{{ ucfirst($item->order->payment?->verified_status ?? 'No payment') }}</span></td>
                    <td><span class="badge badge-muted"
                            data-cell>{{ str($item->order->status)->replace('_', ' ')->title() }}</span></td>
                    <td><span class="fulfillment-badge fulfillment-{{ $item->fulfillment_status }}"
                            data-cell>{{ ucfirst($item->fulfillment_status) }}</span></td>
                    <td>@if($canUpdate)
                        <form method="POST" action="{{ route('publisher.orders.items.status', $item) }}"
                            onsubmit="return confirm('Mark this item as {{ $next }}?')">@csrf @method('PATCH')<input
                                type="hidden" name="status" value="{{ $next }}"><button
                    class="btn btn-primary btn-sm">Mark {{ ucfirst($next) }}</button></form>@else<span
                                class="a-muted">No action</span>@endif
                    </td>
                </tr>
                @empty<tr>
                    <td colspan="11">
                        <div class="analytics-empty">No order items match your filters.</div>
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="publisher-table-footer"><span>Showing {{ $items->firstItem() ?? 0 }}–{{ $items->lastItem() ?? 0 }} of
            {{ $items->total() }} lines</span>@if($items->hasPages())
                <nav class="order-pagination">@if($items->onFirstPage())<span class="disabled">Previous</span>@else<a
                href="{{ $items->previousPageUrl() }}">Previous</a>@endif
                    @foreach(range(1, $items->lastPage()) as $page)<a href="{{ $items->url($page) }}"
                    class="{{ $items->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                    @if($items->hasMorePages())<a href="{{ $items->nextPageUrl() }}">Next</a>@else<span
                    class="disabled">Next</span>@endif
            </nav>@endif
    </div>
</div>
<script>(() => { const root = document.querySelector('[data-publisher-orders]'), rows = [...root.querySelectorAll('[data-export-row]')], all = root.querySelector('[data-select-all]'), count = root.querySelector('[data-selection-count]'), selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked), update = () => { const n = selected().length; count.textContent = `${n} selected`; if (all) { all.checked = n === rows.length && n > 0; all.indeterminate = n > 0 && n < rows.length } }; all?.addEventListener('change', () => { rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); update() }); rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); root.querySelector('[data-copy]')?.addEventListener('click', async e => { const chosen = selected().length ? selected() : rows, text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); await navigator.clipboard.writeText(text); e.target.textContent = 'Copied'; setTimeout(() => e.target.textContent = 'Copy', 1200) }); root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => { const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin), params = new URLSearchParams(location.search); params.delete('page'); const ids = selected().map(r => r.dataset.id).join(','); if (ids) params.set('ids', ids); url.search = params; b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url })); })();</script>
@endsection