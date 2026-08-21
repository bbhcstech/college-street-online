@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Categories & Authors')
@section('nav')@include('admin.partials.nav', ['active' => 'categories'])@endsection
@section('content')
<div class="a-grid a-grid-2">
    <div class="a-card">
        <h3 style="margin-top:0;">Categories</h3>
        <form method="POST" action="{{ route('admin.categories.store') }}" class="flex gap-2" style="margin-bottom:16px;">
            @csrf<input type="text" name="name" class="a-input" placeholder="New category name" required><button class="btn btn-primary btn-sm">Add</button>
        </form>
        <table class="a-table"><thead><tr><th>Name</th><th>Books</th></tr></thead><tbody>
        @forelse($categories as $c)<tr><td>{{ $c->name }}</td><td>{{ $c->books_count }}</td></tr>@empty<tr><td colspan="2">No categories yet.</td></tr>@endforelse
        </tbody></table>
    </div>
    <div class="a-card">
        <h3 style="margin-top:0;">Authors</h3>
        <form method="POST" action="{{ route('admin.authors.store') }}" class="flex gap-2" style="margin-bottom:16px;">
            @csrf<input type="text" name="name" class="a-input" placeholder="New author name" required><button class="btn btn-primary btn-sm">Add</button>
        </form>
        <table class="a-table"><thead><tr><th>Name</th><th>Books</th></tr></thead><tbody>
        @forelse($authors as $a)<tr><td>{{ $a->name }}</td><td>{{ $a->books_count }}</td></tr>@empty<tr><td colspan="2">No authors yet.</td></tr>@endforelse
        </tbody></table>
    </div>
</div>
@endsection
