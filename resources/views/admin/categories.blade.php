@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard'); $brandLabel = 'Admin Console';
    $crumb = 'Marketplace'; $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Categories & Authors')
@section('nav')@include('admin.partials.nav', ['active' => 'categories'])@endsection
@section('content')
<div class="taxonomy-summary">
    <div><span>Categories</span><strong>{{ $categories->whereNull('deleted_at')->count() }}</strong><small>Active records</small></div>
    <div><span>Authors</span><strong>{{ $authors->whereNull('deleted_at')->count() }}</strong><small>Active records</small></div>
    <div><span>Archived</span><strong>{{ $categories->whereNotNull('deleted_at')->count() + $authors->whereNotNull('deleted_at')->count() }}</strong><small>Can be restored</small></div>
</div>

<div class="taxonomy-grid">
    <section class="a-card taxonomy-card">
        <div class="taxonomy-heading"><div><h3>Categories</h3><p>Organize books into customer-facing sections.</p></div><span>{{ $categories->count() }} total</span></div>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="taxonomy-add-form">@csrf<input name="name" class="a-input" maxlength="150" placeholder="Enter category name" required><button class="btn btn-primary">+ Add category</button></form>
        <div class="taxonomy-table-wrap"><table class="a-table taxonomy-table"><thead><tr><th>Category</th><th>Books</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($categories as $category)
            <tr class="{{ $category->trashed() ? 'taxonomy-archived' : '' }}">
                <td><form method="POST" action="{{ route('admin.categories.update', $category) }}" class="taxonomy-edit-form">@csrf @method('PUT')<input name="name" value="{{ $category->name }}" class="a-input" maxlength="150" required><button class="btn btn-outline btn-sm">Save</button></form></td>
                <td><strong>{{ $category->books_count }}</strong></td><td><span class="status-pill {{ $category->trashed() ? 'status-muted' : 'status-success' }}">{{ $category->trashed() ? 'Archived' : 'Active' }}</span></td>
                <td><div class="taxonomy-actions">@if($category->trashed())<form method="POST" action="{{ route('admin.categories.restore', $category) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>@if($category->books_count === 0)<form method="POST" action="{{ route('admin.categories.force-destroy', $category->id) }}" onsubmit="return confirm('Permanently delete this category? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Delete permanently</button></form>@endif @else<form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Archive this category? Existing books will remain safe.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Archive</button></form>@endif</div></td>
            </tr>
        @empty<tr><td colspan="4" class="taxonomy-empty">No categories added yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>

    <section class="a-card taxonomy-card">
        <div class="taxonomy-heading"><div><h3>Authors</h3><p>Maintain author names shown in the catalogue.</p></div><span>{{ $authors->count() }} total</span></div>
        <form method="POST" action="{{ route('admin.authors.store') }}" class="taxonomy-add-form">@csrf<input name="name" class="a-input" maxlength="150" placeholder="Enter author name" required><button class="btn btn-primary">+ Add author</button></form>
        <div class="taxonomy-table-wrap"><table class="a-table taxonomy-table"><thead><tr><th>Author</th><th>Books</th><th>Status</th><th>Actions</th></tr></thead><tbody>
        @forelse($authors as $author)
            <tr class="{{ $author->trashed() ? 'taxonomy-archived' : '' }}">
                <td><form method="POST" action="{{ route('admin.authors.update', $author) }}" class="taxonomy-edit-form">@csrf @method('PUT')<input name="name" value="{{ $author->name }}" class="a-input" maxlength="150" required><button class="btn btn-outline btn-sm">Save</button></form></td>
                <td><strong>{{ $author->books_count }}</strong></td><td><span class="status-pill {{ $author->trashed() ? 'status-muted' : 'status-success' }}">{{ $author->trashed() ? 'Archived' : 'Active' }}</span></td>
                <td><div class="taxonomy-actions">@if($author->trashed())<form method="POST" action="{{ route('admin.authors.restore', $author) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>@if($author->books_count === 0)<form method="POST" action="{{ route('admin.authors.force-destroy', $author->id) }}" onsubmit="return confirm('Permanently delete this author? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Delete permanently</button></form>@endif @else<form method="POST" action="{{ route('admin.authors.destroy', $author) }}" onsubmit="return confirm('Archive this author? Existing books will remain safe.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Archive</button></form>@endif</div></td>
            </tr>
        @empty<tr><td colspan="4" class="taxonomy-empty">No authors added yet.</td></tr>@endforelse
        </tbody></table></div>
    </section>
</div>
@endsection
