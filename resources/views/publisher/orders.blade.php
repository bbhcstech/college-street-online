@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Sales';
    $logoutRoute = route('publisher.logout');
@endphp
@section('title', 'Orders')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Marketing</div><a href="{{ route('publisher.coupons.index') }}" class="nav-link"><span class="nav-icon">🏷</span><span>Coupons & Offers</span></a></div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link active"><span class="nav-icon">&#128666;</span><span>Orders</span></a><a href="{{ route('publisher.payments.index') }}" class="nav-link"><span class="nav-icon">&#8377;</span><span>Payments & Invoices</span></a></div>
<div class="nav-group"><div class="nav-group-title">Reports</div><a href="{{ route('publisher.analytics.index') }}" class="nav-link"><span class="nav-icon">&#128200;</span><span>Analytics & Reports</span></a></div>
@endsection
@section('content')
<div class="a-card">
    <table class="a-table"><thead><tr><th>Order</th><th>Customer</th><th>Book</th><th>Qty</th><th>Overall</th><th>My Item</th><th>Action</th></tr></thead><tbody>
    @forelse($items as $item)
        @php
            $nextStatuses = ['pending' => 'processing', 'processing' => 'packed', 'packed' => 'shipped'];
            $nextStatus = $nextStatuses[$item->fulfillment_status] ?? null;
            $canUpdate = $nextStatus && in_array($item->order->status, ['confirmed', 'processing', 'packed', 'shipped'], true);
        @endphp
        <tr>
            <td>#CSO{{ $item->order_id }}</td>
            <td>{{ $item->order->customer->name ?? '—' }}</td>
            <td>{{ $item->book?->title ?? 'Book unavailable' }}</td>
            <td>{{ $item->quantity }}</td>
            <td><span class="badge badge-muted">{{ ucfirst(str_replace('_', ' ', $item->order->status)) }}</span></td>
            <td><span class="badge {{ $item->fulfillment_status === 'shipped' ? 'badge-success' : 'badge-gold' }}">{{ ucfirst($item->fulfillment_status) }}</span></td>
            <td>
                @if($canUpdate)
                    <form method="POST" action="{{ route('publisher.orders.items.status', $item) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="{{ $nextStatus }}">
                        <button type="submit" class="btn btn-primary btn-sm">Mark {{ ucfirst($nextStatus) }}</button>
                    </form>
                @elseif($item->order->status === 'pending_payment')
                    <span style="color:var(--a-text-muted);font-size:0.8rem;">Awaiting payment</span>
                @else
                    <span style="color:var(--a-text-muted);font-size:0.8rem;">No action</span>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7">No orders yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $items->links() }}</div>
</div>
@endsection
