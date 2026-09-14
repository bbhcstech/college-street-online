@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Marketplace';
    $logoutRoute = route('admin.logout');
    $editing = isset($publisher);
@endphp

@section('title', $editing ? 'Edit Publisher' : 'Add Publisher')
@section('nav')@include('admin.partials.nav', ['active' => 'publishers'])@endsection

@section('content')
<div class="publisher-page-head publisher-form-head" style="margin-bottom: 24px;">
    <div>
        <span class="analytics-eyebrow" style="color: #6366f1; font-weight: 600; font-size: 0.78rem; letter-spacing: 0.5px; text-transform: uppercase;">
            Marketplace Partners
        </span>
        <h2 style="font-size: 1.6rem; font-weight: 700; color: #111827; margin: 4px 0;">
            {{ $editing ? 'Edit Publisher Profile' : 'Add New Publisher' }}
        </h2>
        <p style="color: #6b7280; font-size: 0.88rem; margin: 0;">
            {{ $editing ? 'Update contact information, business details, and credentials.' : 'Create a new publisher account with business profile and login access.' }}
        </p>
    </div>
    <div>
        <a href="{{ route('admin.publishers.index') }}" class="btn btn-outline" style="height: 38px; display: inline-flex; align-items: center; gap: 6px; font-size: 0.85rem; border-radius: 6px;">
            ← Back to Publishers
        </a>
    </div>
</div>

@if($errors->any())
    <div style="padding: 14px 18px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 8px; color: #991b1b; margin-bottom: 24px; font-size: 0.88rem;">
        <strong style="display: block; margin-bottom: 6px; font-weight: 600;">
            Please correct the errors below:
        </strong>
        <ul style="margin: 0; padding-left: 18px;">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="a-card publisher-form-card" style="max-width: 820px; margin: 0 auto; padding: 28px 32px; border-radius: 12px; background: #ffffff; border: 1px solid var(--a-border, #e5e7eb); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);">
    <form method="POST" action="{{ $editing ? route('admin.publishers.update', $publisher) : route('admin.publishers.store') }}">
        @csrf
        @if($editing) 
            @method('PUT') 
        @endif

        <!-- SECTION 1: Account Information -->
        <div class="publisher-form-section" style="margin-bottom: 28px;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span>👤</span> Account &amp; Credentials
            </h3>
            <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 16px;">
                Primary contact person and portal access credentials.
            </p>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 18px;">
                <div class="a-form-group" style="margin: 0;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Contact Person Name <span style="color: #ef4444;">*</span></label>
                    <input type="text" name="name" value="{{ old('name', $publisher->user->name ?? '') }}" 
                           placeholder="e.g. Rahul Sharma"
                           style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; color: #111827; outline: none; background: #f9fafb;" required>
                </div>

                <div class="a-form-group" style="margin: 0;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Email Address <span style="color: #ef4444;">*</span></label>
                    <input type="email" name="email" value="{{ old('email', $publisher->user->email ?? '') }}" 
                           placeholder="publisher@example.com"
                           style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; color: #111827; outline: none; background: #f9fafb;" required>
                </div>
            </div>

            <div style="margin-top: 18px;">
                <div class="a-form-group" style="margin: 0;">
                    <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px;">
                        {{ $editing ? 'New Password' : 'Temporary Password' }} 
                        @if(!$editing)<span style="color: #ef4444;">*</span>@endif
                    </label>
                    <input type="password" name="password" 
                           placeholder="{{ $editing ? 'Leave blank to retain existing password' : 'At least 8 characters' }}"
                           style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; color: #111827; outline: none; background: #f9fafb;" 
                           {{ $editing ? '' : 'required' }} minlength="8">
                    @if($editing)
                        <span style="font-size: 0.78rem; color: #6b7280; margin-top: 4px; display: block;">Leave password field empty if you do not wish to update it.</span>
                    @endif
                </div>
            </div>
        </div>

        <hr style="border: none; border-top: 1px solid #f1f5f9; margin: 24px 0;">

        <!-- SECTION 2: Business & Contact Details -->
        <div class="publisher-form-section" style="margin-bottom: 28px;">
            <h3 style="font-size: 1.05rem; font-weight: 700; color: #1e293b; margin-bottom: 4px; display: flex; align-items: center; gap: 8px;">
                <span>🏢</span> Business &amp; Publishing House Profile
            </h3>
            <p style="font-size: 0.82rem; color: #64748b; margin-bottom: 16px;">Registered publishing entity name and official contact address.</p>

            <div class="a-form-group" style="margin-bottom: 18px;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Business / Publishing House Name <span style="color: #ef4444;">*</span></label>
                <input type="text" name="business_name" value="{{ old('business_name', $publisher->business_name ?? '') }}" 
                       placeholder="e.g. Dey's Publishing / Ananda Publishers"
                       style="width: 100%; height: 42px; padding: 0 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; color: #111827; outline: none; background: #f9fafb;" required>
            </div>

            <div class="a-form-group" style="margin: 0;">
                <label style="display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 6px;">Contact Details &amp; Address</label>
                <textarea name="contact_details" rows="4" 
                          placeholder="Enter phone numbers, office address, GST, or additional details..."
                          style="width: 100%; padding: 12px 14px; border: 1px solid #d1d5db; border-radius: 8px; font-size: 0.9rem; color: #111827; outline: none; background: #f9fafb; resize: vertical;">{{ old('contact_details', $publisher->contact_details ?? '') }}</textarea>
            </div>
        </div>

        <!-- FORM ACTIONS -->
        <div class="publisher-form-actions" style="display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 32px; padding-top: 20px; border-top: 1px solid #f1f5f9;">
            <a href="{{ route('admin.publishers.index') }}" class="btn btn-outline" style="height: 42px; padding: 0 20px; display: inline-flex; align-items: center; font-size: 0.9rem; border-radius: 8px;">
                Cancel
            </a>
            <button type="submit" class="btn btn-primary" style="height: 42px; padding: 0 28px; display: inline-flex; align-items: center; font-size: 0.9rem; font-weight: 600; border-radius: 8px;">
                {{ $editing ? 'Update Publisher' : 'Create Publisher' }}
            </button>
        </div>
    </form>
</div>
@endsection
