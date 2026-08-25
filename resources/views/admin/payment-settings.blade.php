@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Operations';
    $logoutRoute = route('admin.logout');
@endphp
@section('title', 'Payment QR')
@section('nav')@include('admin.partials.nav', ['active' => 'payment-settings'])@endsection
@section('content')
<div class="a-grid a-grid-2" style="align-items:start;">
    <div class="a-card">
        <div class="a-card-head"><div class="a-card-icon">▦</div><div><h3 style="margin:0;">Customer payment QR</h3><div style="font-size:.8rem;color:var(--a-text-muted);margin-top:3px;">Shown securely on the checkout page.</div></div></div>
        <form method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="a-form-group">
                <label for="payment-qr">Upload QR image</label>
                <input id="payment-qr" type="file" name="payment_qr" accept="image/png,image/jpeg,image/webp" class="a-input" required>
                <div class="hint">PNG, JPG or WebP. Maximum 3 MB. A new upload replaces the current QR.</div>
            </div>
            <button class="btn btn-primary">Upload payment QR</button>
        </form>
    </div>
    <div class="a-card" style="text-align:center;">
        <h3>Current QR preview</h3>
        @if($qr?->value)
            <img src="{{ $qr->value }}" alt="Current payment QR code" style="width:100%;max-width:300px;aspect-ratio:1;object-fit:contain;padding:14px;border:1px solid var(--a-border);border-radius:14px;background:#fff;">
            <p style="color:var(--a-text-muted);font-size:.8rem;">Customers can see this QR during checkout.</p>
        @else
            <div style="padding:55px 20px;border:2px dashed var(--a-border);border-radius:14px;color:var(--a-text-muted);">No payment QR uploaded yet.</div>
        @endif
    </div>
</div>
@endsection
