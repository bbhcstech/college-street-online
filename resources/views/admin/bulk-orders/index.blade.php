@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard'); $brandLabel = 'Admin Console'; $crumb = 'Operations'; $logoutRoute = route('admin.logout');
    $badges = ['new'=>'badge-gold','contacted'=>'badge-info','quoted'=>'badge-purple','accepted'=>'badge-blue','completed'=>'badge-success','rejected'=>'badge-danger'];
@endphp
@section('title', 'Bulk Order Requests')
@section('nav')@include('admin.partials.nav', ['active' => 'bulk-orders'])@endsection
@section('content')
<div class="page-header" style="margin-bottom:20px;"><p style="margin:0;color:var(--a-text-muted);">Review institutional and high-volume quote requests.</p><form method="GET"><select name="status" class="a-select" onchange="this.form.submit()"><option value="">All statuses</option>@foreach(\App\Models\BulkOrderRequest::STATUSES as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select></form></div>
<div class="a-card"><div style="overflow-x:auto;"><table class="a-table"><thead><tr><th>Reference</th><th>Institution</th><th>Contact</th><th>Submitted</th><th>Status</th><th></th></tr></thead><tbody>
@forelse($requests as $item)
<tr><td><strong>#BOR{{ $item->id }}</strong></td><td>{{ $item->institution_name }}</td><td>{{ $item->contact_name }}<small style="display:block;color:var(--a-text-muted);">{{ $item->email }}</small></td><td>{{ $item->created_at->format('d M Y') }}</td><td><span class="badge {{ $badges[$item->status] ?? 'badge-muted' }}">{{ ucfirst($item->status) }}</span></td><td><a href="{{ route('admin.bulk-orders.show', $item) }}" class="btn btn-outline btn-sm">View</a></td></tr>
@empty<tr><td colspan="6" style="text-align:center;padding:30px;color:var(--a-text-muted);">No bulk order requests found.</td></tr>@endforelse
</tbody></table></div><div style="margin-top:18px;">{{ $requests->links() }}</div></div>
@endsection
