@extends('layouts.app')
@section('title', 'My Orders | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My Orders</span></div></div>
<section class="section account-section">
    <div class="container">
        <div class="shopping-page-head"><div><span class="eyebrow"><span class="dot"></span> My account</span><h1>Orders &amp; Tracking</h1><p>Follow fulfillment progress and review your order history.</p></div><a class="btn btn-outline" href="{{ route('books.index') }}">Continue shopping</a></div>
        @forelse($orders as $order)
        <div class="card customer-order-card">
            <div class="flex items-center" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div><span class="order-label">Order number</span><strong>#CSO{{ $order->id }}</strong><div style="font-size:0.82rem;color:var(--text-secondary);">Placed {{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} items &middot; {{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }} {{ $order->currency }}</div></div>
                <span class="badge {{ in_array($order->status, ['delivered','completed']) ? 'badge-muted' : 'badge-success' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
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
                            <div class="status-step {{ $currentStep !== false && $index < $currentStep ? 'done' : '' }} {{ $currentStep === $index ? 'current' : '' }}">
                                <div class="dot">{{ $currentStep !== false && $index < $currentStep ? '✓' : $index + 1 }}</div>
                                <div class="lbl">{{ ucfirst(str_replace('_', ' ', $step)) }}</div>
                            </div>
                        @endforeach
                    </div>
                @endif
                <div class="item-fulfillment-list">
                    <h4>Book fulfillment</h4>
                    @foreach($order->items as $item)
                        <div>
                            <span>{{ $item->book?->title ?? 'Book unavailable' }}</span>
                            <strong>{{ ucfirst($item->fulfillment_status) }}</strong>
                        </div>
                    @endforeach
                </div>
                <div class="tracking-history">
                    @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                        <div><strong>{{ ucfirst(str_replace('_', ' ', $history->to_status)) }}</strong><span>{{ $history->created_at->format('d M Y, h:i A') }}</span></div>
                    @empty
                        <div><strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong><span>{{ $order->created_at->format('d M Y, h:i A') }}</span></div>
                    @endforelse
                </div>
            </details>
        </div>
        @empty
            <div class="shopping-empty"><span>&#128230;</span><h2>No orders yet</h2><p>Your placed orders and tracking updates will appear here.</p><a href="{{ route('books.index') }}" class="btn btn-primary">Start browsing</a></div>
        @endforelse
        <div style="margin-top:24px;">{{ $orders->links() }}</div>
    </div>
</section>
@endsection
