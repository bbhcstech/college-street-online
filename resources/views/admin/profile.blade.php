@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Account';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'My Profile')
@section('nav') @include('admin.partials.nav', ['active' => 'profile']) @endsection

@section('content')
<div class="profile-page-head" style="margin-bottom: 24px;">
    <div>
        <span class="analytics-eyebrow" style="text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.8px; color: #64748b;">Account Settings</span>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 4px 0;">Personal Profile</h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">View and manage your account identity, profile details, and security.</p>
    </div>
    <a href="{{ route('admin.dashboard') }}" class="btn btn-outline" style="padding: 9px 16px; border-radius: 8px; font-weight: 600;">← Back to dashboard</a>
</div>

@if(session('success'))
    <div style="padding: 12px 18px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 1.1rem;">✅</span> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="padding: 12px 18px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
        <ul style="margin: 0; padding-left: 20px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="admin-profile-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
    <!-- Identity Card -->
    <div class="a-card admin-profile-identity" style="padding: 24px; border-radius: 12px; text-align: center; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
        <div class="admin-profile-photo" style="margin: 0 auto 16px auto; width: 100px; height: 100px; border-radius: 50%; overflow: hidden; position: relative; background: linear-gradient(135deg, #3b82f6, #1d4ed8); display: flex; align-items: center; justify-content: center; color: white; font-size: 2.2rem; font-weight: 800; border: 3px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
            @if($user->profile_image_url)
                <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" style="width: 100%; height: 100%; object-fit: cover;" onerror="this.style.display='none'; document.getElementById('admin-avatar-fallback').style.display='flex';">
                <span id="admin-avatar-fallback" style="display:none; width: 100%; height: 100%; align-items: center; justify-content: center;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @else
                <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
            @endif
        </div>
        <h3 style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0;">{{ $user->name }}</h3>
        <p style="color: #64748b; font-size: 0.88rem; margin: 0 0 12px 0;">{{ $user->email }}</p>
        
        <div style="margin-bottom: 16px;">
            @if($user->isSuperAdmin())
                <span style="background: rgba(245, 158, 11, 0.12); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3); font-size: 0.75rem; padding: 4px 12px; border-radius: 16px; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">👑 Super Admin</span>
            @else
                <span style="background: rgba(99, 102, 241, 0.1); color: #4f46e5; border: 1px solid rgba(99, 102, 241, 0.2); font-size: 0.75rem; padding: 4px 12px; border-radius: 16px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">🛡️ Administrator</span>
            @endif
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #f1f5f9; text-align: left; margin-bottom: 16px;">
            <div>
                <small style="color: #64748b; font-size: 0.72rem; text-transform: uppercase; font-weight: 700; display: block;">Status</small>
                <strong style="color: #047857; font-size: 0.88rem;">{{ ucfirst($user->status ?? 'active') }}</strong>
            </div>
            <div>
                <small style="color: #64748b; font-size: 0.72rem; text-transform: uppercase; font-weight: 700; display: block;">Member Since</small>
                <strong style="color: #0f172a; font-size: 0.88rem;">{{ $user->created_at->format('M Y') }}</strong>
            </div>
        </div>

        @if($user->profile_image_path)
            <form method="POST" action="{{ route('admin.profile.image.destroy') }}" onsubmit="return confirm('Remove your profile image?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-danger btn-sm" style="width: 100%; border-radius: 6px; font-weight: 600;">Remove Profile Image</button>
            </form>
        @endif
    </div>

    <!-- Main Content Area -->
    <div style="display: flex; flex-direction: column; gap: 24px;">
        <!-- Profile Info Card -->
        <div class="a-card" style="padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Profile Information</h3>
                    <p style="color: #64748b; font-size: 0.85rem; margin: 2px 0 0 0;">Your personal account details and image.</p>
                </div>
                <button id="toggle-profile-edit-btn" type="button" class="btn btn-outline" onclick="toggleProfileEdit()" style="padding: 6px 14px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    ✏️ Edit Profile
                </button>
            </div>

            <!-- Read Only View -->
            <div id="profile-read-view" style="display: block;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Full Name</span>
                        <div style="font-size: 1rem; font-weight: 700; color: #0f172a; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #f1f5f9;">
                            {{ $user->name }}
                        </div>
                    </div>
                    <div>
                        <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; display: block; margin-bottom: 4px;">Email Address</span>
                        <div style="font-size: 1rem; font-weight: 700; color: #0f172a; background: #f8fafc; padding: 10px 14px; border-radius: 8px; border: 1px solid #f1f5f9;">
                            {{ $user->email }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Edit Form View -->
            <div id="profile-edit-view" style="display: none;">
                <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data">
                    @csrf @method('PUT')
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 16px;">
                        <div class="a-form-group">
                            <label for="admin-name" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Full Name</label>
                            <input id="admin-name" name="name" value="{{ old('name', $user->name) }}" class="a-input" required style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                        <div class="a-form-group">
                            <label for="admin-email" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Email Address</label>
                            <input id="admin-email" type="email" name="email" value="{{ old('email', $user->email) }}" class="a-input" required style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                    <div class="a-form-group" style="margin-bottom: 20px;">
                        <label for="profile-image" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Profile Image</label>
                        <input id="profile-image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="a-input" style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 6px 12px;">
                        <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">JPG, PNG or WebP · Maximum 2 MB</small>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn btn-primary" style="padding: 9px 20px; border-radius: 8px; font-weight: 700;">Save Profile Changes</button>
                        <button type="button" class="btn btn-outline" onclick="toggleProfileEdit()" style="padding: 9px 16px; border-radius: 8px;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security / Password Card -->
        <div class="a-card" style="padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
                <div>
                    <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Security &amp; Password</h3>
                    <p style="color: #64748b; font-size: 0.85rem; margin: 2px 0 0 0;">Update your authentication password.</p>
                </div>
                <button id="toggle-password-edit-btn" type="button" class="btn btn-outline" onclick="togglePasswordEdit()" style="padding: 6px 14px; font-weight: 600; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    🔑 Change Password
                </button>
            </div>

            <!-- Password Read Only View -->
            <div id="password-read-view" style="display: block;">
                <div style="display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 14px 18px; border-radius: 8px; border: 1px solid #f1f5f9;">
                    <span style="font-size: 1.2rem;">🔒</span>
                    <div>
                        <div style="font-weight: 700; color: #0f172a; font-size: 0.92rem;">Password</div>
                        <div style="color: #64748b; font-size: 0.85rem;">•••••••••••• (Last updated securely)</div>
                    </div>
                </div>
            </div>

            <!-- Password Form View -->
            <div id="password-edit-view" style="display: none;">
                <form method="POST" action="{{ route('admin.password.update') }}">
                    @csrf @method('PUT')
                    <div class="a-form-group" style="margin-bottom: 16px;">
                        <label style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Current Password</label>
                        <input type="password" name="current_password" class="a-input" autocomplete="current-password" required style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1;">
                    </div>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 20px;">
                        <div class="a-form-group">
                            <label style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">New Password</label>
                            <input type="password" name="password" class="a-input" minlength="8" autocomplete="new-password" required style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                        <div class="a-form-group">
                            <label style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="a-input" minlength="8" autocomplete="new-password" required style="height: 42px; border-radius: 8px; border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                    <div style="display: flex; gap: 12px; align-items: center;">
                        <button type="submit" class="btn btn-primary" style="padding: 9px 20px; border-radius: 8px; font-weight: 700;">Update Password</button>
                        <button type="button" class="btn btn-outline" onclick="togglePasswordEdit()" style="padding: 9px 16px; border-radius: 8px;">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function toggleProfileEdit() {
    const readView = document.getElementById('profile-read-view');
    const editView = document.getElementById('profile-edit-view');
    const btn = document.getElementById('toggle-profile-edit-btn');
    
    if (editView.style.display === 'none') {
        readView.style.display = 'none';
        editView.style.display = 'block';
        btn.innerHTML = '❌ Cancel';
    } else {
        readView.style.display = 'block';
        editView.style.display = 'none';
        btn.innerHTML = '✏️ Edit Profile';
    }
}

function togglePasswordEdit() {
    const readView = document.getElementById('password-read-view');
    const editView = document.getElementById('password-edit-view');
    const btn = document.getElementById('toggle-password-edit-btn');
    
    if (editView.style.display === 'none') {
        readView.style.display = 'none';
        editView.style.display = 'block';
        btn.innerHTML = '❌ Cancel';
    } else {
        readView.style.display = 'block';
        editView.style.display = 'none';
        btn.innerHTML = '🔑 Change Password';
    }
}

@if($errors->has('name') || $errors->has('email') || $errors->has('profile_image'))
    toggleProfileEdit();
@endif
@if($errors->has('current_password') || $errors->has('password'))
    togglePasswordEdit();
@endif
</script>
@endsection