@extends('layouts.dashboard')
@php $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Sales';
$logoutRoute = route('publisher.logout'); @endphp
@section('title', 'Payments & Invoices')
@section('nav')@include('publisher.partials.nav', ['active' => 'payments'])@endsection
@section('content')
<style>
    .payment-page-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        margin-bottom: 20px
    }

    .payment-page-head h2 {
        font: italic 600 1.8rem var(--font-display);
        margin: 7px 0
    }

    .payment-page-head p {
        margin: 0;
        color: var(--a-text-muted)
    }

    .payment-manual-note {
        padding: 8px 12px;
        border-radius: 99px;
        background: #fff3dc;
        color: #a6630c;
        font-size: .7rem;
        font-weight: 800
    }

    .payment-summary-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 14px;
        margin-bottom: 18px
    }

    .payment-summary-grid>div {
        padding: 17px 19px;
        background: var(--a-surface);
        border: 1px solid var(--a-border);
        border-radius: 12px
    }

    .payment-summary-grid span,
    .payment-summary-grid strong,
    .payment-summary-grid small {
        display: block
    }

    .payment-summary-grid span {
        color: var(--a-text-muted);
        font-size: .72rem;
        font-weight: 700
    }

    .payment-summary-grid strong {
        font: italic 600 1.45rem var(--font-display);
        margin: 8px 0 4px
    }

    .payment-summary-grid small {
        color: var(--a-text-muted);
        font-size: .64rem
    }

    .publisher-payment-card {
        padding: 0;
        overflow: hidden
    }

    .publisher-payment-toolbar {
        display: grid;
        grid-template-columns: 180px 180px auto auto;
        gap: 9px;
        padding: 18px 20px;
        border-bottom: 1px solid var(--a-border)
    }

    .publisher-payment-table {
        min-width: 1100px
    }

    .publisher-payment-table td small {
        display: block;
        color: var(--a-text-muted);
        font-size: .65rem;
        margin-top: 3px
    }

    @media(max-width:850px) {
        .payment-summary-grid {
            grid-template-columns: repeat(2, 1fr)
        }

        .publisher-payment-toolbar {
            grid-template-columns: 1fr 1fr
        }

        .payment-page-head {
            align-items: flex-start;
            flex-direction: column
        }
    }

    @media(max-width:520px) {

        .payment-summary-grid,
        .publisher-payment-toolbar {
            grid-template-columns: 1fr
        }
    }
</style>

<div class="payment-page-head">
    <div>
        <span class="analytics-eyebrow">Manual payment records</span>
        <h2>Payments and invoices</h2>
        <p>Amounts appear after the administrator verifies the customer's UTR payment.</p>
    </div>
    <span class="payment-manual-note">No payment gateway connected</span>
</div>
<div class="payment-summary-grid">
    <div>
        <span>Net verified earnings</span>
        <strong>₹{{ number_format(($totals->verified ?? 0) - ($totals->deductions ?? 0), 2) }}</strong>
        <small>Gross sales minus deductions</small>
    </div>
    <div>
        <span>Pending verification</span>
        <strong>₹{{ number_format($totals->pending ?? 0, 2) }}</strong>
        <small>Awaiting admin review</small>
    </div>
    <div>
        <span>Verified orders</span>
        <strong>{{ number_format($totals->paid_orders ?? 0) }}</strong>
        <small>Orders containing your books</small>
    </div>
    <div>
        <span>Publisher deductions</span>
        <strong>₹{{ number_format($totals->deductions ?? 0, 2) }}</strong>
        <small>Commission deducted from verified sales</small>
    </div>
</div>
<section class="a-card publisher-payment-card">
    <form method="GET" class="publisher-payment-toolbar">
        <select name="period" class="a-select">
            <option value="month" @selected($period === 'month')>This month</option>
            <option value="year" @selected($period === 'year')>This year</option>
            <option value="all" @selected($period === 'all')>All time</option>
        </select>
        <select name="payment" class="a-select">
            <option value="">All payments</option>
            <option value="pending" @selected($paymentStatus === 'pending')>Pending</option>
            <option value="verified" @selected($paymentStatus === 'verified')>Verified</option>
            <option value="rejected" @selected($paymentStatus === 'rejected')>Rejected</option>
        </select>
        <button class="btn btn-primary">Filter</button>
        <a href="{{ route('publisher.payments.index') }}" class="btn btn-outline">Reset</a>
    </form>
    <div class="publisher-table-scroll">
        <table class="a-table publisher-payment-table">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Order</th>
                    <th>Customer</th>
                    <th>Date</th>
                    <th>My gross amount</th>
                    <th>Payment</th>
                    <th>Verified on</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                @php($publisherGross = $order->items->sum(fn($item) => $item->quantity * ($item->base_unit_price ?? $item->unit_price)))
                <tr>
                    <td><strong>INV-P{{ auth()->user()->publisher->id }}-{{ $order->id }}</strong></td>
                    <td>#CSO{{ $order->id }}</td>
                    <td><strong>{{ $order->customer?->name ?? '—' }}</strong><small>{{ $order->customer?->email }}</small>
                    </td>
                    <td>{{ $order->created_at->format('d M Y') }}</td>
                    <td><strong>₹{{ number_format($publisherGross, 2) }}</strong></td>
                    <td><span
                            class="order-payment payment-{{ $order->payment?->verified_status ?? 'none' }}">{{ ucfirst($order->payment?->verified_status ?? 'No payment') }}</span>
                    </td>
                    <td>{{ $order->payment?->verified_at?->format('d M Y, h:i A') ?? '—' }}</td>
                    <td><a target="_blank" href="{{ route('publisher.payments.invoice', $order) }}"
                            class="btn btn-outline btn-sm">View invoice</a></td>
                </tr>
                @empty<tr>
                    <td colspan="8" class="empty-state">No payment records match these filters.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
    <div class="publisher-table-footer">
        <span>
            Showing {{ $orders->firstItem() ?? 0 }}–{{ $orders->lastItem() ?? 0 }} of
            {{ $orders->total() }}
        </span>
        <div class="order-pagination">
            @if($orders->previousPageUrl())
                <a href="{{ $orders->previousPageUrl() }}">Previous</a>
            @else
                <span class="disabled">Previous</span>
            @endif
            
            @if($orders->nextPageUrl())
                <a href="{{ $orders->nextPageUrl() }}">Next</a>
            @else
                <span class="disabled">Next</span>
            @endif
        </div>
    </div>
</section>

<div class="a-grid a-grid-2" style="margin-top:18px;align-items:start;">
    <section class="a-card" style="padding:20px;">
        <div class="a-card-head dashboard-card-title"><div><h3>Request payout</h3><p>Available after delivery plus the 7-day return window.</p></div></div>
        <div class="payment-summary-grid" style="grid-template-columns:repeat(3,1fr);margin:12px 0;">
            <div><span>Available</span><strong>₹{{ number_format(max(0, $availableBalance - $reservedBalance), 2) }}</strong></div>
            <div><span>Pending release</span><strong>₹{{ number_format($pendingBalance, 2) }}</strong></div>
            <div><span>Reserved</span><strong>₹{{ number_format($reservedBalance, 2) }}</strong></div>
        </div>
        <form method="POST" action="{{ route('publisher.payouts.store') }}">@csrf
            <label class="a-label">Amount</label><input class="a-input" type="number" name="amount" min="1" step="0.01" required>
            <label class="a-label" style="margin-top:10px;">Bank / UPI payment details</label><textarea class="a-input" name="payment_details" rows="3" maxlength="1000" required placeholder="Account holder, bank, account number and IFSC, or UPI ID"></textarea>
            <button class="btn btn-primary" style="margin-top:12px;">Submit payout request</button>
        </form>
    </section>
    <section class="a-card">
        <div class="a-card-head dashboard-card-title"><div><h3>Payout history</h3><p>Manual settlement status and references.</p></div></div>
        <div class="publisher-table-scroll"><table class="a-table"><thead><tr><th>Date</th><th>Amount</th><th>Status</th><th>Reference</th></tr></thead><tbody>
            @forelse($payouts as $payout)<tr><td>{{ $payout->created_at->format('d M Y') }}</td><td>₹{{ number_format($payout->amount, 2) }}</td><td><span class="badge badge-info">{{ ucfirst($payout->status) }}</span></td><td>{{ $payout->reference ?? '—' }}</td></tr>
            @empty<tr><td colspan="4">No payout requests yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>

<section class="a-card" style="margin-top:18px;">
    <div class="a-card-head dashboard-card-title"><div><h3>Earnings ledger</h3><p>Auditable sales, discounts, commission, refunds, and payouts.</p></div><a class="btn btn-outline btn-sm" href="{{ route('publisher.payments.statement') }}">Download statement</a></div>
    <div class="publisher-table-scroll"><table class="a-table"><thead><tr><th>Date</th><th>Type</th><th>Description</th><th>Gross</th><th>Discount</th><th>Commission</th><th>Net change</th><th>Available</th></tr></thead><tbody>
        @forelse($ledgerEntries as $entry)<tr><td>{{ $entry->created_at->format('d M Y') }}</td><td>{{ ucfirst($entry->type) }}</td><td>{{ $entry->description }}</td><td>₹{{ number_format($entry->gross_amount, 2) }}</td><td>₹{{ number_format($entry->discount_amount, 2) }}</td><td>₹{{ number_format($entry->commission_amount, 2) }}</td><td style="color:{{ $entry->amount < 0 ? '#c43d3d' : '#078657' }};font-weight:700;">{{ $entry->amount < 0 ? '−' : '+' }}₹{{ number_format(abs($entry->amount), 2) }}</td><td>{{ $entry->available_at?->format('d M Y') ?? 'After delivery' }}</td></tr>
        @empty<tr><td colspan="8">No ledger entries yet. New entries appear when payments are verified.</td></tr>@endforelse
    </tbody></table></div>
</section>
@endsection
