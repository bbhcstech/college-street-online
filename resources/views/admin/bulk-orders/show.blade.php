@extends('layouts.dashboard')
@php $homeRoute = route('admin.dashboard'); $brandLabel = 'Admin Console'; $crumb = 'Operations'; $logoutRoute = route('admin.logout'); @endphp
@section('title', 'Bulk Request #BOR'.$bulkOrder->id)
@section('nav')@include('admin.partials.nav', ['active' => 'bulk-orders'])@endsection
@section('content')
<div class="a-grid a-grid-2" style="align-items:start;">
    <div>
        <div class="a-card"><h3>Request details</h3><div class="a-grid a-grid-2"><div><small style="color:var(--a-text-muted);">Institution</small><strong style="display:block;margin-top:4px;">{{ $bulkOrder->institution_name }}</strong></div><div><small style="color:var(--a-text-muted);">Contact</small><strong style="display:block;margin-top:4px;">{{ $bulkOrder->contact_name }}</strong></div><div><small style="color:var(--a-text-muted);">Email</small><div>{{ $bulkOrder->email }}</div></div><div><small style="color:var(--a-text-muted);">Phone</small><div>{{ $bulkOrder->phone }}</div></div></div></div>
        <div class="a-card"><h3>Books and quantities</h3><div style="white-space:pre-wrap;line-height:1.7;">{{ $bulkOrder->requirements }}</div>@if($bulkOrder->notes)<hr style="border:0;border-top:1px solid var(--a-border);margin:20px 0;"><h3>Additional notes</h3><div style="white-space:pre-wrap;">{{ $bulkOrder->notes }}</div>@endif</div>
    </div>
    <div class="a-card"><h3>Manage request</h3><form method="POST" action="{{ route('admin.bulk-orders.update', $bulkOrder) }}">@csrf @method('PUT')
        <div class="a-form-group"><label>Status</label><select name="status" class="a-select">@foreach(\App\Models\BulkOrderRequest::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $bulkOrder->status) === $status)>{{ ucfirst($status) }}</option>@endforeach</select></div>
        <div class="a-form-group"><label>Quoted amount (₹)</label><input type="number" name="quoted_amount" min="0" step="0.01" class="a-input" value="{{ old('quoted_amount', $bulkOrder->quoted_amount) }}"></div>
        <div class="a-form-group"><label>Internal notes</label><textarea name="admin_notes" class="a-textarea" placeholder="Stock checks, contact history, pricing notes...">{{ old('admin_notes', $bulkOrder->admin_notes) }}</textarea><div class="hint">Visible only to administrators.</div></div>
        <button class="btn btn-primary">Save changes</button><a href="{{ route('admin.bulk-orders.index') }}" class="btn btn-outline">Back</a>
    </form></div>
</div>
@endsection
