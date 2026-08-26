@extends('layouts.dashboard')
@php $homeRoute=route('admin.dashboard'); $brandLabel='Admin Console'; $crumb='Account'; $logoutRoute=route('admin.logout'); @endphp
@section('title', 'My Profile')
@section('nav')@include('admin.partials.nav', ['active' => 'profile'])@endsection
@section('content')
<div class="profile-page-head"><div><span class="analytics-eyebrow">Account settings</span><h2>Personal profile</h2><p>Manage your admin identity, profile image, email, and password.</p></div><a href="{{ route('admin.dashboard') }}" class="btn btn-outline">← Back to dashboard</a></div>
<div class="admin-profile-grid">
    <div class="a-card admin-profile-identity">
        <div class="admin-profile-photo">@if($user->profile_image_url)<img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}">@else<span>{{ strtoupper(substr($user->name,0,1)) }}</span>@endif</div>
        <h3>{{ $user->name }}</h3><p>{{ $user->email }}</p><span class="badge badge-info">Administrator</span>
        <div class="admin-profile-meta"><div><small>Account status</small><strong>{{ ucfirst($user->status ?? 'active') }}</strong></div><div><small>Member since</small><strong>{{ $user->created_at->format('M Y') }}</strong></div></div>
        @if($user->profile_image_path)<form method="POST" action="{{ route('admin.profile.image.destroy') }}" onsubmit="return confirm('Remove your profile image?')">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove profile image</button></form>@endif
    </div>
    <div>
        <div class="a-card"><div class="a-card-head dashboard-card-title"><div><h3>Profile information</h3><p>Update your personal account details.</p></div></div>
            <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">@csrf @method('PUT')
                <div class="admin-profile-form-grid"><div class="a-form-group"><label for="admin-name">Full name</label><input id="admin-name" name="name" value="{{ old('name',$user->name) }}" class="a-input" required></div><div class="a-form-group"><label for="admin-email">Email address</label><input id="admin-email" type="email" name="email" value="{{ old('email',$user->email) }}" class="a-input" required></div></div>
                <div class="a-form-group"><label for="profile-image">Profile image</label><div class="admin-file-field"><input id="profile-image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="a-input"><span>JPG, PNG or WebP · Maximum 2 MB</span></div></div>
                <button class="btn btn-primary">Save profile changes</button>
            </form>
        </div>
        <div class="a-card"><div class="a-card-head dashboard-card-title"><div><h3>Security</h3><p>Use a strong password you do not use elsewhere.</p></div></div>
            <form method="POST" action="{{ route('admin.password.update') }}">@csrf @method('PUT')
                <div class="a-form-group"><label>Current password</label><input type="password" name="current_password" class="a-input" autocomplete="current-password" required></div><div class="admin-profile-form-grid"><div class="a-form-group"><label>New password</label><input type="password" name="password" class="a-input" minlength="8" autocomplete="new-password" required></div><div class="a-form-group"><label>Confirm new password</label><input type="password" name="password_confirmation" class="a-input" minlength="8" autocomplete="new-password" required></div></div>
                <button class="btn btn-primary">Change password</button>
            </form>
        </div>
    </div>
</div>
@endsection
