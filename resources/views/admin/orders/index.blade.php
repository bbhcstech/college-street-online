@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Operations';
    $logoutRoute = route('admin.logout');
    $statusBadgeClasses = [
        'pending_payment' => 'badge-gold',
        'confirmed' => 'badge-info',
        'processing' => 'badge-purple',
        'packed' => 'badge-orange',
        'shipped' => 'badge-blue',
        'delivered' => 'badge-success',
        'completed' => 'badge-success',
        'cancelled' => 'badge-danger',
        'return_requested' => 'badge-orange',
        'returned' => 'badge-muted',
    ];
@endphp
@section('title', 'All Orders')
@section('nav')@include('admin.partials.nav', ['active' => 'orders'])@endsection
@section('content')
<div class="a-card">
    <table class="a-table"><thead><tr><th>Order</th><th>Customer</th><th>Total</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($orders as $order)
        <tr>
            <td>#CSO{{ $order->id }}</td><td>{{ $order->customer->name ?? '—' }}</td><td>&#8377;{{ number_format($order->total_amount,0) }}</td>
            <td><span class="badge {{ $statusBadgeClasses[$order->status] ?? 'badge-muted' }}">{{ ucfirst(str_replace('_',' ',$order->status)) }}</span></td>
            <td><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm">View</a></td>
        </tr>
    @empty
        <tr><td colspan="5">No orders yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $orders->links() }}</div>
</div>
@endsection
