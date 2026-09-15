@extends('layouts.app')
@section('title', 'My Orders | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My
                Orders</span></div>
    </div>
    <section class="section account-section">
        <div class="container">
            <div class="shopping-page-head">
                <div><span class="eyebrow"><span class="dot"></span> My account</span>
                    <h1>Orders &amp; Tracking</h1>
                    <p>Follow fulfillment progress and review your order history.</p>
                </div><a class="btn btn-outline" href="{{ route('books.index') }}">Continue shopping</a>
            </div>
            @forelse($orders as $order)
                <div class="card customer-order-card">
                    <div class="flex items-center" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                        <div><span class="order-label">Order number</span><strong>#CSO{{ $order->id }}</strong>
                            <div style="font-size:0.82rem;color:var(--text-secondary);">Placed
                                {{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} items &middot;
                                {{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}
                            </div>
                        </div>
                        <span
                            class="badge {{ in_array($order->status, ['delivered', 'completed']) ? 'badge-muted' : 'badge-success' }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                    </div>
                    <details class="customer-tracking">
                        <summary><span>Track order</span><small>View fulfillment and status history</small></summary>
                        @php
                            $trackingSteps = ['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered'];
                            $currentStatus = $order->status === 'completed' ? 'delivered' : $order->status;
                            $currentStep = array_search($currentStatus, $trackingSteps, true);
                            $isStopped = in_array($order->status, ['cancelled', 'returned'], true);
                        @endphp
                        @if($isStopped)
                            <p class="tracking-notice">This order is {{ str_replace('_', ' ', $order->status) }}.</p>
                        @else
                            <div class="status-timeline customer-status-timeline">
                                @foreach($trackingSteps as $index => $step)
                                    <div
                                        class="status-step {{ $currentStep !== false && $index < $currentStep ? 'done' : '' }} {{ $currentStep === $index ? 'current' : '' }}">
                                        <div class="dot">{{ $currentStep !== false && $index < $currentStep ? '✓' : $index + 1 }}</div>
                                        <div class="lbl">{{ ucfirst(str_replace('_', ' ', $step)) }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                        <div class="item-fulfillment-list">
                            <h4>Book fulfillment</h4>
                            @foreach($order->items as $item)
                                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 6px;">
                                    <span>{{ $item->book?->title ?? 'Book unavailable' }} &times; {{ $item->quantity }}</span>
                                    <div>
                                        <strong style="margin-right:8px;">{{ $order->currency_symbol }}{{ number_format($item->quantity * $item->unit_price, 2) }}</strong>
                                        <span class="badge badge-outline" style="font-size:0.7rem;">{{ ucfirst($item->fulfillment_status) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div style="margin-top: 14px; padding: 12px; background: var(--bg-surface-alt, #f8fafc); border-radius: 8px; border: 1px solid var(--border-color, #e2e8f0); font-size: 0.85rem;">
                            <h4 style="margin: 0 0 8px 0; font-size: 0.9rem;">Order Financial Summary</h4>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:var(--text-secondary);">
                                <span>Items Subtotal</span>
                                <span>{{ $order->currency_symbol }}{{ number_format($order->subtotal, 2) }}</span>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:var(--text-secondary);">
                                <span>Shipping Fee</span>
                                <span>{{ $order->currency_symbol }}{{ number_format($order->shipping_fee, 2) }}</span>
                            </div>
                            @if(isset($order->tax_amount) && $order->tax_amount > 0)
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px; color:var(--text-secondary);">
                                    <span>Market Tax ({{ number_format($order->tax_rate, 1) }}% {{ $order->is_tax_inclusive ? 'incl.' : 'excl.' }})</span>
                                    <span>{{ $order->currency_symbol }}{{ number_format($order->tax_amount, 2) }}</span>
                                </div>
                            @endif
                            @if($order->discount_amount > 0)
                                <div style="display:flex; justify-content:space-between; margin-bottom:4px; color: #078657;">
                                    <span>Discount</span>
                                    <span>&minus;{{ $order->currency_symbol }}{{ number_format($order->discount_amount, 2) }}</span>
                                </div>
                            @endif
                            <div style="display:flex; justify-content:space-between; font-weight:700; font-size:0.95rem; margin-top:8px; border-top:1px solid var(--border-color, #cbd5e1); padding-top:6px;">
                                <span>Total Billed</span>
                                <span>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</span>
                            </div>
                        </div>
                        <div class="tracking-history" style="margin-top: 14px;">
                            @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</strong><span>{{ $history->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @empty
                                <div>
                                    <strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong><span>{{ $order->created_at->format('d M Y, h:i A') }}</span>
                                </div>
                            @endforelse
                        </div>
                    </details>
                </div>
            @empty
                <div class="shopping-empty"><span>&#128230;</span>
                    <h2>No orders yet</h2>
                    <p>Your placed orders and tracking updates will appear here.</p><a href="{{ route('books.index') }}"
                        class="btn btn-primary">Start browsing</a>
                </div>
            @endforelse
            <div style="margin-top:24px;">{{ $orders->links() }}</div>
        </div>
    </section>
@endsection