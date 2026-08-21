@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Publishers')
@section('nav')@include('admin.partials.nav', ['active' => 'publishers'])@endsection
@section('content')
<div class="page-header"><div></div><a href="{{ route('admin.publishers.create') }}" class="btn btn-primary">+ Add Publisher</a></div>
<div class="a-card">
    <table class="a-table"><thead><tr><th>Business Name</th><th>Contact</th><th>Books</th><th></th></tr></thead><tbody>
    @forelse($publishers as $p)
        <tr>
            <td>{{ $p->business_name }}</td><td>{{ $p->user->email ?? '—' }}</td><td>{{ $p->books_count }}</td>
            <td><form method="POST" action="{{ route('admin.publishers.destroy', $p) }}" onsubmit="return confirm('Remove this publisher?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove</button></form></td>
        </tr>
    @empty
        <tr><td colspan="4">No publishers yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $publishers->links() }}</div>
</div>
@endsection
