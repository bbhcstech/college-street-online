@extends('layouts.dashboard')
@php($homeRoute = route('publisher.dashboard'))
@php($brandLabel = 'Publisher Panel')
@php($crumb = 'Publisher')
@php($logoutRoute = route('publisher.logout'))
@section('title', 'Dashboard')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link active"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<div class="a-grid a-grid-4" style="margin-bottom:24px;">
    <div class="stat-bx"><div class="num">{{ $bookCount }}</div><div class="label">Books Listed</div></div>
    <div class="stat-box"><div class="num">{{ $lowStockCount }}</div><div class="label">Low Stock Titles</div></div>
</div>
<div class="a-card">
    <div class="a-card-head"><h3 style="margin:0;">Recent Orders</h3></div>
    <table class="a-table"><thead><tr><th>Book</th><th>Order</th><th>Qty</th><th>Status</th></tr></thead><tbody>
    @forelse($recentOrderItems as $item)
        <tr><td>{{ $item->book->title }}</td><td>#CSO{{ $item->order_id }}</td><td>{{ $item->quantity }}</td><td><span class="badge badge-success">{{ ucfirst(str_replace('_',' ',$item->order->status)) }}</span></td></tr>
    @empty
        <tr><td colspan="4">No orders yet.</td></tr>
    @endforelse
    </tbody></table>
</div>
@endsection
