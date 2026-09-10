@extends('layouts.app')
@section('title', 'My Profile | College Street Online')
@section('content')
    <section class="section account-section">
        <div class="container" style="max-width:1000px;">
            <div class="breadcrumb-row">
                <a href="{{ route('home') }}">Home</a>
                <span class="sep">/</span>
                <span class="current">My Profile</span>
            </div>

            <div class="card account-header-card" style="margin-bottom:24px;padding:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:20px;">
                <div class="flex items-center" style="gap:20px;">
                    <div class="account-avatar" style="margin:0;">
                        @if($user->profile_image_url)
                            <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}">
                        @else
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        @endif
                    </div>
                    <div>
                        <div style="display:flex;align-items:center;gap:10px;margin-bottom:4px;flex-wrap:wrap;">
                            <h2 style="margin:0;font-size:1.35rem;">{{ $user->name }}</h2>
                            <span style="display:inline-block;padding:3px 10px;border-radius:99px;color:var(--success);background:rgba(31,157,108,0.1);font-size:0.72rem;font-weight:800;">Customer Account</span>
                        </div>
                        <p style="margin:0;color:var(--text-secondary);font-size:0.88rem;">{{ $user->email }}</p>
                    </div>
                </div>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a class="btn btn-outline" href="{{ route('account.orders') }}">View My Orders</a>
                    <a class="btn btn-outline" href="{{ route('books.index') }}">Browse Books</a>
                </div>
            </div>

            <div class="account-forms" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(340px, 1fr));gap:24px;">
                <div class="card account-form-card">
                    <div class="account-card-head">
                        <span>&#128100;</span>
                        <div>
                            <h3>Profile information</h3>
                            <p>Keep your contact details up to date.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Name</label>
                            <input name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="form-group">
                            <label>Email address</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="form-group">
                            <label for="customer-profile-image">Profile image</label>
                            <input id="customer-profile-image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="form-control">
                            <small class="auth-field-help">JPG, PNG or WebP. Maximum 2 MB.</small>
                        </div>
                        <button class="btn btn-primary">Save profile</button>
                    </form>
                </div>
                <div class="card account-form-card">
                    <div class="account-card-head">
                        <span>&#128274;</span>
                        <div>
                            <h3>Change password</h3>
                            <p>Use at least eight characters for security.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('account.password.update') }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label>Current password</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>New password</label>
                            <input type="password" name="password" class="form-control" minlength="8" required>
                        </div>
                        <div class="form-group">
                            <label>Confirm new password</label>
                            <input type="password" name="password_confirmation" class="form-control" minlength="8" required>
                        </div>
                        <button class="btn btn-primary">Change password</button>
                    </form>
                </div>
            </div>
        </div>
    </section>
@endsection