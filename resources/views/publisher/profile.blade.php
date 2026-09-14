@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Account';
    $logoutRoute = route('publisher.logout');
    $profileUpdateRoute = route('publisher.profile.update');
    $passwordUpdateRoute = route('publisher.password.update');
    $hasProfileErrors = $errors->has('name') || $errors->has('email') || $errors->has('business_name') || $errors->has('contact_details') || $errors->has('profile_image');
@endphp
@section('title', 'My Profile')
@section('nav')@include('publisher.partials.nav', ['active' => 'profile'])@endsection
@section('content')
    <style>
        .profile-input-readonly {
            background-color: var(--a-surface-alt, #f4f6f9) !important;
            border-color: var(--a-border, #e2e8f0) !important;
            color: var(--a-text, #1e293b) !important;
            cursor: default !important;
            user-select: text;
        }
        .profile-input-readonly:focus {
            outline: none !important;
            border-color: var(--a-border, #e2e8f0) !important;
            box-shadow: none !important;
        }
        .profile-edit-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.72rem;
            font-weight: 600;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(38, 132, 190, 0.12);
            color: var(--a-primary);
        }
    </style>

    <div class="profile-page-head">
        <div>
            <span class="analytics-eyebrow">Publisher Account</span>
            <h2>Profile & Settings</h2>
            <p>Manage your publisher identity, business contact details, and security.</p>
        </div>
        <a href="{{ route('publisher.dashboard') }}" class="btn btn-outline">&larr; Back to dashboard</a>
    </div>

    <div class="admin-profile-grid">
        <!-- Identity Summary Card -->
        <div class="a-card admin-profile-identity">
            <div class="admin-profile-photo">
                @if($user->profile_image_url)
                    <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}" onerror="this.style.display='none'; document.getElementById('pub-avatar-fallback').style.display='flex';">
                    <span id="pub-avatar-fallback" style="display:none;">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @else
                    <span>{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                @endif
            </div>

            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
            <span class="badge badge-info" style="margin-bottom: 8px;">{{ $user->publisher?->business_name ?? 'Publisher' }}</span>

            <div class="admin-profile-meta">
                <div>
                    <small>Approval status</small>
                    <strong style="text-transform: capitalize; color: {{ ($user->publisher?->approval_status ?? '') === 'approved' ? 'var(--a-success, #1F9D6C)' : 'var(--a-gold, #EDA13A)' }};">
                        {{ $user->publisher?->approval_status ?? 'Pending' }}
                    </strong>
                </div>
                <div>
                    <small>Member since</small>
                    <strong>{{ $user->created_at->format('M Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- Form Cards Column -->
        <div>
            <!-- Profile Information Card -->
            <div class="a-card">
                <div class="a-card-head dashboard-card-title" style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 18px;">
                    <div>
                        <h3 style="margin:0;">Profile Information</h3>
                        <p style="margin:4px 0 0; color:var(--a-text-muted); font-size:0.8rem;">View and manage your publisher details.</p>
                    </div>
                    <button type="button" id="btn-toggle-edit" class="btn btn-outline btn-sm" style="display:flex; align-items:center; gap:6px;">
                        <span id="toggle-icon">✏️</span>
                        <span id="toggle-label">Edit Profile</span>
                    </button>
                </div>

                <form method="POST" action="{{ $profileUpdateRoute }}" enctype="multipart/form-data" id="profile-form">
                    @csrf
                    @method('PUT')

                    <div class="admin-profile-form-grid">
                        <div class="a-form-group">
                            <label for="input-name">Contact Person Name</label>
                            <input id="input-name" name="name" value="{{ old('name', $user->name) }}" class="a-input profile-field profile-input-readonly" readonly required>
                        </div>
                        <div class="a-form-group">
                            <label for="input-email">Email Address</label>
                            <input id="input-email" type="email" name="email" value="{{ old('email', $user->email) }}" class="a-input profile-field profile-input-readonly" readonly required>
                        </div>
                    </div>

                    @if($user->isPublisher())
                        <div class="a-form-group">
                            <label for="input-business-name">Business / Press Name</label>
                            <input id="input-business-name" name="business_name" value="{{ old('business_name', $user->publisher?->business_name) }}" class="a-input profile-field profile-input-readonly" readonly required>
                        </div>

                        <div class="a-form-group">
                            <label for="input-contact-details">Contact Details & Address</label>
                            <textarea id="input-contact-details" name="contact_details" rows="3" class="a-textarea profile-field profile-input-readonly" readonly>{{ old('contact_details', $user->publisher?->contact_details) }}</textarea>
                        </div>
                    @endif

                    <div class="a-form-group" id="profile-image-group" style="display: none;">
                        <label for="profile-image">Profile / Logo Image</label>
                        <div class="admin-file-field">
                            <input id="profile-image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="a-input">
                            <span style="display:block; color:var(--a-text-muted); font-size:0.75rem; margin-top:4px;">JPG, PNG or WebP &bull; Maximum 2 MB</span>
                        </div>
                    </div>

                    <div id="profile-actions" style="display: none; margin-top: 20px; padding-top: 16px; border-top: 1px solid var(--a-border);">
                        <button type="submit" class="btn btn-primary">Save Changes</button>
                        <button type="button" id="btn-cancel-edit" class="btn btn-outline" style="margin-left: 8px;">Cancel</button>
                    </div>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="a-card" style="margin-top: 20px;">
                <div class="a-card-head dashboard-card-title" style="margin-bottom: 18px;">
                    <div>
                        <h3 style="margin:0;">Security & Password</h3>
                        <p style="margin:4px 0 0; color:var(--a-text-muted); font-size:0.8rem;">Ensure your account uses a strong and unique password.</p>
                    </div>
                </div>

                <form method="POST" action="{{ $passwordUpdateRoute }}">
                    @csrf
                    @method('PUT')

                    <div class="a-form-group">
                        <label for="current_password">Current Password</label>
                        <input id="current_password" type="password" name="current_password" class="a-input" autocomplete="current-password" required placeholder="Enter current password">
                    </div>

                    <div class="admin-profile-form-grid">
                        <div class="a-form-group">
                            <label for="new_password">New Password</label>
                            <input id="new_password" type="password" name="password" class="a-input" minlength="8" autocomplete="new-password" required placeholder="Min. 8 characters">
                        </div>
                        <div class="a-form-group">
                            <label for="password_confirmation">Confirm New Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" class="a-input" minlength="8" autocomplete="new-password" required placeholder="Repeat new password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" style="margin-top: 6px;">Change Password</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        (function() {
            const toggleBtn = document.getElementById('btn-toggle-edit');
            const cancelBtn = document.getElementById('btn-cancel-edit');
            const toggleLabel = document.getElementById('toggle-label');
            const toggleIcon = document.getElementById('toggle-icon');
            const fields = document.querySelectorAll('.profile-field');
            const imageGroup = document.getElementById('profile-image-group');
            const actions = document.getElementById('profile-actions');

            let isEditing = {{ $hasProfileErrors ? 'true' : 'false' }};

            // Store original values to restore on Cancel
            const originalValues = {};
            fields.forEach(field => {
                originalValues[field.id] = field.value;
            });

            function setEditMode(enable) {
                isEditing = enable;
                fields.forEach(field => {
                    if (enable) {
                        field.removeAttribute('readonly');
                        field.classList.remove('profile-input-readonly');
                    } else {
                        field.setAttribute('readonly', 'readonly');
                        field.classList.add('profile-input-readonly');
                    }
                });

                if (enable) {
                    imageGroup.style.display = 'block';
                    actions.style.display = 'block';
                    toggleLabel.textContent = 'Cancel';
                    toggleIcon.textContent = '✕';
                    toggleBtn.classList.add('btn-secondary');
                    document.getElementById('input-name').focus();
                } else {
                    imageGroup.style.display = 'none';
                    actions.style.display = 'none';
                    toggleLabel.textContent = 'Edit Profile';
                    toggleIcon.textContent = '✏️';
                    toggleBtn.classList.remove('btn-secondary');
                }
            }

            if (isEditing) {
                setEditMode(true);
            }

            toggleBtn.addEventListener('click', function() {
                if (isEditing) {
                    // Cancel changes
                    fields.forEach(field => {
                        if (originalValues[field.id] !== undefined) {
                            field.value = originalValues[field.id];
                        }
                    });
                    setEditMode(false);
                } else {
                    setEditMode(true);
                }
            });

            cancelBtn.addEventListener('click', function() {
                fields.forEach(field => {
                    if (originalValues[field.id] !== undefined) {
                        field.value = originalValues[field.id];
                    }
                });
                setEditMode(false);
            });
        })();
    </script>
@endsection