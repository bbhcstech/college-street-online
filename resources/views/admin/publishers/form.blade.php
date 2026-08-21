@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@php($editing = isset($publisher))
@section('title', $editing ? 'Edit Publisher' : 'Add Publisher')
@section('nav')@include('admin.partials.nav', ['active' => 'publishers'])@endsection
@section('content')
<div class="a-card" style="max-width:560px;">
    <form method="POST" action="{{ $editing ? route('admin.publishers.update', $publisher) : route('admin.publishers.store') }}">
        @csrf
        @if($editing) @method('PUT') @endif
        <div class="a-form-group"><label>Contact Name</label><input type="text" name="name" value="{{ old('name', $publisher->user->name ?? '') }}" class="a-input" required></div>
        <div class="a-form-group"><label>Email</label><input type="email" name="email" value="{{ old('email', $publisher->user->email ?? '') }}" class="a-input" required></div>
        <div class="a-form-group"><label>{{ $editing ? 'New Password (leave blank to keep current)' : 'Temporary Password' }}</label><input type="password" name="password" class="a-input" {{ $editing ? '' : 'required' }} minlength="8"></div>
        <div class="a-form-group"><label>Business Name</label><input type="text" name="business_name" value="{{ old('business_name', $publisher->business_name ?? '') }}" class="a-input" required></div>
        <div class="a-form-group"><label>Contact Details</label><textarea name="contact_details" class="a-textarea">{{ old('contact_details', $publisher->contact_details ?? '') }}</textarea></div>
        <button type="submit" class="btn btn-primary">{{ $editing ? 'Update Publisher' : 'Create Publisher' }}</button>
    </form>
</div>
@endsection
