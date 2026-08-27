@extends('layouts.dashboard')
@php $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
$logoutRoute = route('admin.logout'); @endphp
@section('title', 'Edit Book')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
    <div class="book-form-head">
        <div><span class="analytics-eyebrow">Catalogue entry #{{ $book->id }}</span>
            <h2>Edit book details</h2>
            <p>Update catalogue information without changing historical order records.</p>
        </div>
        <div class="flex gap-2"><a href="{{ route('books.show', $book) }}" target="_blank" class="btn btn-outline">View book
                ↗</a><a href="{{ route('admin.books.index') }}" class="btn btn-outline">← Back</a></div>
    </div>
    <form method="POST" action="{{ route('admin.books.update', $book) }}" class="book-editor">@csrf @method('PUT')
        <div class="book-editor-main">
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>1</span>
                    <div>
                        <h3>Publication details</h3>
                        <p>Update the customer-facing catalogue information.</p>
                    </div>
                </div>
                <div class="a-form-group"><label>Book title</label><input name="title"
                        value="{{ old('title', $book->title) }}" class="a-input" maxlength="250" required></div>
                <div class="book-form-grid">
                    <div class="a-form-group"><label>ISBN</label><input name="isbn" value="{{ old('isbn', $book->isbn) }}"
                            class="a-input" maxlength="20" required></div>
                    <div class="a-form-group"><label>Publisher</label>
                        <div class="book-readonly-field">{{ $book->publisher->business_name ?? '—' }}<small>Publisher
                                ownership cannot be changed here.</small></div>
                    </div>
                </div>
                <div class="book-form-grid">
                    <div class="a-form-group"><label>Author</label><select name="author_id" class="a-select"
                            required>@foreach($authors as $author)
                                <option value="{{ $author->id }}"
                                    @selected((string) old('author_id', $book->author_id) === (string) $author->id)>
                            {{ $author->name }}</option>@endforeach
                        </select></div>
                    <div class="a-form-group"><label>Category</label><select name="category_id" class="a-select">
                            <option value="">General / Uncategorized</option>@foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected((string) old('category_id', $book->category_id) === (string) $category->id)>
                            {{ $category->name }}</option>@endforeach
                        </select></div>
                </div>
            </section>
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>2</span>
                    <div>
                        <h3>Pricing</h3>
                        <p>Update customer pricing while preserving old order snapshots.</p>
                    </div>
                </div>
                <div class="book-form-grid">
                    <div class="a-form-group"><label>Selling price (₹)</label><input type="number" step="0.01" min="0"
                            name="price" value="{{ old('price', $book->price) }}" class="a-input" required></div>
                    <div class="a-form-group"><label>MRP (₹)</label><input type="number" step="0.01" min="0" name="mrp"
                            value="{{ old('mrp', $book->mrp) }}" class="a-input"></div>
                </div>
            </section>
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>3</span>
                    <div>
                        <h3>Description</h3>
                        <p>Keep the book summary accurate and useful.</p>
                    </div>
                </div><textarea name="description" class="a-textarea book-description"
                    placeholder="Write a clear book description...">{{ old('description', $book->description) }}</textarea>
            </section>
        </div>
        <aside class="book-editor-side">
            <section class="a-card book-cover-panel">
                <h3>Current cover</h3>
                <div class="book-cover-preview">@if($book->cover_url)<img src="{{ $book->cover_url }}"
                alt="{{ $book->title }} cover">@else<span>▣</span>
                        <p>No cover image</p>@endif
                </div><small>Cover replacement will be handled when uploads are migrated to public storage.</small>
            </section>
            <section class="a-card book-publish-panel">
                <h3>Catalogue controls</h3>
                <div class="a-form-group"><label>Status</label><select name="status" class="a-select" required>
                        <option value="active" @selected(old('status', $book->status) === 'active')>Active — visible</option>
                        <option value="inactive" @selected(old('status', $book->status) === 'inactive')>Inactive — hidden
                        </option>
                    </select></div>
                <div class="book-stock-summary"><span>Current
                        stock</span><strong>{{ $book->inventory->quantity ?? 0 }}</strong><small>Manage stock from the
                        publisher inventory workflow.</small></div><button class="btn btn-primary book-save-button">Save
                    changes</button><a href="{{ route('admin.books.index') }}"
                    class="btn btn-outline book-save-button">Cancel</a>
            </section>
        </aside>
    </form>
@endsection