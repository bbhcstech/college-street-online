@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Overview')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Dashboard')
@section('nav')
@include('admin.partials.nav', ['active' => 'dashboard'])
@endsection
@section('content')
<div class="a-grid a-grid-4" style="margin-bottom:24px;">
    <div class="stat-box"><div class="num">{{ $publisherCount }}</div><div class="label">Active Publishers</div></div>
    <div class="stat-box"><div class="num">{{ $bookCount }}</div><div class="label">Books Listed</div></div>
    <div class="stat-box"><div class="num">{{ $orderCount }}</div><div class="label">Orders This Month</div></div>
    <div class="stat-box"><div class="num">{{ $pendingPayments }}</div><div class="label">UTR Payments to Verify</div></div>
</div>
<div class="a-grid" style="grid-template-columns:1.6fr 1fr;align-items:start;">
<div class="a-card">
    <div class="a-card-head"><h3 style="margin:0;">Pending UTR Verifications</h3></div>
    <table class="a-table"><thead><tr><th>Order</th><th>Customer</th><th>UTR</th><th>Amount</th><th></th></tr></thead><tbody>
    @forelse($recentPayments as $p)
        <tr>
            <td>#CSO{{ $p->order_id }}</td><td>{{ $p->order->customer->name ?? '—' }}</td><td>{{ $p->utr_number }}</td><td>&#8377;{{ number_format($p->order->total_amount,0) }}</td>
            <td class="flex gap-2">
                <form method="POST" action="{{ route('admin.payments.verify', $p) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="verified"><button class="btn btn-outline btn-sm">Verify</button></form>
                <form method="POST" action="{{ route('admin.payments.verify', $p) }}">@csrf @method('PATCH')<input type="hidden" name="decision" value="rejected"><button class="btn btn-danger btn-sm">Reject</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5">No pending verifications.</td></tr>
    @endforelse
    </tbody></table>
</div>
<div class="a-card"><div class="a-card-head"><h3 style="margin:0;">Order Status Mix</h3></div>
    <div style="display:flex;flex-direction:column;gap:9px;">
        @forelse($statusMix as $status => $count)
            <div class="flex items-center" style="justify-content:space-between;font-size:0.85rem;"><span>{{ ucfirst(str_replace('_',' ',$status)) }}</span><strong>{{ $count }}</strong></div>
        @empty
            <p style="font-size:0.85rem;">No orders yet.</p>
        @endforelse
    </div>
</div>
</div>
@endsection
