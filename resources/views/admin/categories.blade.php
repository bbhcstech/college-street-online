@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard'); 
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace'; 
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Categories & Authors')
@section('nav')@include('admin.partials.nav', ['active' => 'categories'])@endsection

@section('content')
<div class="taxonomy-summary">
    <div>
        <span>Categories</span>
        <strong>{{ $activeCategories }}</strong>
        <small>Active records</small>
    </div>
    <div>
        <span>Authors</span>
        <strong>{{ $activeAuthors }}</strong>
        <small>Active records</small>
    </div>
    <div>
        <span>Archived</span>
        <strong>{{ $archivedTotal }}</strong>
        <small>Can be restored</small>
    </div>
</div>

<div class="taxonomy-grid">
    <section class="a-card taxonomy-card">
        <div class="taxonomy-heading">
            <div>
                <h3>Categories</h3>
                <p>Organize books into customer-facing sections.</p>
            </div>
            <div class="taxonomy-heading-actions">
                <span>{{ $categories->total() }} total</span>
                <button type="button" class="btn btn-primary btn-sm" data-taxonomy-modal-open data-title="Add category" data-placeholder="Enter category name" data-action="{{ route('admin.categories.store') }}">+ Add category</button>
            </div>
        </div>
        <div class="taxonomy-table-wrap">
            <table class="a-table taxonomy-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Category</th>
                        <th>Books</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($categories as $category)
                    <tr class="{{ $category->trashed() ? 'taxonomy-archived' : '' }}">
                        <td>{{ ($categories->firstItem() ?? 1) + $loop->index }}</td>
                        <td><strong>{{ $category->name }}</strong></td>
                        <td><strong>{{ $category->books_count }}</strong></td>
                        <td>
                            @if($category->trashed())
                                <span style="background: rgba(148, 163, 184, 0.2); color: var(--a-text-muted); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.75rem;">Archived</span>
                            @else
                                <span style="background: rgba(16, 185, 129, 0.15); color: var(--a-success); padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 0.75rem;">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="taxonomy-actions">
                                @unless($category->trashed())
                                    <button type="button" class="btn btn-outline btn-sm" data-taxonomy-modal-open data-title="Edit category" data-action="{{ route('admin.categories.update', $category) }}" data-method="PUT" data-name="{{ $category->name }}">Edit</button>
                                @endunless 
                                @if($category->trashed())
                                    <form method="POST" action="{{ route('admin.categories.restore', $category) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>
                                    @if($category->books_count === 0)
                                        <form method="POST" action="{{ route('admin.categories.force-destroy', $category->id) }}" onsubmit="return confirm('Permanently delete this category? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Delete permanently</button></form>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Archive this category? Existing books will remain safe.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Archive</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="taxonomy-empty">No categories added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="taxonomy-pagination">
            <span>Showing {{ $categories->firstItem() ?? 0 }}–{{ $categories->lastItem() ?? 0 }} of {{ $categories->total() }}</span>
            <nav class="order-pagination" aria-label="Category pages">
                @if($categories->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $categories->previousPageUrl() }}">Previous</a>@endif
                @foreach(range(1, max(1, $categories->lastPage())) as $page)<a href="{{ $categories->url($page) }}" class="{{ $categories->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                @if($categories->hasMorePages())<a href="{{ $categories->nextPageUrl() }}">Next</a>@else<span class="disabled">Next</span>@endif
            </nav>
        </div>
    </section>

    <section class="a-card taxonomy-card">
        <div class="taxonomy-heading">
            <div>
                <h3>Authors</h3>
                <p>Maintain author names shown in the catalogue.</p>
            </div>
            <div class="taxonomy-heading-actions">
                <span>{{ $authors->total() }} total</span>
                <button type="button" class="btn btn-primary btn-sm" data-taxonomy-modal-open data-title="Add author" data-placeholder="Enter author name" data-action="{{ route('admin.authors.store') }}">+ Add author</button>
            </div>
        </div>
        <div class="taxonomy-table-wrap">
            <table class="a-table taxonomy-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Author</th>
                        <th>Books</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($authors as $author)
                    <tr class="{{ $author->trashed() ? 'taxonomy-archived' : '' }}">
                        <td>{{ ($authors->firstItem() ?? 1) + $loop->index }}</td>
                        <td><strong>{{ $author->name }}</strong></td>
                        <td><strong>{{ $author->books_count }}</strong></td>
                        <td>
                            @if($author->trashed())
                                <span style="background: rgba(148, 163, 184, 0.2); color: var(--a-text-muted); padding: 3px 10px; border-radius: 12px; font-weight: 600; font-size: 0.75rem;">Archived</span>
                            @else
                                <span style="background: rgba(16, 185, 129, 0.15); color: var(--a-success); padding: 3px 10px; border-radius: 12px; font-weight: 700; font-size: 0.75rem;">Active</span>
                            @endif
                        </td>
                        <td>
                            <div class="taxonomy-actions">
                                @unless($author->trashed())
                                    <button type="button" class="btn btn-outline btn-sm" data-taxonomy-modal-open data-title="Edit author" data-action="{{ route('admin.authors.update', $author) }}" data-method="PUT" data-name="{{ $author->name }}">Edit</button>
                                @endunless 
                                @if($author->trashed())
                                    <form method="POST" action="{{ route('admin.authors.restore', $author) }}">@csrf<button class="btn btn-outline btn-sm">Restore</button></form>
                                    @if($author->books_count === 0)
                                        <form method="POST" action="{{ route('admin.authors.force-destroy', $author->id) }}" onsubmit="return confirm('Permanently delete this author? This cannot be undone.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Delete permanently</button></form>
                                    @endif
                                @else
                                    <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" onsubmit="return confirm('Archive this author? Existing books will remain safe.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Archive</button></form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="taxonomy-empty">No authors added yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="taxonomy-pagination">
            <span>Showing {{ $authors->firstItem() ?? 0 }}–{{ $authors->lastItem() ?? 0 }} of {{ $authors->total() }}</span>
            <nav class="order-pagination" aria-label="Author pages">
                @if($authors->onFirstPage())<span class="disabled">Previous</span>@else<a href="{{ $authors->previousPageUrl() }}">Previous</a>@endif
                @foreach(range(1, max(1, $authors->lastPage())) as $page)<a href="{{ $authors->url($page) }}" class="{{ $authors->currentPage() === $page ? 'active' : '' }}">{{ $page }}</a>@endforeach
                @if($authors->hasMorePages())<a href="{{ $authors->nextPageUrl() }}">Next</a>@else<span class="disabled">Next</span>@endif
            </nav>
        </div>
    </section>
</div>

<dialog class="taxonomy-modal" data-taxonomy-modal>
    <form method="POST" data-taxonomy-form>
        @csrf
        <input type="hidden" name="_method" data-taxonomy-method disabled>
        <div class="taxonomy-modal-head">
            <div>
                <h3 data-taxonomy-title>Add record</h3>
                <p>Enter a name to add a new record.</p>
            </div>
            <button type="button" data-taxonomy-close aria-label="Close">&times;</button>
        </div>
        <div class="taxonomy-modal-body">
            <label for="taxonomy-name">Name</label>
            <input id="taxonomy-name" name="name" class="a-input" maxlength="150" required data-taxonomy-name>
        </div>
        <div class="taxonomy-modal-actions">
            <button type="button" class="btn btn-outline" data-taxonomy-close>Cancel</button>
            <button class="btn btn-primary" data-taxonomy-submit>Add</button>
        </div>
    </form>
</dialog>

<script>
(() => { 
    const modal = document.querySelector('[data-taxonomy-modal]'), 
          form = modal?.querySelector('[data-taxonomy-form]'), 
          title = modal?.querySelector('[data-taxonomy-title]'), 
          input = modal?.querySelector('[data-taxonomy-name]'), 
          method = modal?.querySelector('[data-taxonomy-method]'), 
          submit = modal?.querySelector('[data-taxonomy-submit]'); 

    document.querySelectorAll('[data-taxonomy-modal-open]').forEach(button => button.addEventListener('click', () => { 
        const editing = button.dataset.method === 'PUT'; 
        form.action = button.dataset.action; 
        title.textContent = button.dataset.title; 
        input.placeholder = button.dataset.placeholder || ''; 
        input.value = button.dataset.name || ''; 
        method.disabled = !editing; 
        method.value = editing ? 'PUT' : ''; 
        submit.textContent = editing ? 'Save changes' : 'Add'; 
        modal.showModal(); 
        input.focus(); 
    })); 

    modal?.querySelectorAll('[data-taxonomy-close]').forEach(button => button.addEventListener('click', () => modal.close())); 
    modal?.addEventListener('click', event => { if (event.target === modal) modal.close(); }); 
})();
</script>
@endsection
