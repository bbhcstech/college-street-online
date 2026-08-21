@extends('layouts.dashboard')
@php($homeRoute = route('publisher.dashboard'))
@php($brandLabel = 'Publisher Panel')
@php($crumb = 'Catalogue')
@php($logoutRoute = route('publisher.logout'))
@section('title', 'My Books')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link active"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<div class="page-header"><div></div><a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add Book</a></div>
<div class="a-card">
    <table class="a-table"><thead><tr><th>Title</th><th>ISBN</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($books as $book)
        <tr>
            <td>{{ $book->title }}</td><td>{{ $book->isbn }}</td><td>&#8377;{{ number_format($book->price,0) }}</td>
            <td>{{ $book->inventory->quantity ?? 0 }}</td>
            <td><span class="badge {{ $book->status === 'active' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($book->status) }}</span></td>
            <td class="flex gap-2">
                <a href="{{ route('publisher.books.edit', $book) }}" class="btn btn-outline btn-sm">Edit</a>
                <form method="POST" action="{{ route('publisher.books.destroy', $book) }}" onsubmit="return confirm('Remove this book?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="6">No books yet. <a href="{{ route('publisher.books.create') }}">Add your first book &rarr;</a></td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $books->links() }}</div>
</div>
@endsection
