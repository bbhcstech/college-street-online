@extends('layouts.dashboard')
@php($homeRoute = route('publisher.dashboard'))
@php($brandLabel = 'Publisher Panel')
@php($crumb = 'Sales')
@php($logoutRoute = route('publisher.logout'))
@section('title', 'Orders')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link active"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<div class="a-card">
    <table class="a-table"><thead><tr><th>Order</th><th>Customer</th><th>Book</th><th>Qty</th><th>Status</th></tr></thead><tbody>
    @forelse($items as $item)
        <tr><td>#CSO{{ $item->order_id }}</td><td>{{ $item->order->customer->name ?? '—' }}</td><td>{{ $item->book->title }}</td><td>{{ $item->quantity }}</td><td><span class="badge badge-success">{{ ucfirst(str_replace('_',' ',$item->order->status)) }}</span></td></tr>
    @empty
        <tr><td colspan="5">No orders yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $items->links() }}</div>
</div>
@endsection
