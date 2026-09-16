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
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <p style="color: var(--a-text-muted); font-size: 0.9rem; margin: 0;">Update account profile details and security permissions for {{ $administrator->name }}.</p>
    </div>
    <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline" style="padding: 10px 18px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
        ← Back to Administrators
    </a>
</div>

<div class="a-card" style="padding: 32px; border-radius: 12px; background: var(--a-surface); border: 1px solid var(--a-border); box-shadow: var(--a-shadow-sm); max-width: 900px;">
    <form method="POST" action="{{ route('admin.administrators.update', $administrator) }}">
        @csrf @method('PUT')
        
        <!-- Section 1: Account Information -->
        <div style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid var(--a-border);">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <span style="font-size: 1.2rem;">👤</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--a-text); margin: 0;">Account Information</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="a-form-group">
                    <label for="admin-name" style="font-weight: 700; font-size: 0.88rem; color: var(--a-text); margin-bottom: 6px; display: block;">Full Name</label>
                    <input id="admin-name" class="a-input" name="name" value="{{ old('name', $administrator->name) }}" required style="height: 44px; border-radius: 8px; border: 1px solid var(--a-border); background: var(--a-surface-alt); color: var(--a-text); padding: 0 14px; font-size: 0.95rem;">
                    @error('name') <span style="color: var(--a-danger, #ef4444); font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="a-form-group">
                    <label for="admin-email" style="font-weight: 700; font-size: 0.88rem; color: var(--a-text); margin-bottom: 6px; display: block;">Email Address</label>
                    <input id="admin-email" class="a-input" type="email" name="email" value="{{ old('email', $administrator->email) }}" required style="height: 44px; border-radius: 8px; border: 1px solid var(--a-border); background: var(--a-surface-alt); color: var(--a-text); padding: 0 14px; font-size: 0.95rem;">
                    @error('email') <span style="color: var(--a-danger, #ef4444); font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="a-form-group" style="margin-top: 20px;">
                    <label for="admin-role" style="font-weight: 700; font-size: 0.88rem; color: var(--a-text); margin-bottom: 6px; display: block;">Administrator Role &amp; Permissions</label>
                    <select id="admin-role" name="role" class="a-input" required style="height: 44px; border-radius: 8px; border: 1px solid var(--a-border); background: var(--a-surface-alt); color: var(--a-text); padding: 0 14px; font-size: 0.95rem;">
                        <option value="admin" {{ old('role', $administrator->role) === 'admin' ? 'selected' : '' }}>🛡️ Standard Admin — Standard catalog, orders, and management access</option>
                        <option value="super_admin" {{ old('role', $administrator->role) === 'super_admin' ? 'selected' : '' }}>👑 Super Admin — Full system control &amp; administrator management access</option>
                    </select>
                    @error('role') <span style="color: var(--a-danger, #ef4444); font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <!-- Section 2: Password Update (Optional) -->
        <div style="margin-bottom: 28px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <span style="font-size: 1.2rem;">🔑</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--a-text); margin: 0;">New Password <span style="font-weight: 400; color: var(--a-text-muted); font-size: 0.88rem;">(Optional)</span></h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="a-form-group">
                    <label for="admin-password" style="font-weight: 700; font-size: 0.88rem; color: var(--a-text); margin-bottom: 6px; display: block;">New Password</label>
                    <input id="admin-password" class="a-input" type="password" name="password" minlength="8" placeholder="Leave blank to keep current password" style="height: 44px; border-radius: 8px; border: 1px solid var(--a-border); background: var(--a-surface-alt); color: var(--a-text); padding: 0 14px; font-size: 0.95rem;">
                    <small style="color: var(--a-text-muted); font-size: 0.8rem; margin-top: 4px; display: block;">Minimum 8 characters if changing.</small>
                    @error('password') <span style="color: var(--a-danger, #ef4444); font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="a-form-group">
                    <label for="admin-password-confirmation" style="font-weight: 700; font-size: 0.88rem; color: var(--a-text); margin-bottom: 6px; display: block;">Confirm New Password</label>
                    <input id="admin-password-confirmation" class="a-input" type="password" name="password_confirmation" placeholder="••••••••" style="height: 44px; border-radius: 8px; border: 1px solid var(--a-border); background: var(--a-surface-alt); color: var(--a-text); padding: 0 14px; font-size: 0.95rem;">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 12px; align-items: center; padding-top: 20px; border-top: 1px solid var(--a-border);">
            <button class="btn btn-primary" type="submit" style="padding: 11px 26px; border-radius: 8px; font-weight: 700; font-size: 0.95rem;">Save Changes</button>
            <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline" style="padding: 11px 20px; border-radius: 8px; font-size: 0.95rem;">Cancel</a>
        </div>
    </form>
</div>
@endsection
