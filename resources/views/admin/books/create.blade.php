@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Add Book')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
<div class="a-card" style="max-width:780px;">
    <div class="a-card-head"><h3 style="margin:0;">Add Book</h3></div>
    <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="a-form-group"><label>Publisher</label><select name="publisher_id" class="a-select" required><option value="">Select approved publisher</option>@foreach($publishers as $publisher)<option value="{{ $publisher->id }}" {{ (string) old('publisher_id') === (string) $publisher->id ? 'selected' : '' }}>{{ $publisher->business_name }}</option>@endforeach</select></div>
        <div class="a-form-group"><label>Title</label><input type="text" name="title" value="{{ old('title') }}" class="a-input" required></div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>ISBN</label><input type="text" name="isbn" value="{{ old('isbn') }}" class="a-input" required></div>
            <div class="a-form-group"><label>Status</label><select name="status" class="a-select" required><option value="active" {{ old('status', 'active') === 'active' ? 'selected' : '' }}>Active</option><option value="inactive" {{ old('status') === 'inactive' ? 'selected' : '' }}>Inactive</option></select></div>
        </div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Author</label><select name="author_id" class="a-select"><option value="">Select an author</option>@foreach($authors as $author)<option value="{{ $author->id }}" {{ (string) old('author_id') === (string) $author->id ? 'selected' : '' }}>{{ $author->name }}</option>@endforeach</select></div>
            <div class="a-form-group"><label>New Author <small>(if not listed)</small></label><input type="text" name="new_author_name" value="{{ old('new_author_name') }}" maxlength="150" class="a-input" placeholder="Enter author name"></div>
        </div>
        <div class="a-form-group"><label>Category</label><select name="category_id" class="a-select"><option value="">General</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ (string) old('category_id') === (string) $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
        <div class="a-grid a-grid-3">
            <div class="a-form-group"><label>Price (&#8377;)</label><input type="number" step="0.01" min="0" name="price" value="{{ old('price') }}" class="a-input" required></div>
            <div class="a-form-group"><label>MRP (&#8377;)</label><input type="number" step="0.01" min="0" name="mrp" value="{{ old('mrp') }}" class="a-input"></div>
            <div class="a-form-group"><label>Initial Stock</label><input type="number" min="0" name="initial_stock" value="{{ old('initial_stock', 0) }}" class="a-input" required></div>
        </div>
        <div class="a-form-group"><label>Description</label><textarea name="description" class="a-textarea">{{ old('description') }}</textarea></div>
        <div class="a-form-group"><label>Cover Image</label><input type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" class="a-input"></div>
        <div class="flex gap-2"><button class="btn btn-primary">Create Book</button><a href="{{ route('admin.books.index') }}" class="btn btn-outline">Cancel</a></div>
    </form>
</div>
@endsection
