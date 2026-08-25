@extends('layouts.app')
@section('title', 'My Orders | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My Orders</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container">
        <h1>My Orders</h1>
        @forelse($orders as $order)
        <div class="card" style="margin-bottom:20px;">
            <div class="flex items-center" style="justify-content:space-between;flex-wrap:wrap;gap:10px;">
                <div><strong>Order #CSO{{ $order->id }}</strong><div style="font-size:0.82rem;color:var(--text-secondary);">Placed {{ $order->created_at->format('d M Y') }} &middot; {{ $order->items->count() }} items &middot; &#8377;{{ number_format($order->total_amount, 0) }}</div></div>
                <span class="badge {{ in_array($order->status, ['delivered','completed']) ? 'badge-muted' : 'badge-success' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span>
            </div>
            <details class="customer-tracking">
                <summary>Track order</summary>
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
            <p>You haven't placed any orders yet. <a href="{{ route('books.index') }}" style="color:var(--brand-primary);font-weight:600;">Start browsing &rarr;</a></p>
        @endforelse
        <div style="margin-top:24px;">{{ $orders->links() }}</div>
    </div>
</section>
@endsection
