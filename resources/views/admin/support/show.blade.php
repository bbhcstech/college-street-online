@extends('layouts.dashboard')
@php $homeRoute=route('admin.dashboard');$brandLabel='Admin Console';$crumb='Operations';$logoutRoute=route('admin.logout'); @endphp
@section('title','Support Ticket')
@section('nav')@include('admin.partials.nav',['active'=>'support'])@endsection
@section('content')
<div class="profile-page-head"><div><span class="analytics-eyebrow">Customer support</span><h2>#SUP{{ str_pad($ticket->id,4,'0',STR_PAD_LEFT) }}</h2><p>{{ $ticket->subject }}</p></div><a href="{{ route('admin.support.index') }}" class="btn btn-outline">Back</a></div>
<div class="support-admin-grid"><div class="a-card"><h3>Customer message</h3><p><strong>{{ $ticket->user->name }}</strong> · {{ $ticket->user->email }}</p><div class="support-message-box">{{ $ticket->message }}</div></div><div class="a-card"><h3>Reply and status</h3><form method="POST" action="{{ route('admin.support.update',$ticket) }}">@csrf @method('PUT')<div class="a-form-group"><label>Status</label><select name="status" class="a-input" required>@foreach(['open','in_progress','resolved','closed'] as $status)<option value="{{ $status }}" @selected($ticket->status===$status)>{{ ucfirst(str_replace('_',' ',$status)) }}</option>@endforeach</select></div><div class="a-form-group"><label>Reply</label><textarea name="admin_reply" rows="8" maxlength="5000" class="a-textarea">{{ old('admin_reply',$ticket->admin_reply) }}</textarea></div><button class="btn btn-primary">Save response</button></form></div></div>
@endsection
