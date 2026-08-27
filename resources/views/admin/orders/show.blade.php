@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Operations')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Order #CSO' . $order->id)
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')
<div class="a-grid" style="grid-template-columns:minmax(0,1.6fr) minmax(300px,1fr);align-items:start;">
    <div style="min-width:0;">
        <div class="a-card">
            <h3 style="margin-top:0;">Items</h3>
            <table class="a-table">
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Qty</th>
                        <th>Unit Price</th>
                        <th>Fulfillment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>{{ $item->book?->title ?? 'Book unavailable' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $order->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                            <td>{{ ucfirst($item->fulfillment_status) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="a-card">
            <h3 style="margin-top:0;">Update Status</h3>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="a-select">
                    @foreach(['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled', 'return_requested', 'returned'] as $s)
                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-primary">Update</button>
            </form>
        </div>
        <div class="a-card">
            <h3 style="margin-top:0;">Status History</h3>
            <table class="a-table">
                <thead>
                    <tr>
                        <th>From</th>
                        <th>To</th>
                        <th>By</th>
                        <th>When</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($order->statusHistory as $h)
                        <tr>
                            <td>{{ $h->from_status ?? '—' }}</td>
                            <td>{{ $h->to_status }}</td>
                            <td>{{ $h->actor->name ?? 'System' }}</td>
                            <td>{{ $h->created_at->format('d M, H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">No history yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div style="min-width:0;">
        <div class="a-card">
            <h3 style="margin-top:0;">Customer</h3>
            <p>{{ $order->customer->name ?? '—' }}<br>{{ $order->customer->email ?? '' }}</p>
            <p style="font-size:0.85rem;color:var(--a-text-muted);">{{ $order->shipping_address }}</p>
        </div>
        @if($order->payment)
        <div class="a-card order-payment-review">
            <h3 style="margin-top:0;">Payment</h3>
            <div class="payment-review-utr"><span>Transaction reference
                    (UTR)</span><strong>{{ $order->payment->utr_number }}</strong></div>
            <p>Status: <span
                    class="badge {{ $order->payment->verified_status === 'verified' ? 'badge-success' : 'badge-gold' }}">{{ ucfirst($order->payment->verified_status) }}</span>
            </p>
            @if($order->payment->proof_url)
            @php($proofExtension = strtolower(pathinfo($order->payment->proof_url, PATHINFO_EXTENSION)))
                <div style="margin:14px 0;max-width:100%;overflow:hidden;">
                    <strong>Payment Proof</strong>
                    @if(in_array($proofExtension, ['jpg', 'jpeg', 'png']))
                        <a href="{{ route('admin.payments.proof', $order->payment) }}" target="_blank" rel="noopener">
                            <img src="{{ route('admin.payments.proof', $order->payment) }}" alt="Payment proof"
                                style="display:block;width:100%;max-width:100%;height:220px;object-fit:contain;margin-top:8px;border:1px solid var(--a-border);border-radius:8px;box-sizing:border-box;">
                        </a>
                    @else
                        <a href="{{ route('admin.payments.proof', $order->payment) }}" target="_blank" rel="noopener"
                            class="btn btn-outline btn-sm" style="margin-top:8px;">View Payment Proof</a>
                    @endif
                </div>
            @else
            <p style="color:var(--a-text-muted);">No payment proof uploaded.</p>
            @endif
            @if($order->payment->verified_status === 'pending')
                <div class="payment-review-actions">
                    <form method="POST" action="{{ route('admin.payments.verify', $order->payment) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="decision" value="verified"><button class="btn btn-primary btn-sm">Verify
                            payment</button>
                    </form>
                    <form method="POST" action="{{ route('admin.payments.verify', $order->payment) }}"
                        onsubmit="return confirm('Reject this payment proof?')">@csrf @method('PATCH')<input type="hidden"
                            name="decision" value="rejected"><button class="btn btn-danger-outline btn-sm">Reject</button>
                    </form>
                </div>
            @endif
        </div>
        @endif
        <div class="a-card">
            <h3 style="margin-top:0;">Totals</h3>
            <div class="summary-line">
                <span>Subtotal</span><span>{{ $order->currency_symbol }}{{ number_format($order->subtotal, 2) }}</span>
            </div>
            <div class="summary-line">
                <span>Shipping</span><span>{{ $order->currency_symbol }}{{ number_format($order->shipping_fee, 2) }}</span>
            </div>
            <div class="summary-line"><span>Platform
                    Fee</span><span>{{ $order->currency_symbol }}{{ number_format($order->platform_fee, 2) }}</span>
            </div>
            <div class="summary-line">
                <span>Discount</span><span>&minus;{{ $order->currency_symbol }}{{ number_format($order->discount_amount, 2) }}</span>
            </div>
            <div class="summary-line total"><span>Total
                    ({{ $order->currency }})</span><span>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>
    </div>
</div>
@endsection