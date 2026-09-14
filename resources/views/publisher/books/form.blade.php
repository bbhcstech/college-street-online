@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Catalogue';
    $logoutRoute = route('publisher.logout');
@endphp
@section('title', $book->exists ? 'Edit Book' : 'Add Book')
@section('nav')@include('publisher.partials.nav', ['active' => $book->exists ? 'books' : 'books_create'])@endsection
@section('content')
    <div class="book-form-head">
        <div>
            <span class="analytics-eyebrow">Catalogue Entry</span>
            <h2>{{ $book->exists ? 'Edit Book' : 'Create a New Book' }}</h2>
            <p>{{ $book->exists ? 'Update publication details, pricing, and cover image.' : 'Add publication details, pricing, opening stock, and cover image.' }}</p>
        </div>
        <a href="{{ route('publisher.books.index') }}" class="btn btn-outline">&larr; Back to books</a>
    </div>

    <form method="POST"
        action="{{ $book->exists ? route('publisher.books.update', $book) : route('publisher.books.store') }}"
        enctype="multipart/form-data"
        class="book-editor"
        data-book-form>
        @csrf
        @if($book->exists)
            @method('PUT')
        @endif

        <div class="book-editor-main">
            <!-- Step 1: Publication Details -->
            <section class="a-card book-form-section">
                <div class="book-section-head">
                    <span>1</span>
                    <div>
                        <h3>Publication Details</h3>
                        <p>Core bibliographic information readers and buyers use to identify the book.</p>
                    </div>
                </div>

                <div class="a-form-group">
                    <label for="book-title">Book Title</label>
                    <input id="book-title" name="title" value="{{ old('title', $book->title) }}" class="a-input"
                        placeholder="Enter the complete book title" maxlength="250" required autofocus>
                </div>

                <div class="book-form-grid">
                    <div class="a-form-group">
                        <label for="book-isbn">ISBN</label>
                        <input id="book-isbn" name="isbn" value="{{ old('isbn', $book->isbn) }}" class="a-input"
                            placeholder="ISBN-10 or ISBN-13" maxlength="20" required>
                        <div class="hint" style="color:var(--a-text-muted); font-size:0.75rem; margin-top:4px;">Must be unique across the catalogue.</div>
                    </div>
                    <div class="a-form-group">
                        <label for="book-category">Category</label>
                        <select id="book-category" name="category_id" class="a-select">
                            <option value="">General / Uncategorized</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((string) old('category_id', $book->category_id) === (string) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="book-form-grid">
                    <div class="a-form-group">
                        <label for="book-author">Existing Author</label>
                        <select id="book-author" name="author_id" class="a-select">
                            <option value="">Select an author</option>
                            @foreach($authors as $author)
                                <option value="{{ $author->id }}" @selected((string) old('author_id', $book->author_id) === (string) $author->id)>
                                    {{ $author->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="a-form-group">
                        <label for="new-author">Or Create New Author</label>
                        <input id="new-author" name="new_author_name" value="{{ old('new_author_name') }}" class="a-input"
                            maxlength="150" placeholder="Author name not listed above">
                        <div class="hint" style="color:var(--a-text-muted); font-size:0.75rem; margin-top:4px;">Select from existing authors or type a new name.</div>
                    </div>
                </div>
            </section>

            <!-- Step 2: Pricing & Inventory -->
            <section class="a-card book-form-section">
                <div class="book-section-head">
                    <span>2</span>
                    <div>
                        <h3>Pricing &amp; Stock</h3>
                        <p>Set the selling price and opening inventory quantity.</p>
                    </div>
                </div>

                <div class="book-form-grid {{ $book->exists ? '' : 'three' }}">
                    <div class="a-form-group">
                        <label for="book-price">Selling Price (₹)</label>
                        <input id="book-price" type="number" step="0.01" min="0" name="price"
                            value="{{ old('price', $book->price) }}" class="a-input" placeholder="0.00" required>
                    </div>
                    <div class="a-form-group">
                        <label for="book-mrp">MRP (₹, optional)</label>
                        <input id="book-mrp" type="number" step="0.01" min="0" name="mrp"
                            value="{{ old('mrp', $book->mrp) }}" class="a-input" placeholder="0.00">
                        <div class="hint" style="color:var(--a-text-muted); font-size:0.75rem; margin-top:4px;">Original printed list price.</div>
                    </div>
                    @if(!$book->exists)
                        <div class="a-form-group">
                            <label for="initial-stock">Initial Stock</label>
                            <input id="initial-stock" type="number" min="0" name="initial_stock"
                                value="{{ old('initial_stock', 0) }}" class="a-input" required>
                            <div class="hint" style="color:var(--a-text-muted); font-size:0.75rem; margin-top:4px;">Opening stock balance.</div>
                        </div>
                    @endif
                </div>
            </section>

            <!-- Step 3: Book Description -->
            <section class="a-card book-form-section">
                <div class="book-section-head">
                    <span>3</span>
                    <div>
                        <h3>Description &amp; Synopsis</h3>
                        <p>Provide a detailed summary to engage readers.</p>
                    </div>
                </div>
                <div class="a-form-group" style="margin-bottom:0;">
                    <textarea id="book-desc" name="description" class="a-textarea book-description" rows="5"
                        placeholder="Write a summary, table of contents, or details about the book...">{{ old('description', $book->description) }}</textarea>
                </div>
            </section>
        </div>

        <!-- Right Side Panel: Cover & Publishing -->
        <aside class="book-editor-side">
            <section class="a-card book-cover-panel">
                <h3>Cover Image</h3>
                <div class="book-cover-preview" data-cover-preview>
                    @if($book->cover_url)
                        <img src="{{ $book->cover_url }}" alt="{{ $book->title }}">
                    @else
                        <span>▣</span>
                        <p>Preview appears here</p>
                    @endif
                </div>
                <label for="cover-image" class="btn btn-outline book-upload-button" style="display:block; text-align:center; cursor:pointer;">
                    {{ $book->cover_url ? 'Replace cover image' : 'Choose cover image' }}
                </label>
                <input id="cover-image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp" data-cover-input hidden>
                <small style="display:block; text-align:center; color:var(--a-text-muted); font-size:0.72rem; margin-top:8px;">
                    JPG, PNG or WebP &bull; Maximum 5 MB
                </small>
            </section>

            <section class="a-card book-publish-panel" style="margin-top:16px;">
                <h3>Publishing</h3>
                <div class="a-form-group">
                    <label for="book-status">Catalogue Status</label>
                    <select id="book-status" name="status" class="a-select" required>
                        <option value="active" @selected(old('status', $book->status ?? 'active') === 'active')>
                            Active &mdash; visible to customers
                        </option>
                        <option value="inactive" @selected(old('status', $book->status) === 'inactive')>
                            Inactive &mdash; hidden from catalogue
                        </option>
                    </select>
                </div>

                <div class="book-form-note" style="color:var(--a-text-muted); font-size:0.75rem; margin-bottom:14px;">
                    {{ $book->exists ? 'Changes will reflect immediately in the customer marketplace.' : 'Book and initial inventory records are created safely together.' }}
                </div>

                <button type="submit" class="btn btn-primary book-save-button" style="width:100%; justify-content:center;">
                    {{ $book->exists ? 'Save Changes' : '+ Create Book' }}
                </button>
                <a href="{{ route('publisher.books.index') }}" class="btn btn-outline book-save-button" style="width:100%; justify-content:center; margin-top:8px; display:flex;">
                    Cancel
                </a>
            </section>
        </aside>
    </form>

    <script>
        document.querySelector('[data-cover-input]').addEventListener('change', function (e) {
            const file = e.target.files[0];
            const preview = document.querySelector('[data-cover-preview]');
            if (!file || !preview) return;
            const reader = new FileReader();
            reader.onload = function (event) {
                preview.innerHTML = `<img src="${event.target.result}" alt="Selected cover preview" style="width:100%; height:100%; object-fit:cover; border-radius:8px;">`;
            };
            reader.readAsDataURL(file);
        });
    </script>
@endsection