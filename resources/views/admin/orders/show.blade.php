@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Operations')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Order #CSO' . $order->id)
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')

<div class="publisher-page-head" style="margin-bottom: 20px; display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 12px;">
    <div>
        <a href="{{ route('admin.orders.index') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 0.8rem; color: var(--a-primary); text-decoration: none; font-weight: 700; margin-bottom: 6px;">← Back to All Orders</a>
        <h2 style="margin: 0; font-size: 1.6rem;">Order #CSO{{ $order->id }}</h2>
        <p style="margin: 4px 0 0; color: var(--a-text-muted); font-size: 0.85rem;">Placed on {{ $order->created_at->format('d F Y \a\t h:i A') }}</p>
    </div>
    <div style="display: flex; align-items: center; gap: 10px;">
        <span class="order-status-select status-{{ $order->status }}" style="font-size: 0.85rem; padding: 6px 16px;">
            {{ ucfirst(str_replace('_', ' ', $order->status)) }}
        </span>
    </div>
</div>

<div class="a-grid" style="grid-template-columns: minmax(0, 1.65fr) minmax(320px, 1fr); align-items: start; gap: 20px;">
    <!-- LEFT COLUMN -->
    <div style="min-width: 0; display: flex; flex-direction: column; gap: 20px;">
        
        <!-- SECTION 1: Order Information & Books / Items -->
        <div class="a-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--a-border); display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1.05rem;">Books / Items</h3>
                <span class="badge badge-outline">{{ $order->items->count() }} line items</span>
            </div>
            <div style="overflow-x: auto;">
                <table class="a-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>Book / Title</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total Amount</th>
                            <th>Fulfillment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <strong>{{ $item->book?->title ?? 'Book unavailable' }}</strong>
                                    @if($item->book?->author_name)
                                        <small style="display: block; color: var(--a-text-muted);">by {{ $item->book->author_name }}</small>
                                    @endif
                                </td>
                                <td><strong>{{ $item->quantity }}</strong></td>
                                <td>{{ $order->currency_symbol }}{{ number_format($item->unit_price, 2) }}</td>
                                <td><strong>{{ $order->currency_symbol }}{{ number_format($item->quantity * $item->unit_price, 2) }}</strong></td>
                                <td><span class="badge badge-outline" style="text-transform: capitalize;">{{ str_replace('_', ' ', $item->fulfillment_status) }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- SECTION 2: Shipping and Tracking & Status Update -->
        <div class="a-card" style="padding: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.05rem;">Shipping &amp; Tracking Operations</h3>
            <form method="POST" action="{{ route('admin.orders.status', $order) }}" style="display: flex; flex-direction: column; gap: 12px;">
                @csrf @method('PATCH')
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--a-text-muted); margin-bottom: 4px;">Update Order Status</label>
                        <select name="status" class="a-select" style="width: 100%;">
                            @foreach(['pending_payment', 'confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed', 'cancelled', 'return_requested', 'returned'] as $s)
                                <option value="{{ $s }}" @selected($order->status === $s)>
                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.75rem; font-weight: 700; color: var(--a-text-muted); margin-bottom: 4px;">Courier Tracking Number</label>
                        <input type="text" name="tracking_number" class="a-input" placeholder="e.g. AWB987654321" value="{{ old('tracking_number', $order->tracking_number) }}" style="width: 100%;">
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 4px;">
                    @if($order->tracking_number)
                        <span style="font-size: 0.82rem; color: var(--a-text-muted);">Current Tracking #: <strong style="color: var(--a-primary);">{{ $order->tracking_number }}</strong></span>
                    @else
                        <span style="font-size: 0.82rem; color: var(--a-text-muted);">No courier tracking number assigned yet.</span>
                    @endif
                    <button class="btn btn-primary btn-sm">Update Status &amp; Tracking</button>
                </div>
            </form>
        </div>

        <!-- SECTION 3: Order Timeline -->
        <div class="a-card" style="padding: 0; overflow: hidden;">
            <div style="padding: 16px 20px; border-bottom: 1px solid var(--a-border);">
                <h3 style="margin: 0; font-size: 1.05rem;">Order Timeline (Audit History)</h3>
            </div>
            <div style="overflow-x: auto;">
                <table class="a-table" style="margin: 0;">
                    <thead>
                        <tr>
                            <th>From Status</th>
                            <th>To Status</th>
                            <th>Changed By</th>
                            <th>Date &amp; Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($order->statusHistory as $h)
                            <tr>
                                <td><span class="badge badge-outline" style="font-size: 0.75rem;">{{ $h->from_status ? ucfirst(str_replace('_', ' ', $h->from_status)) : '—' }}</span></td>
                                <td><strong style="color: var(--a-primary);">{{ ucfirst(str_replace('_', ' ', $h->to_status)) }}</strong></td>
                                <td>{{ $h->actor->name ?? 'System / Customer' }}</td>
                                <td>{{ $h->created_at->format('d M Y, h:i A') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="color: var(--a-text-muted); text-align: center; padding: 16px;">No status change log recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- RIGHT COLUMN -->
    <div style="min-width: 0; display: flex; flex-direction: column; gap: 20px;">
        
        <!-- SECTION 4: Customer Information & Shipping Address -->
        <div class="a-card" style="padding: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.05rem;">Customer Information</h3>
            <div style="margin-bottom: 14px;">
                <strong style="font-size: 0.95rem; display: block; color: var(--a-text);">{{ $order->customer->name ?? 'Guest / Unavailable' }}</strong>
                <span style="font-size: 0.82rem; color: var(--a-text-muted); display: block;">{{ $order->customer->email ?? 'No email on file' }}</span>
                @if($order->shipping_phone)
                    <span style="font-size: 0.82rem; color: var(--a-text-muted); display: block; margin-top: 2px;">Phone: <strong>{{ $order->shipping_phone }}</strong></span>
                @endif
            </div>
            <div style="border-top: 1px solid var(--a-border); padding-top: 12px;">
                <h4 style="margin: 0 0 6px 0; font-size: 0.82rem; font-weight: 800; text-transform: uppercase; color: var(--a-text-muted); letter-spacing: 0.05em;">Shipping Address</h4>
                <p style="margin: 0; font-size: 0.85rem; line-height: 1.5; color: var(--a-text);">{{ $order->shipping_address ?: 'No address specified' }}</p>
                <div style="margin-top: 8px;">
                    <span class="badge badge-outline" style="font-weight: 700;">Country: {{ $order->country ?: 'India' }}</span>
                </div>
            </div>
        </div>

        <!-- SECTION 5: Price Breakdown -->
        <div class="a-card" style="padding: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 14px; font-size: 1.05rem;">Price Breakdown</h3>
            <div class="summary-line" style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.85rem; border-bottom: 1px dashed var(--a-border);">
                <span style="color: var(--a-text-muted);">Items Subtotal</span>
                <strong>{{ $order->currency_symbol }}{{ number_format($order->subtotal, 2) }}</strong>
            </div>
            <div class="summary-line" style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.85rem; border-bottom: 1px dashed var(--a-border);">
                <span style="color: var(--a-text-muted);">Shipping Charge</span>
                <strong>{{ $order->currency_symbol }}{{ number_format($order->shipping_fee, 2) }}</strong>
            </div>
            <div class="summary-line" style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.85rem; border-bottom: 1px dashed var(--a-border);">
                <span style="color: var(--a-text-muted);">Platform Fee</span>
                <strong>{{ $order->currency_symbol }}{{ number_format($order->platform_fee, 2) }}</strong>
            </div>
            @if($order->discount_amount > 0 || $order->coupon)
                <div class="summary-line" style="display: flex; justify-content: space-between; padding: 6px 0; font-size: 0.85rem; border-bottom: 1px dashed var(--a-border); color: #078657;">
                    <span>Discount @if($order->coupon?->code) ({{ $order->coupon->code }}) @endif</span>
                    <strong>&minus;{{ $order->currency_symbol }}{{ number_format($order->discount_amount, 2) }}</strong>
                </div>
            @endif
            <div class="summary-line total" style="display: flex; justify-content: space-between; padding: 10px 0 0 0; font-size: 1.1rem; font-weight: 800; color: var(--a-primary);">
                <span>Final Amount ({{ $order->currency }})</span>
                <span>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }}</span>
            </div>
        </div>

        <!-- SECTION 6: Payment Information -->
        <div class="a-card order-payment-review" style="padding: 20px;">
            <h3 style="margin-top: 0; margin-bottom: 12px; font-size: 1.05rem;">Payment Information</h3>
            @if($order->payment)
                <p style="margin: 0 0 8px 0; font-size: 0.85rem;"><strong>Method:</strong> {{ strtoupper(str_replace('_', ' ', $order->payment->payment_method ?? 'Manual UPI')) }}</p>
                <div class="payment-review-utr" style="margin-bottom: 10px; padding: 10px 12px; background: var(--a-surface-alt); border-radius: 8px;">
                    <span style="display: block; font-size: 0.68rem; color: var(--a-text-muted); text-transform: uppercase; font-weight: 700;">Transaction Reference / UTR</span>
                    <strong style="font-size: 0.9rem; color: var(--a-primary);">{{ $order->payment->utr_number ?? 'N/A' }}</strong>
                </div>
                <div style="margin-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 0.85rem;">Payment Status:</span>
                    <span class="badge {{ $order->payment->verified_status === 'verified' ? 'badge-success' : ($order->payment->verified_status === 'rejected' ? 'badge-danger' : 'badge-gold') }}">
                        {{ ucfirst($order->payment->verified_status) }}
                    </span>
                </div>
                @if($order->payment->rejection_reason)
                    <p style="color: #e53e3e; font-size: 0.85rem; margin: 6px 0; background: #fff5f5; padding: 8px 10px; border-radius: 6px; border: 1px solid #fed7d7;">
                        <strong>Rejection Reason:</strong> {{ $order->payment->rejection_reason }}
                    </p>
                @endif
                @if($order->payment->admin_notes)
                    <p style="color: var(--a-text-muted); font-size: 0.85rem; margin: 6px 0;">
                        <strong>Admin Notes:</strong> {{ $order->payment->admin_notes }}
                    </p>
                @endif
                @if($order->payment->proof_url)
                    @php($proofExtension = strtolower(pathinfo($order->payment->proof_url, PATHINFO_EXTENSION)))
                    <div style="margin: 14px 0;">
                        <strong style="display: block; font-size: 0.85rem; margin-bottom: 6px;">Payment Proof Screenshot</strong>
                        @if(in_array($proofExtension, ['jpg', 'jpeg', 'png']))
                            <a href="{{ route('admin.payments.proof', $order->payment) }}" target="_blank" rel="noopener">
                                <img src="{{ route('admin.payments.proof', $order->payment) }}" alt="Payment proof" style="display: block; width: 100%; height: 200px; object-fit: contain; border: 1px solid var(--a-border); border-radius: 8px;">
                            </a>
                        @else
                            <a href="{{ route('admin.payments.proof', $order->payment) }}" target="_blank" rel="noopener" class="btn btn-outline btn-sm">View Proof Document</a>
                        @endif
                    </div>
                @else
                    <p style="color: var(--a-text-muted); font-size: 0.85rem; margin: 8px 0;">No payment proof file attached.</p>
                @endif
                @if($order->payment->verified_status === 'pending')
                    <div class="payment-review-actions" style="margin-top: 14px; border-top: 1px solid var(--a-border); padding-top: 14px;">
                        <form method="POST" action="{{ route('admin.payments.verify', $order->payment) }}" style="margin-bottom: 10px;">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="verified">
                            <input type="text" name="admin_notes" class="a-input" placeholder="Admin notes (optional)" style="margin-bottom: 6px; font-size: 0.82rem; width: 100%;">
                            <button class="btn btn-primary btn-sm" style="width: 100%;">✓ Verify Payment</button>
                        </form>
                        <form method="POST" action="{{ route('admin.payments.verify', $order->payment) }}" onsubmit="return confirm('Reject this payment proof?')">
                            @csrf @method('PATCH')
                            <input type="hidden" name="decision" value="rejected">
                            <input type="text" name="rejection_reason" class="a-input" placeholder="Rejection reason (required for reject)" style="margin-bottom: 6px; font-size: 0.82rem; width: 100%;" required>
                            <button class="btn btn-danger-outline btn-sm" style="width: 100%;">✕ Reject Payment</button>
                        </form>
                    </div>
                @endif
            @else
                <p style="color: var(--a-text-muted); font-size: 0.85rem; margin: 0;">No payment record associated with this order.</p>
            @endif
        </div>
    </div>
</div>
@endsection