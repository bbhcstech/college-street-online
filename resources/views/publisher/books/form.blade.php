@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout');
@endphp
@section('title', $book->exists ? 'Edit Book' : 'Add Book')
@section('nav')
<div class="nav-group"><div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a></div>
<div class="nav-group"><div class="nav-group-title">Catalogue</div>
    <a href="{{ route('publisher.books.index') }}" class="nav-link active"><span class="nav-icon">&#128214;</span><span>My Books</span></a>
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link"><span class="nav-icon">&#128230;</span><span>Inventory</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span class="nav-icon">&#128666;</span><span>Orders</span></a></div>
@endsection
@section('content')
<div class="a-card" style="max-width:640px;">
    <form method="POST" action="{{ $book->exists ? route('publisher.books.update', $book) : route('publisher.books.store') }}" enctype="multipart/form-data">
        @csrf
        @if($book->exists) @method('PUT') @endif
        <div class="a-form-group"><label>Title</label><input type="text" name="title" value="{{ old('title', $book->title) }}" class="a-input" required></div>
        <div class="a-form-group"><label>ISBN</label><input type="text" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="a-input" required></div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group">
                <label>Author</label>
                <select name="author_id" class="a-select">
                    <option value="">Select an author</option>
                    @foreach($authors as $author)
                        <option value="{{ $author->id }}" {{ (string) old('author_id', $book->author_id) === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="a-form-group">
                <label>New Author <small>(if not listed)</small></label>
                <input type="text" name="new_author_name" value="{{ old('new_author_name') }}" maxlength="150" class="a-input" placeholder="Enter author name">
            </div>
        </div>
        <div class="a-form-group">
            <label>Category</label>
            <select name="category_id" class="a-select">
                <option value="">General / Uncategorized</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ (string) old('category_id', $book->category_id) === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Price (&#8377;)</label><input type="number" step="0.01" name="price" value="{{ old('price', $book->price) }}" class="a-input" required></div>
            <div class="a-form-group"><label>MRP (&#8377;, optional)</label><input type="number" step="0.01" name="mrp" value="{{ old('mrp', $book->mrp) }}" class="a-input"></div>
        </div>
        @if(!$book->exists)
        <div class="a-form-group"><label>Initial Stock</label><input type="number" name="initial_stock" value="0" class="a-input"></div>
        @endif
        <div class="a-form-group"><label>Description</label><textarea name="description" class="a-textarea">{{ old('description', $book->description) }}</textarea></div>
        <div class="a-form-group"><label>Cover Image</label><input type="file" name="cover_image" class="a-input"></div>
        <button type="submit" class="btn btn-primary">{{ $book->exists ? 'Update Book' : 'Add Book' }}</button>
    </form>
</div>
@endsection
