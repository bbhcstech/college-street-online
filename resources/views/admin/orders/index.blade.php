@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Operations';
    $logoutRoute = route('admin.logout');
    $statuses = ['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled', 'return_requested', 'returned'];
    $statusMap = [
        'pending_payment' => 'Pending Payment Orders',
        'confirmed' => 'Confirmed Orders',
        'processing' => 'Processing Orders',
        'packed' => 'Packed Orders',
        'shipped' => 'Shipped Orders',
        'delivered' => 'Delivered Orders',
        'completed' => 'Completed Orders',
        'cancelled' => 'Cancelled Orders',
        'return_requested' => 'Return & Refund Requests',
        'returned' => 'Returned Orders',
    ];
    $paymentMap = [
        'pending' => 'Payment Verification Queue',
        'verified' => 'Verified Payment Transactions',
        'failed' => 'Failed Payments',
    ];
    $pageTitle = $paymentMap[request('payment')] ?? ($statusMap[request('status')] ?? 'All Customer Orders');
@endphp
@section('title', $pageTitle)
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')
    <style>
        .order-id-badge {
            display: inline-block;
            padding: 4px 8px;
            background: color-mix(in srgb, var(--a-primary) 8%, var(--a-surface));
            color: var(--a-primary);
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.85rem;
            letter-spacing: 0.02em;
            font-family: monospace;
            border: 1px solid color-mix(in srgb, var(--a-primary) 20%, transparent);
        }
        .order-country-tag {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.74rem;
            font-weight: 700;
            background: var(--a-surface-alt);
            color: var(--a-text);
            border: 1px solid var(--a-border);
            text-transform: uppercase;
        }
        .payment-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 4px 10px;
            border-radius: 99px;
            font-size: 0.73rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .payment-pill.payment-verified {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            border: 1px solid rgba(16, 185, 129, 0.28);
        }
        .payment-pill.payment-pending {
            background: rgba(245, 158, 11, 0.12);
            color: #d97706;
            border: 1px solid rgba(245, 158, 11, 0.28);
        }
        .payment-pill.payment-rejected,
        .payment-pill.payment-failed {
            background: rgba(239, 68, 68, 0.12);
            color: #dc2626;
            border: 1px solid rgba(239, 68, 68, 0.28);
        }
        .payment-pill.payment-none {
            background: rgba(100, 116, 139, 0.1);
            color: #64748b;
            border: 1px solid rgba(100, 116, 139, 0.2);
        }
        .order-amount-text {
            font-size: 0.94rem;
            font-weight: 800;
            color: var(--a-text);
            line-height: 1.2;
            display: block;
        }
        .order-currency-text {
            font-size: 0.68rem;
            color: var(--a-text-muted);
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 1px;
            display: block;
        }
        .order-customer-wrap {
            min-width: 140px;
        }
        .order-customer-wrap strong {
            display: block;
            font-size: 0.88rem;
            color: var(--a-text);
            font-weight: 700;
            line-height: 1.2;
        }
        .order-customer-wrap small {
            display: block;
            font-size: 0.74rem;
            color: var(--a-text-muted);
            margin-top: 2px;
        }
        .order-date-wrap span {
            display: block;
            font-size: 0.84rem;
            font-weight: 700;
            color: var(--a-text);
            line-height: 1.2;
            white-space: nowrap;
        }
        .order-date-wrap small {
            display: block;
            font-size: 0.72rem;
            color: var(--a-text-muted);
            margin-top: 2px;
            white-space: nowrap;
        }
        .order-status-select {
            padding: 5px 8px;
            font-size: 0.78rem;
            font-weight: 700;
            border-radius: 6px;
            cursor: pointer;
            border: 1px solid var(--a-border);
            background: var(--a-surface);
            color: var(--a-text);
        }
        .order-data-table th, 
        .order-data-table td {
            padding: 10px 12px !important;
            vertical-align: middle;
        }
    </style>

    <div class="order-summary">
        <a href="{{ route('admin.orders.index') }}" class="summary-card {{ !request('status') && !request('payment') ? 'active' : '' }}">
            <i class="icon-total">#</i>
            <span>Total orders<small>All orders</small></span>
            <strong>{{ $totalOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}"
            class="summary-card pending {{ request('status') === 'pending_payment' ? 'active' : '' }}">
            <i class="icon-pending">!</i>
            <span>Pending<small>Needs payment</small></span>
            <strong>{{ $pendingOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}"
            class="summary-card processing {{ request('status') === 'processing' ? 'active' : '' }}">
            <i class="icon-processing">⚙</i>
            <span>Processing<small>In fulfillment</small></span>
            <strong>{{ $processingOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}"
            class="summary-card shipped {{ request('status') === 'shipped' ? 'active' : '' }}">
            <i class="icon-shipped">🚚</i>
            <span>Shipped<small>In transit</small></span>
            <strong>{{ $shippedOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}"
            class="summary-card delivered {{ request('status') === 'delivered' ? 'active' : '' }}">
            <i class="icon-delivered">✓</i>
            <span>Delivered<small>Fulfilled</small></span>
            <strong>{{ $deliveredOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'cancelled']) }}"
            class="summary-card cancelled {{ request('status') === 'cancelled' ? 'active' : '' }}">
            <i class="icon-cancelled">✕</i>
            <span>Cancelled<small>Voided</small></span>
            <strong>{{ $cancelledOrders }}</strong>
        </a>
        <a href="{{ route('admin.orders.index', ['status' => 'return_requested']) }}"
            class="summary-card returned {{ in_array(request('status'), ['return_requested', 'returned']) ? 'active' : '' }}">
            <i class="icon-returned">↩</i>
            <span>Return / Refund<small>Requests &amp; returns</small></span>
            <strong>{{ $returnedOrders }}</strong>
        </a>
    </div>

    <div class="a-card publisher-table-card" data-order-table data-export-base="{{ route('admin.orders.export', 'csv') }}">
        <form method="GET" class="order-table-toolbar">
            <div class="order-filter-main">
                <div class="publisher-search">
                    <span>⌕</span>
                    <input name="q" value="{{ request('q') }}" placeholder="Order ID, Customer name or email">
                </div>
                <select name="status" class="a-select">
                    <option value="">All statuses</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                        </option>
                    @endforeach
                </select>
                <select name="payment" class="a-select">
                    <option value="">All payments</option>
                    <option value="pending" @selected(request('payment') === 'pending')>Pending</option>
                    <option value="verified" @selected(request('payment') === 'verified')>Verified</option>
                    <option value="rejected" @selected(request('payment') === 'rejected')>Rejected</option>
                    <option value="none" @selected(request('payment') === 'none')>No payment</option>
                </select>
                <select name="country" class="a-select">
                    <option value="">All countries</option>
                    <option value="India" @selected(request('country') === 'India')>India</option>
                    @foreach($countries as $c)
                        @if($c !== 'India')
                            <option value="{{ $c }}" @selected(request('country') === $c)>{{ $c }}</option>
                        @endif
                    @endforeach
                </select>
                <button class="btn btn-primary btn-sm">Apply</button>
                @if(request()->query())
                    <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">Reset</a>
                @endif
            </div>

            <details class="order-filter-more" @if(request()->only(['date_from', 'date_to', 'min_price', 'max_price', 'per_page']) !== []) open @endif>
                <summary>More filters <span>Date range, price range &amp; entries</span></summary>
                <div class="order-filter-options">
                    <label><span>Date From</span><input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input"></label>
                    <label><span>Date To</span><input type="date" name="date_to" value="{{ request('date_to') }}" class="a-input"></label>
                    <label><span>Min Price (₹)</span><input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="Min ₹" class="a-input" step="0.01"></label>
                    <label><span>Max Price (₹)</span><input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="Max ₹" class="a-input" step="0.01"></label>
                    <label><span>Page Size</span>
                        <select name="per_page" class="a-select">
                            <option value="10" @selected($orders->perPage() === 10)>10 entries</option>
                            <option value="25" @selected($orders->perPage() === 25)>25 entries</option>
                            <option value="50" @selected($orders->perPage() === 50)>50 entries</option>
                            <option value="100" @selected($orders->perPage() === 100)>100 entries</option>
                        </select>
                    </label>
                </div>
            </details>
        </form>

        <div class="publisher-export-bar">
            <div><strong data-selection-count>0 selected</strong><span>Exports use selected rows, or all filtered orders when none are selected.</span></div>
            <div class="publisher-export-buttons">
                <span class="order-export-label">Export</span>
                <button type="button" class="btn btn-outline btn-sm" data-copy>Copy</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="excel">Excel</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="pdf">PDF</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="print">Print</button>
                <button type="button" class="btn btn-outline btn-sm" data-export="csv">CSV</button>
            </div>
        </div>

        <div class="publisher-table-scroll">
            <table class="a-table order-data-table">
                <thead>
                    <tr>
                        <th style="width: 38px; text-align: center;"><input type="checkbox" data-select-all aria-label="Select all orders"></th>
                        <th style="width: 100px;">Order ID</th>
                        <th style="width: 120px;">Date &amp; Time</th>
                        <th>Customer</th>
                        <th style="width: 85px; text-align: center;">Country</th>
                        <th style="width: 75px; text-align: center;">Items</th>
                        <th style="width: 110px;">Amount</th>
                        <th style="width: 130px; text-align: center;">Payment Status</th>
                        <th style="width: 140px;">Order Status</th>
                        <th style="width: 80px; text-align: right;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr data-export-row data-id="{{ $order->id }}">
                            <td style="text-align: center;">
                                <input type="checkbox" data-row-select aria-label="Select order CSO{{ $order->id }}">
                            </td>
                            <td>
                                <a href="{{ route('admin.orders.show', $order) }}" style="text-decoration:none;">
                                    <span class="order-id-badge" data-cell>#CSO{{ $order->id }}</span>
                                </a>
                            </td>
                            <td data-cell class="order-date-wrap">
                                <span>{{ $order->created_at->format('d M Y') }}</span>
                                <small>{{ $order->created_at->format('h:i A') }}</small>
                            </td>
                            <td class="order-customer-wrap">
                                <strong data-cell>{{ $order->customer?->name ?? 'Guest User' }}</strong>
                                <small data-cell>{{ $order->customer?->email ?? 'No email' }}</small>
                            </td>
                            <td style="text-align: center;">
                                <span class="order-country-tag" data-cell>{{ $order->country ?: 'IN' }}</span>
                            </td>
                            <td style="text-align: center;" data-cell>
                                <strong style="font-size:0.88rem; color:var(--a-text);">{{ $order->items_count ?? 0 }}</strong>
                                <small style="font-size:0.72rem; color:var(--a-text-muted); display:block;">items</small>
                            </td>
                            <td data-cell>
                                <span class="order-amount-text">{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }}</span>
                                <span class="order-currency-text">{{ $order->currency }}</span>
                            </td>
                            <td style="text-align: center;">
                                @php $payStatus = $order->payment?->verified_status ?? 'none'; @endphp
                                <span class="payment-pill payment-{{ $payStatus }}" data-cell>
                                    @if($payStatus === 'verified') ● Verified
                                    @elseif($payStatus === 'pending') ◷ Pending
                                    @elseif($payStatus === 'rejected') ✕ Rejected
                                    @else — No proof
                                    @endif
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.orders.status', $order) }}">
                                    @csrf @method('PATCH')
                                    <select name="status" class="order-status-select status-{{ $order->status }}"
                                        data-current="{{ $order->status }}"
                                        onchange="if(confirm('Change this order status?'))this.form.submit();else this.value=this.dataset.current">
                                        @foreach($statuses as $status)
                                            <option value="{{ $status }}" @selected($order->status === $status)>
                                                {{ ucfirst(str_replace('_', ' ', $status)) }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm"
                                    style="font-size: 0.8rem; padding: 5px 12px; font-weight: 700; border-radius: 6px;">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10">
                                <div class="analytics-empty order-empty">
                                    <strong>No orders found</strong>
                                    <span>Try changing or resetting your filters.</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="publisher-table-footer">
            <span>Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of {{ $orders->total() }} orders</span>
            @if($orders->hasPages())
                <nav class="order-pagination" aria-label="Order pages">
                    @if($orders->onFirstPage())
                        <span class="disabled">Previous</span>
                    @else
                        <a href="{{ $orders->previousPageUrl() }}">Previous</a>
                    @endif
                    @foreach(range(1, $orders->lastPage()) as $page)
                        <a href="{{ $orders->url($page) }}" class="{{ $orders->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>
                    @endforeach
                    @if($orders->hasMorePages())
                        <a href="{{ $orders->nextPageUrl() }}">Next</a>
                    @else
                        <span class="disabled">Next</span>
                    @endif
                </nav>
            @endif
        </div>
    </div>

    <script>
        (() => {
            const root = document.querySelector('[data-order-table]'),
                rows = [...root.querySelectorAll('[data-export-row]')],
                all = root.querySelector('[data-select-all]'),
                count = root.querySelector('[data-selection-count]');
            const selected = () => rows.filter(r => r.querySelector('[data-row-select]').checked),
                update = () => {
                    const n = selected().length;
                    count.textContent = `${n} selected`;
                    if (all) {
                        all.checked = n === rows.length && n > 0;
                        all.indeterminate = n > 0 && n < rows.length;
                    }
                };
            all?.addEventListener('change', () => {
                rows.forEach(r => r.querySelector('[data-row-select]').checked = all.checked);
                update();
            });
            rows.forEach(r => r.querySelector('[data-row-select]').addEventListener('change', update));
            const ids = () => selected().map(r => r.dataset.id).join(',');
            root.querySelector('[data-copy]')?.addEventListener('click', async e => {
                const chosen = selected().length ? selected() : rows,
                    text = chosen.map(r => [...r.querySelectorAll('[data-cell]')].map(c => c.textContent.trim()).join('\t')).join('\n');
                await navigator.clipboard.writeText(text);
                e.target.textContent = 'Copied';
                setTimeout(() => e.target.textContent = 'Copy', 1200);
            });
            root.querySelectorAll('[data-export]').forEach(b => b.addEventListener('click', () => {
                const url = new URL(root.dataset.exportBase.replace(/csv$/, b.dataset.export), location.origin),
                    params = new URLSearchParams(location.search);
                params.delete('page');
                if (ids()) params.set('ids', ids());
                url.search = params;
                b.dataset.export === 'print' || b.dataset.export === 'pdf' ? window.open(url, '_blank') : location.href = url;
            }));
        })();
    </script>
@endsection