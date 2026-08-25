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
<div class="a-grid a-grid-2">
    <div class="a-card">
        <h3 style="margin-top:0;">Categories</h3>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2" style="margin-bottom:16px;">
            @csrf<input type="text" name="name" class="a-input" placeholder="New category name" required><button class="btn btn-primary btn-sm">Add</button>
        </form>
        <table class="a-table"><thead><tr><th>Name</th><th>Books</th><th>Actions</th></tr></thead><tbody>
        @forelse($categories as $c)
            <tr>
                <td>
                    <form method="POST" action="{{ route('admin.categories.update', $c) }}" class="flex gap-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" class="a-input" value="{{ $c->name }}" required>
                        <button class="btn btn-primary btn-sm">Save</button>
                    </form>
                </td>
                <td>{{ $c->books_count }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Delete this category?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm" @disabled($c->books_count > 0) title="{{ $c->books_count > 0 ? 'Linked books must be reassigned first' : 'Delete category' }}">Delete</button>
                    </form>
                </td>
            </tr>
        @empty<tr><td colspan="3">No categories yet.</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="a-card">
        <h3 style="margin-top:0;">Authors</h3>
        <form method="POST" action="{{ route('admin.authors.store') }}" class="flex gap-2" style="margin-bottom:16px;">
            @csrf<input type="text" name="name" class="a-input" placeholder="New author name" required><button class="btn btn-primary btn-sm">Add</button>
        </form>
        <table class="a-table"><thead><tr><th>Name</th><th>Books</th><th>Actions</th></tr></thead><tbody>
        @forelse($authors as $a)
            <tr>
                <td>
                    <form method="POST" action="{{ route('admin.authors.update', $a) }}" class="flex gap-2">
                        @csrf @method('PUT')
                        <input type="text" name="name" class="a-input" value="{{ $a->name }}" required>
                        <button class="btn btn-primary btn-sm">Save</button>
                    </form>
                </td>
                <td>{{ $a->books_count }}</td>
                <td>
                    <form method="POST" action="{{ route('admin.authors.destroy', $a) }}" onsubmit="return confirm('Delete this author?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm" @disabled($a->books_count > 0) title="{{ $a->books_count > 0 ? 'Linked books must be reassigned first' : 'Delete author' }}">Delete</button>
                    </form>
                </td>
            </tr>
        @empty<tr><td colspan="3">No authors yet.</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@endsection
