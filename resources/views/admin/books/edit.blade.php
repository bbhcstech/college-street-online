@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Edit Book')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
<div class="a-card" style="max-width:760px;">
    <div class="a-card-head"><div><h3 style="margin:0;">Edit Book</h3><small style="color:var(--a-text-muted);">Publisher: {{ $book->publisher->business_name ?? '—' }}</small></div>@if($book->cover_url)<img src="{{ $book->cover_url }}" alt="{{ $book->title }} cover" class="a-book-cover-thumb">@endif</div>
    <form method="POST" action="{{ route('admin.books.update', $book) }}">
        @csrf @method('PUT')
        <div class="a-form-group"><label>Title</label><input type="text" name="title" value="{{ old('title', $book->title) }}" class="a-input" required></div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>ISBN</label><input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="a-input" required></div>
            <div class="a-form-group"><label>Status</label><select name="status" class="a-select" required><option value="active" {{ old('status', $book->status) === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ old('status', $book->status) === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
        </div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Author</label><select name="author_id" class="a-select" required>@foreach($authors as $author)<option value="{{ $author->id }}" {{ (string) old('author_id', $book->author_id) === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>@endforeach</select></div>
            <div class="a-form-group"><label>Category</label><select name="category_id" class="a-select"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string) old('category_id', $book->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
        </div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Price (&#8377;)</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price', $book->price) }}" class="a-input" required></div>
            <div class="a-form-group"><label>MRP (&#8377;)</label><input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp', $book->mrp) }}" class="a-input"></div>
        </div>
        <div class="a-form-group"><label>Description</label><textarea name="description" class="a-textarea">{{ old('description', $book->description) }}</textarea></div>
        <div class="flex gap-2"><button class="btn btn-primary">Save Changes</button><a href="{{ route('admin.books.index') }}" class="btn btn-outline">Cancel</a></div>
    </form>
</div>
@endsection
