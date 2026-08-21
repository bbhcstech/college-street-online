@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Operations')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Order #CSO' . $order->id)
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')
<div class="a-grid" style="grid-template-columns:1.6fr 1fr;align-items:start;">
<div>
<div class="a-card">
    <h3 style="margin-top:0;">Items</h3>
    <table class="a-table"><thead><tr><th>Book</th><th>Qty</th><th>Unit Price</th></tr></thead><tbody>
    @foreach($order->items as $item)
        <tr><td>{{ $item->book->title }}</td><td>{{ $item->quantity }}</td><td>&#8377;{{ number_format($item->unit_price,0) }}</td></tr>
    @endforeach
    </tbody></table>
</div>
<div class="a-card">
    <h3 style="margin-top:0;">Update Status</h3>
    <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">
        @csrf @method('PATCH')
        <select name="status" class="a-select">
            @foreach(['pending_payment','confirmed','processing','packed','shipped','delivered','completed','cancelled','return_requested','returned'] as $s)
                <option value="{{ $s }}" {{ $order->status===$s?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
            @endforeach
        </select>
        <button class="btn btn-primary">Update</button>
    </form>
</div>
<div class="a-card">
    <h3 style="margin-top:0;">Status History</h3>
    <table class="a-table"><thead><tr><th>From</th><th>To</th><th>By</th><th>When</th></tr></thead><tbody>
    @forelse($order->statusHistory as $h)
        <tr><td>{{ $h->from_status ?? '—' }}</td><td>{{ $h->to_status }}</td><td>{{ $h->actor->name ?? 'System' }}</td><td>{{ $h->created_at->format('d M, H:i') }}</td></tr>
    @empty
        <tr><td colspan="4">No history yet.</td></tr>
    @endforelse
    </tbody></table>
</div>
</div>
<div>
<div class="a-card">
    <h3 style="margin-top:0;">Customer</h3>
    <p>{{ $order->customer->name ?? '—' }}<br>{{ $order->customer->email ?? '' }}</p>
    <p style="font-size:0.85rem;color:var(--a-text-muted);">{{ $order->shipping_address }}</p>
</div>
@if($order->payment)
<div class="a-card">
    <h3 style="margin-top:0;">Payment</h3>
    <p>UTR: <strong>{{ $order->payment->utr_number }}</strong></p>
    <p>Status: <span class="badge {{ $order->payment->verified_status==='verified'?'badge-success':'badge-gold' }}">{{ ucfirst($order->payment->verified_status) }}</span></p>
    @if($order->payment->verified_status === 'pending')
    <form method="POST" action="{{ route('admin.payments.verify', $order->payment) }}" class="flex gap-2">
        @csrf @method('PATCH')
        <input type="hidden" name="decision" value="verified"><button class="btn btn-outline btn-sm">Verify</button>
    </form>
    @endif
</div>
@endif
<div class="a-card">
    <h3 style="margin-top:0;">Totals</h3>
    <div class="summary-line"><span>Subtotal</span><span>&#8377;{{ number_format($order->subtotal,0) }}</span></div>
    <div class="summary-line"><span>Shipping</span><span>&#8377;{{ number_format($order->shipping_fee,0) }}</span></div>
    <div class="summary-line"><span>Platform Fee</span><span>&#8377;{{ number_format($order->platform_fee,0) }}</span></div>
    <div class="summary-line"><span>Discount</span><span>&minus;&#8377;{{ number_format($order->discount_amount,0) }}</span></div>
    <div class="summary-line total"><span>Total</span><span>&#8377;{{ number_format($order->total_amount,0) }}</span></div>
</div>
</div>
</div>
@endsection
