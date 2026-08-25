@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'All Books')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
<div class="page-header"><div></div><a href="{{ route('admin.books.create') }}" class="btn btn-primary">+ Add Book</a></div>
<form method="GET" class="book-admin-filters">
    <input type="text" name="q" value="{{ request('q') }}" class="a-input" placeholder="Search title, ISBN, author or publisher">
    <select name="publisher_id" class="a-select"><option value="">All publishers</option>@foreach($publishers as $publisher)<option value="{{ $publisher->id }}" {{ (string) request('publisher_id') === (string) $publisher->id ? 'selected' : '' }}>{{ $publisher->business_name }}</option>@endforeach</select>
    <select name="category_id" class="a-select"><option value="">All categories</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string) request('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select>
    <select name="status" class="a-select"><option value="">All statuses</option><option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option></select>
    <select name="deleted" class="a-select"><option value="">Current books</option><option value="with" {{ request('deleted') === 'with' ? 'selected' : '' }}>Include removed</option><option value="only" {{ request('deleted') === 'only' ? 'selected' : '' }}>Removed only</option></select>
    <button class="btn btn-primary btn-sm">Filter</button>
    @if(request()->query())<a href="{{ route('admin.books.index') }}" class="btn btn-outline btn-sm">Clear</a>@endif
</form>

<div class="a-card admin-books-table">
    <table class="a-table"><thead><tr><th>Book</th><th>Publisher</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($books as $book)
        <tr class="{{ $book->trashed() ? 'admin-book-removed' : '' }}">
            <td><div class="a-book-title">
                @if($book->cover_url)<img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="a-book-cover-thumb">@else<div class="a-book-cover-thumb a-book-cover-placeholder">&#128214;</div>@endif
                <div><span>{{ $book->title }}</span><small>{{ $book->author->name ?? 'Unknown author' }} · {{ $book->isbn }}</small></div>
            </div></td>
            <td>{{ $book->publisher->business_name ?? '—' }}</td>
            <td>{{ $book->category->name ?? 'General' }}</td>
            <td>&#8377;{{ number_format($book->price, 0) }}</td>
            <td>{{ $book->inventory->quantity ?? 0 }}</td>
            <td>@if($book->trashed())<span class="badge badge-danger">Removed</span>@else<span class="badge {{ $book->status === 'active' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($book->status) }}</span>@endif</td>
            <td><div class="admin-book-actions">
                @if($book->trashed())
                    <form method="POST" action="{{ route('admin.books.restore', $book->id) }}">@csrf @method('PATCH')<button class="btn btn-primary btn-sm">Restore</button></form>
                    <form method="POST" action="{{ route('admin.books.force-destroy', $book->id) }}" onsubmit="return confirm('Permanently delete this book? This cannot be undone.');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Delete Permanently</button></form>
                @else
                    <a href="{{ route('books.show', $book) }}" class="btn btn-outline btn-sm" target="_blank" rel="noopener">View</a>
                    <a href="{{ route('admin.books.edit', $book) }}" class="btn btn-outline btn-sm">Edit</a>
                    <form method="POST" action="{{ route('admin.books.status', $book) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="{{ $book->status === 'active' ? 'inactive' : 'active' }}"><button class="btn btn-outline btn-sm">{{ $book->status === 'active' ? 'Deactivate' : 'Activate' }}</button></form>
                    <form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Remove this book?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove</button></form>
                @endif
            </div></td>
        </tr>
    @empty
        <tr><td colspan="7">No books match your filters.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $books->links() }}</div>
</div>
@endsection
