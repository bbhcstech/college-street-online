@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Add Publisher')
@section('nav')@include('admin.partials.nav', ['active' => 'publishers'])@endsection
@section('content')
<div class="a-card" style="max-width:560px;">
    <form method="POST" action="{{ route('admin.publishers.store') }}">
        @csrf
        <div class="a-form-group"><label>Contact Name</label><input type="text" name="name" class="a-input" required></div>
        <div class="a-form-group"><label>Email</label><input type="email" name="email" class="a-input" required></div>
        <div class="a-form-group"><label>Temporary Password</label><input type="password" name="password" class="a-input" required minlength="8"></div>
        <div class="a-form-group"><label>Business Name</label><input type="text" name="business_name" class="a-input" required></div>
        <div class="a-form-group"><label>Contact Details</label><textarea name="contact_details" class="a-textarea"></textarea></div>
        <button type="submit" class="btn btn-primary">Create Publisher</button>
    </form>
</div>
@endsection
