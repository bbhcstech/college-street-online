@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $logoutRoute = route('admin.logout');
    $crumb = 'Access Control';
    $active = 'administrators';
@endphp
@section('title', 'Add Administrator')
@section('nav') @include('admin.partials.nav', ['active' => $active]) @endsection

@section('content')
<div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
    <div>
        <span class="a-eyebrow" style="text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.8px; color: #64748b;">Access Control</span>
        <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 4px 0;">Add New Administrator</h2>
        <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Create a new administrator account and assign system access permissions.</p>
    </div>
    <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline" style="padding: 10px 18px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px;">
        ← Back to Administrators
    </a>
</div>

<div class="a-card" style="padding: 32px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); max-width: 900px;">
    <form method="POST" action="{{ route('admin.administrators.store') }}">
        @csrf
        
        <!-- Section 1: Account Information -->
        <div style="margin-bottom: 28px; padding-bottom: 20px; border-bottom: 1px solid #f1f5f9;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <span style="font-size: 1.2rem;">👤</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Account Information</h3>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="a-form-group">
                    <label for="admin-name" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Full Name</label>
                    <input id="admin-name" class="a-input" name="name" value="{{ old('name') }}" placeholder="e.g. John Doe" required style="height: 44px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 0.95rem;">
                    @error('name') <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="a-form-group">
                    <label for="admin-email" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Email Address</label>
                    <input id="admin-email" class="a-input" type="email" name="email" value="{{ old('email') }}" placeholder="e.g. admin@collegestreetonline.com" required style="height: 44px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 0.95rem;">
                    @error('email') <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
            </div>

            @if(auth()->user()->isSuperAdmin())
                <div class="a-form-group" style="margin-top: 20px;">
                    <label for="admin-role" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Administrator Role &amp; Permissions</label>
                    <select id="admin-role" name="role" class="a-input" required style="height: 44px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 0.95rem;">
                        <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>🛡️ Standard Admin — Standard catalog, orders, and management access</option>
                        <option value="super_admin" {{ old('role') === 'super_admin' ? 'selected' : '' }}>👑 Super Admin — Full system control &amp; administrator management access</option>
                    </select>
                    @error('role') <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>
            @endif
        </div>

        <!-- Section 2: Security Credentials -->
        <div style="margin-bottom: 28px;">
            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 16px;">
                <span style="font-size: 1.2rem;">🔑</span>
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0;">Security Credentials</h3>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px;">
                <div class="a-form-group">
                    <label for="admin-password" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Account Password</label>
                    <input id="admin-password" class="a-input" type="password" name="password" minlength="8" required placeholder="••••••••" style="height: 44px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 0.95rem;">
                    <small style="color: #64748b; font-size: 0.8rem; margin-top: 4px; display: block;">Minimum 8 characters required.</small>
                    @error('password') <span style="color: #ef4444; font-size: 0.8rem; font-weight: 600; margin-top: 4px; display: block;">{{ $message }}</span> @enderror
                </div>

                <div class="a-form-group">
                    <label for="admin-password-confirmation" style="font-weight: 700; font-size: 0.88rem; color: #334155; margin-bottom: 6px; display: block;">Confirm Password</label>
                    <input id="admin-password-confirmation" class="a-input" type="password" name="password_confirmation" required placeholder="••••••••" style="height: 44px; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 14px; font-size: 0.95rem;">
                </div>
            </div>
        </div>

        <!-- Form Actions -->
        <div style="display: flex; gap: 12px; align-items: center; padding-top: 20px; border-top: 1px solid #f1f5f9;">
            <button class="btn btn-primary" type="submit" style="padding: 11px 26px; border-radius: 8px; font-weight: 700; font-size: 0.95rem; display: inline-flex; align-items: center; gap: 6px;">
                <span style="font-size: 1.1rem; line-height: 1;">+</span> Create Administrator
            </button>
            <a href="{{ route('admin.administrators.index') }}" class="btn btn-outline" style="padding: 11px 20px; border-radius: 8px; font-size: 0.95rem;">Cancel</a>
        </div>
    </form>
</div>
@endsection
