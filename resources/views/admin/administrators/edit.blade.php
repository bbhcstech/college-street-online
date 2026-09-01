@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $logoutRoute = route('admin.logout');
    $crumb = 'Access Control';
    $active = 'administrators';
@endphp
@section('title', 'Edit Administrator')
@section('nav') @include('admin.partials.nav', ['active' => $active]) @endsection
@section('content')
<div class="page-heading-row">
    <div><span class="a-eyebrow">Secure access</span><h2>Edit administrator</h2><p>Update account information or assign a new password.</p></div>
    <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline">Back</a>
</div>

<div class="a-card" style="max-width:720px;">
    <form method="POST" action="{{ route('admin.administrators.update', $administrator) }}">
        @csrf @method('PUT')
        <div class="a-form-group"><label for="admin-name">Full name</label><input id="admin-name" class="a-input" name="name" value="{{ old('name', $administrator->name) }}" required></div>
        <div class="a-form-group"><label for="admin-email">Email address</label><input id="admin-email" class="a-input" type="email" name="email" value="{{ old('email', $administrator->email) }}" required></div>
        <div class="a-form-group"><label for="admin-password">New password</label><input id="admin-password" class="a-input" type="password" name="password" minlength="8"><small>Leave blank to keep the current password. Minimum 8 characters.</small></div>
        <div class="a-form-group"><label for="admin-password-confirmation">Confirm new password</label><input id="admin-password-confirmation" class="a-input" type="password" name="password_confirmation"></div>
        <button class="btn btn-primary" type="submit">Save Changes</button>
    </form>
</div>
@endsection
