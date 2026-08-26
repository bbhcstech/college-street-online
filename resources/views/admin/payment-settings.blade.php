@extends('layouts.dashboard')
@php $homeRoute=route('admin.dashboard');$brandLabel='Admin Console';$crumb='Operations';$logoutRoute=route('admin.logout'); @endphp
@section('title','Payment QR')
@section('nav')@include('admin.partials.nav',['active'=>'payment-settings'])@endsection
@section('content')
<div class="publisher-page-head"><div><span class="analytics-eyebrow">Payment settings</span><h2>Checkout payment QR</h2><p>Manage the QR code customers use for manual payments.</p></div><span class="payment-live-pill"><i></i>{{ $qrUrl ? 'QR active' : 'Setup required' }}</span></div>

<div class="payment-settings-grid">
    <section class="a-card payment-upload-card">
        <div class="payment-card-head"><span>1</span><div><h3>Upload replacement QR</h3><p>The new image becomes visible at checkout immediately.</p></div></div>
        <form method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data" data-payment-qr-form>@csrf @method('PUT')
            <label for="payment-qr" class="payment-drop-zone" data-payment-drop><div class="payment-upload-icon">▣</div><strong>Choose a QR image</strong><span>PNG, JPG or WebP · Maximum 3 MB</span><input id="payment-qr" type="file" name="payment_qr" accept="image/png,image/jpeg,image/webp" required data-payment-input></label>
            <div class="payment-file-preview" data-payment-preview hidden><img alt="Selected QR preview" data-payment-preview-image><div><strong data-payment-file-name></strong><span>Ready to upload</span></div><button type="button" class="btn btn-outline btn-sm" data-payment-clear>Change</button></div>
            <div class="payment-warning"><strong>Before replacing</strong><p>Confirm the QR belongs to the correct payment account. The previous QR will be removed after a successful update.</p></div>
            <button class="btn btn-primary payment-submit" data-payment-submit disabled>{{ $qrUrl ? 'Replace payment QR' : 'Upload payment QR' }}</button>
        </form>
    </section>

    <section class="a-card payment-preview-card">
        <div class="payment-card-head"><span>2</span><div><h3>Current customer preview</h3><p>This is the image shown on the checkout page.</p></div></div>
        @if($qrUrl)
            <div class="payment-qr-frame"><img src="{{ $qrUrl }}" alt="Current checkout payment QR code"></div>
            <div class="payment-current-meta"><div><span>Status</span><strong class="payment-status-active">Active</strong></div><div><span>Last updated</span><strong>{{ $qr->updated_at?->format('d M Y, h:i A') }}</strong></div><div><span>Storage</span><strong>{{ str_starts_with($qr->value,'http') ? 'Cloud image' : 'Public storage' }}</strong></div></div>
            <a href="{{ $qrUrl }}" target="_blank" rel="noopener" class="btn btn-outline payment-open-qr">Open full-size QR</a>
        @else
            <div class="payment-empty"><span>▦</span><h4>No payment QR configured</h4><p>Upload a QR image so customers can complete checkout payments.</p></div>
        @endif
    </section>
</div>

<section class="a-card payment-flow-card"><div><span>1</span><strong>Customer scans QR</strong><small>Shown during checkout</small></div><b>→</b><div><span>2</span><strong>Customer submits UTR</strong><small>Payment proof is recorded</small></div><b>→</b><div><span>3</span><strong>Admin verifies payment</strong><small>Order becomes confirmed</small></div></section>

<section class="a-card" style="max-width:620px"><div class="payment-card-head"><span>%</span><div><h3>Publisher deduction</h3><p>Set the commission deducted when an administrator verifies a customer payment.</p></div></div><form method="POST" action="{{ route('admin.payment-settings.commission') }}">@csrf @method('PUT')<div class="form-group"><label for="publisher-commission">Commission percentage</label><input id="publisher-commission" class="a-input" type="number" name="publisher_commission_rate" value="{{ old('publisher_commission_rate',$commissionRate) }}" min="0" max="100" step="0.01" required><small style="display:block;margin-top:7px;color:var(--a-text-muted)">Example: 10% means the publisher receives 90%.</small></div><div class="payment-warning"><strong>Existing invoices remain unchanged</strong><p>The new rate applies only to payments verified after saving it.</p></div><button class="btn btn-primary">Save deduction rate</button></form></section>

<script>(()=>{const form=document.querySelector('[data-payment-qr-form]'),input=form?.querySelector('[data-payment-input]'),drop=form?.querySelector('[data-payment-drop]'),preview=form?.querySelector('[data-payment-preview]'),image=form?.querySelector('[data-payment-preview-image]'),name=form?.querySelector('[data-payment-file-name]'),submit=form?.querySelector('[data-payment-submit]'),clear=form?.querySelector('[data-payment-clear]');input?.addEventListener('change',()=>{const file=input.files[0];if(!file)return;image.src=URL.createObjectURL(file);name.textContent=file.name;drop.hidden=true;preview.hidden=false;submit.disabled=false});clear?.addEventListener('click',()=>{input.value='';preview.hidden=true;drop.hidden=false;submit.disabled=true});})();</script>
@endsection
