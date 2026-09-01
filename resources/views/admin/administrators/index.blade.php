@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $logoutRoute = route('admin.logout');
    $crumb = 'Access Control';
    $active = 'administrators';
@endphp
@section('title', 'Administrators')
@section('nav') @include('admin.partials.nav', ['active' => $active]) @endsection
@section('content')
<div class="page-heading-row">
    <div><span class="a-eyebrow">Secure access</span><h2>Manage administrators</h2><p>Create and review authorized admin accounts.</p></div>
    <a href="{{ route('admin.administrators.create') }}" class="btn btn-primary">+ Add Administrator</a>
</div>

<div class="a-card">
    <form method="GET" class="table-filters">
        <input type="search" name="q" value="{{ request('q') }}" class="a-input" placeholder="Search name or email">
        <button class="btn btn-primary" type="submit">Search</button>
        <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline">Reset</a>
    </form>
    <div class="table-responsive">
        <table class="a-table">
            <thead><tr><th>Administrator</th><th>Email</th><th>Status</th><th>Created</th><th>Actions</th></tr></thead>
            <tbody>
            @forelse($administrators as $administrator)
                <tr>
                    <td><strong>{{ $administrator->name }}</strong>@if($administrator->is(auth()->user())) <small>(You)</small>@endif</td>
                    <td>{{ $administrator->email }}</td>
                    <td><span class="status-badge {{ $administrator->status === 'active' ? 'status-success' : 'status-danger' }}">{{ ucfirst($administrator->status) }}</span></td>
                    <td>{{ $administrator->created_at->format('d M Y') }}</td>
                    <td>
                        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                            <a href="{{ route('admin.administrators.edit', $administrator) }}" class="btn btn-outline btn-sm">Edit</a>
                            @if(! $administrator->is(auth()->user()))
                                <form method="POST" action="{{ route('admin.administrators.status', $administrator) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="{{ $administrator->status === 'active' ? 'suspended' : 'active' }}">
                                    <button type="submit" class="btn btn-sm {{ $administrator->status === 'active' ? 'btn-danger' : 'btn-primary' }}" onclick="return confirm('Change this administrator status?')">{{ $administrator->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5">No administrators found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $administrators->links() }}
</div>
@endsection
