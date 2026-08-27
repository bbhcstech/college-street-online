@extends('layouts.dashboard')
@php $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
$logoutRoute = route('admin.logout'); @endphp
@section('title', 'Add Book')
@section('nav')@include('admin.partials.nav', ['active' => 'books'])@endsection
@section('content')
    <div class="book-form-head">
        <div><span class="analytics-eyebrow">Catalogue entry</span>
            <h2>Create a new book</h2>
            <p>Add publication details, pricing, stock, and a cover image.</p>
        </div><a href="{{ route('admin.books.index') }}" class="btn btn-outline">← Back to books</a>
    </div>
    <form method="POST" action="{{ route('admin.books.store') }}" enctype="multipart/form-data" class="book-editor"
        data-book-form>@csrf
        <div class="book-editor-main">
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>1</span>
                    <div>
                        <h3>Publication details</h3>
                        <p>Core information customers use to identify the book.</p>
                    </div>
                </div>
                <div class="a-form-group"><label for="book-title">Book title</label><input id="book-title" name="title"
                        value="{{ old('title') }}" class="a-input" placeholder="Enter the complete title" maxlength="250"
                        required></div>
                <div class="book-form-grid">
                    <div class="a-form-group"><label>ISBN</label><input name="isbn" value="{{ old('isbn') }}"
                            class="a-input" placeholder="ISBN-10 or ISBN-13" maxlength="20" required>
                        <div class="hint">Must be unique in the catalogue.</div>
                    </div>
                    <div class="a-form-group"><label>Publisher</label><select name="publisher_id" class="a-select" required>
                            <option value="">Select approved publisher</option>@foreach($publishers as $publisher)
                                <option value="{{ $publisher->id }}"
                                    @selected((string) old('publisher_id') === (string) $publisher->id)>
                            {{ $publisher->business_name }}</option>@endforeach
                        </select></div>
                </div>
                <div class="book-form-grid">
                    <div class="a-form-group"><label>Existing author</label><select name="author_id" class="a-select">
                            <option value="">Select an author</option>@foreach($authors as $author)
                                <option value="{{ $author->id }}" @selected((string) old('author_id') === (string) $author->id)>
                            {{ $author->name }}</option>@endforeach
                        </select></div>
                    <div class="a-form-group"><label>Or create new author</label><input name="new_author_name"
                            value="{{ old('new_author_name') }}" class="a-input" maxlength="150"
                            placeholder="Author not listed above">
                        <div class="hint">Choose an existing author or enter a new one.</div>
                    </div>
                </div>
                <div class="a-form-group"><label>Category</label><select name="category_id" class="a-select">
                        <option value="">General / Uncategorized</option>@foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id') === (string) $category->id)>
                        {{ $category->name }}</option>@endforeach
                    </select></div>
            </section>
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>2</span>
                    <div>
                        <h3>Pricing & inventory</h3>
                        <p>Set the selling price and opening stock safely.</p>
                    </div>
                </div>
                <div class="book-form-grid three">
                    <div class="a-form-group"><label>Selling price (₹)</label><input type="number" step="0.01" min="0"
                            name="price" value="{{ old('price') }}" class="a-input" required></div>
                    <div class="a-form-group"><label>MRP (₹)</label><input type="number" step="0.01" min="0" name="mrp"
                            value="{{ old('mrp') }}" class="a-input">
                        <div class="hint">Optional original price.</div>
                    </div>
                    <div class="a-form-group"><label>Initial stock</label><input type="number" min="0" name="initial_stock"
                            value="{{ old('initial_stock', 0) }}" class="a-input" required></div>
                </div>
            </section>
            <section class="a-card book-form-section">
                <div class="book-section-head"><span>3</span>
                    <div>
                        <h3>Description</h3>
                        <p>Explain what the book offers to potential readers.</p>
                    </div>
                </div>
                <div class="a-form-group" style="margin-bottom:0;"><textarea name="description"
                        class="a-textarea book-description"
                        placeholder="Write a clear book description...">{{ old('description') }}</textarea></div>
            </section>
        </div>
        <aside class="book-editor-side">
            <section class="a-card book-cover-panel">
                <h3>Cover image</h3>
                <div class="book-cover-preview" data-cover-preview><span>▣</span>
                    <p>Preview appears here</p>
                </div><label for="cover-image" class="btn btn-outline book-upload-button">Choose cover image</label><input
                    id="cover-image" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp"
                    data-cover-input hidden><small>JPG, PNG or WebP · Maximum 5 MB</small>
            </section>
            <section class="a-card book-publish-panel">
                <h3>Publishing</h3>
                <div class="a-form-group"><label>Catalogue status</label><select name="status" class="a-select" required>
                        <option value="active" @selected(old('status', 'active') === 'active')>Active — visible to customers
                        </option>
                        <option value="inactive" @selected(old('status') === 'inactive')>Inactive — hidden from catalogue
                        </option>
                    </select></div>
                <div class="book-form-note">The book and inventory are created together. If creation fails, no partial
                    record is kept.</div><button class="btn btn-primary book-save-button">Create book</button><a
                    href="{{ route('admin.books.index') }}" class="btn btn-outline book-save-button">Cancel</a>
            </section>
        </aside>
    </form>
    <script>document.querySelector('[data-cover-input]').addEventListener('change', e => { const file = e.target.files[0], preview = document.querySelector('[data-cover-preview]'); if (!file) return; const reader = new FileReader(); reader.onload = event => preview.innerHTML = `<img src="${event.target.result}" alt="Selected cover preview">`; reader.readAsDataURL(file); });</script>
@endsection