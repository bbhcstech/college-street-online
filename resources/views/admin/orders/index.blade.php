@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Operations';
    $logoutRoute = route('admin.logout');
    $statuses = ['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled', 'return_requested', 'returned'];
@endphp
@section('title', 'All Orders')
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')
    <div class="publisher-page-head">
        <div><span class="analytics-eyebrow">Order operations</span>
            <h2>All customer orders</h2>
            <p>Search, export, and manage every order from one table.</p>
        </div>
    </div>
    <div class="order-summary">
        <div><span>Total orders</span><strong>{{ $totalOrders }}</strong></div>
        <div><span>Pending payment</span><strong>{{ $pendingOrders }}</strong></div>
        <div><span>Verified revenue</span><strong>₹{{ number_format($totalRevenue, 2) }}</strong></div>
    </div>

    <div class="a-card publisher-table-card" data-order-table data-export-base="{{ route('admin.orders.export', 'csv') }}">
        <form method="GET" class="order-table-toolbar">
            <div class="publisher-search"><span>⌕</span><input name="q" value="{{ request('q') }}"
                    placeholder="Search order, customer or email"></div>
            <select name="status" class="a-select">
                <option value="">All statuses</option>@foreach($statuses as $status)
                    <option value="{{ $status }}" @selected(request('status') === $status)>
                {{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach
            </select>
            <select name="payment" class="a-select">
                <option value="">All payments</option>
                <option value="pending" @selected(request('payment') === 'pending')>Pending</option>
                <option value="verified" @selected(request('payment') === 'verified')>Verified</option>
                <option value="rejected" @selected(request('payment') === 'rejected')>Rejected</option>
                <option value="none" @selected(request('payment') === 'none')>No payment</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input" title="From date"><input
                type="date" name="date_to" value="{{ request('date_to') }}" class="a-input" title="To date">
            <select name="per_page" class="a-select">
                <option value="10" @selected($orders->perPage() === 10)>10 entries</option>
                <option value="25" @selected($orders->perPage() === 25)>25 entries</option>
                <option value="50" @selected($orders->perPage() === 50)>50 entries</option>
                <option value="100" @selected($orders->perPage() === 100)>100 entries</option>
            </select>
            <button class="btn btn-primary btn-sm">Apply</button>@if(request()->query())<a
            href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif
        </form>
        <div class="publisher-export-bar">
            <div><strong data-selection-count>0 selected</strong><span>Exports use selected rows, or all filtered orders
                    when none are selected.</span></div>
            <div class="publisher-export-buttons"><button type="button" class="btn btn-outline btn-sm"
                    data-copy>Copy</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="excel">Excel</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="pdf">PDF</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="print">Print</button><button type="button" class="btn btn-outline btn-sm"
                    data-export="csv">CSV</button></div>
        </div>
        <div class="publisher-table-scroll">
            <table class="a-table order-data-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" data-select-all aria-label="Select all orders"></th>
                        <th>Order</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr data-export-row data-id="{{ $order->id }}">
                            <td><input type="checkbox" data-row-select aria-label="Select order CSO{{ $order->id }}"></td>
                            <td><strong data-cell>#CSO{{ $order->id }}</strong><small>{{ $order->items_count ?? '' }}</small>
                            </td>
                            <td><strong data-cell>{{ $order->customer?->name ?? '—' }}</strong><small
                                    data-cell>{{ $order->customer?->email ?? '—' }}</small></td>
                            <td data-cell>
                                {{ $order->created_at->format('d M Y') }}<small>{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td><strong
                                    data-cell>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }}</strong><small>{{ $order->currency }}</small>
                            </td>
                            <td><span class="order-payment payment-{{ $order->payment?->verified_status ?? 'none' }}"
                                    data-cell>{{ ucfirst($order->payment?->verified_status ?? 'No payment') }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}">@csrf
                                    @method('PATCH')<select name="status"
                                        class="order-status-select status-{{ $order->status }}"
                                        data-current="{{ $order->status }}"
                                        onchange="if(confirm('Change this order status?'))this.form.submit();else this.value=this.dataset.current">@foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected($order->status === $status)>
                                        {{ ucfirst(str_replace('_', ' ', $status)) }}</option>@endforeach
                                    </select></form>
                            </td>
                            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">View
                                    details</a></td>
                        </tr>
                    @empty<tr>
                        <td colspan="8">
                            <div class="analytics-empty">No orders match your filters.</div>
                        </td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="publisher-table-footer"><span>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of
                {{ $orders->total() }} orders</span>@if($orders->hasPages())
                    <nav class="order-pagination" aria-label="Order pages">@if($orders->onFirstPage())<span
                    class="disabled">Previous</span>@else<a href="{{ $orders->previousPageUrl() }}">Previous</a>@endif
                        @foreach(range(1, $orders->lastPage()) as $page)<a href="{{ $orders->url($page) }}"
                        class="{{ $orders->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                        @if($orders->hasMorePages())<a href="{{ $orders->nextPageUrl() }}">Next</a>@else<span
                class="disabled">Next</span>@endif</nav>@endif
        </div>
    </div>
    <script>(() => { const root = document.querySelector('[data-order-table]'), rows = [...root.querySelectorAll('[data-export-row]')], all = root.querySelector('[data-select-all]'), count = root.querySelector('[data-selection-count]'); const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked), update = () => { const n = selected().length; count.textContent = `${n} selected`; if (all) { all.checked = n === rows.length && n > 0; all.indeterminate = n > 0 && n < rows.length } }; all?.addEventListener('change', () => { rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked); update() }); rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update)); const ids = () => selected().map(r => r.dataset.id).join(','); root.querySelector('[data-copy]')?.addEventListener('click', async e => { const chosen = selected().length ? selected() : rows, text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n'); await navigator.clipboard.writeText(text); e.target.textContent = 'Copied'; setTimeout(() => e.target.textContent = 'Copy', 1200) }); root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => { const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin), params = new URLSearchParams(location.search); params.delete('page'); if (ids()) params.set('ids', ids()); url.search = params; b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url })); })();</script>
@endsection