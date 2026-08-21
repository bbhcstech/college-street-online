@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'All Books')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
<div class="a-card">
    <table class="a-table"><thead><tr><th>Title</th><th>Publisher</th><th>Category</th><th>Price</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($books as $book)
        <tr>
            <td>{{ $book->title }}</td><td>{{ $book->publisher->business_name ?? '—' }}</td><td>{{ $book->category->name ?? '—' }}</td>
            <td>&#8377;{{ number_format($book->price,0) }}</td>
            <td><span class="badge {{ $book->status === 'active' ? 'badge-success' : 'badge-muted' }}">{{ ucfirst($book->status) }}</span></td>
            <td><form method="POST" action="{{ route('admin.books.destroy', $book) }}" onsubmit="return confirm('Remove this book?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove</button></form></td>
        </tr>
    @empty
        <tr><td colspan="6">No books yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $books->links() }}</div>
</div>
@endsection
