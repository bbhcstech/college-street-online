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
    <table class="a-table"><thead><tr><th>Business Name</th><th>Contact</th><th>Books</th><th>Status</th><th></th></tr></thead><tbody>
    @forelse($publishers as $p)
        <tr>
            <td>{{ $p->business_name }}</td><td>{{ $p->user->email ?? '—' }}</td><td>{{ $p->books_count }}</td>
            <td><span class="badge {{ $p->approval_status === 'approved' ? 'badge-success' : ($p->approval_status === 'rejected' ? 'badge-danger' : 'badge-gold') }}">{{ ucfirst($p->approval_status) }}</span></td>
            <td><div class="flex gap-2" style="flex-wrap:wrap;"><a href="{{ route('admin.publishers.edit', $p) }}" class="btn btn-outline btn-sm">Edit</a>@if($p->approval_status !== 'approved')<form method="POST" action="{{ route('admin.publishers.approval', $p) }}">@csrf @method('PATCH')<input type="hidden" name="approval_status" value="approved"><button class="btn btn-primary btn-sm">Approve</button></form>@endif @if($p->approval_status !== 'rejected')<form method="POST" action="{{ route('admin.publishers.approval', $p) }}">@csrf @method('PATCH')<input type="hidden" name="approval_status" value="rejected"><button class="btn btn-danger btn-sm">Reject</button></form>@endif<form method="POST" action="{{ route('admin.publishers.destroy', $p) }}" onsubmit="return confirm('Remove this publisher?');">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove</button></form></div></td>
        </tr>
    @empty
        <tr><td colspan="5">No publishers yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $publishers->links() }}</div>
</div>
@endsection
