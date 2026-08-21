@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Growth')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Newsletter Subscribers')
@section('nav')@include('admin.partials.nav', ['active' => 'newsletter'])@endsection
@section('content')
<div class="page-header"><div></div><a href="{{ route('admin.newsletter.export') }}" class="btn btn-primary">Export CSV</a></div>
<div class="a-card">
    <table class="a-table"><thead><tr><th>Email</th><th>Subscribed</th></tr></thead><tbody>
    @forelse($subscribers as $s)
        <tr><td>{{ $s->email }}</td><td>{{ $s->subscribed_at->format('d M Y') }}</td></tr>
    @empty
        <tr><td colspan="2">No subscribers yet.</td></tr>
    @endforelse
    </tbody></table>
    <div style="margin-top:16px;">{{ $subscribers->links() }}</div>
</div>
@endsection
