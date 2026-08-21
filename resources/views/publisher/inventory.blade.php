@extends('layouts.dashboard')
@php($homeRoute = route('publisher.dashboard'))
@php($brandLabel = 'Publisher Panel')
@php($crumb = 'Catalogue')
@php($logoutRoute = route('publisher.logout'))
@section('title', 'Inventory')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link active"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<form method="GET" class="topbar-search" style="max-width:400px;margin-bottom:20px;">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search books...">
</form>
<div class="a-card">
    <table class="a-table"><thead><tr><th>Title</th><th>Stock</th><th>Threshold</th><th></th></tr></thead><tbody>
    @forelse($inventory as $book)
        <tr>
            <td>{{ $book->title }}</td>
            <td>{{ $book->inventory->quantity ?? 0 }} {{ ($book->inventory && $book->inventory->isLowStock()) ? '⚠️' : '' }}</td>
            <td>{{ $book->inventory->low_stock_threshold ?? 5 }}</td>
            <td>
                <form method="POST" action="{{ route('publisher.inventory.restock', $book) }}" class="flex gap-2">
                    @csrf
                    <input type="number" name="quantity" placeholder="+qty" class="a-input" style="width:90px;" min="1" required>
                    <button class="btn btn-outline btn-sm">Restock</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="4">No books found.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $inventory->links() }}</div>
</div>
@endsection
