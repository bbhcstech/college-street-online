@extends('layouts.app')
@section('title', 'Bulk Orders | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Bulk Orders</span></div></div>
<section class="page-hero"><div class="container"><span class="eyebrow"><span class="dot"></span> Institutions &amp; Libraries</span><h1>Bulk &amp; Institutional Orders</h1><p class="lead">Tell us which books and quantities you need. Our team will review availability and contact you with a quotation.</p></div></section>
<section class="section" style="padding-top:0;">
    <div class="container"><div class="grid grid-2" style="grid-template-columns:1.35fr .65fr;align-items:start;gap:28px;">
        <div class="card">
            <h3 style="margin-top:0;">Request a quotation</h3>
            <form method="POST" action="{{ route('bulk-orders.store') }}">@csrf
                <div class="grid grid-2" style="gap:14px;">
                    <div class="form-group"><label>Institution / Business Name</label><input name="institution_name" value="{{ old('institution_name') }}" class="form-control" required></div>
                    <div class="form-group"><label>Contact Person</label><input name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" class="form-control" required></div>
                    <div class="form-group"><label>Email Address</label><input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="form-control" required></div>
                    <div class="form-group"><label>Phone Number</label><input name="phone" value="{{ old('phone') }}" class="form-control" required></div>
                </div>
                <div class="form-group"><label>Books and quantities needed</label><textarea name="requirements" class="form-control" style="min-height:150px;" placeholder="Example: Organic Chemistry — 40 copies&#10;Modern Indian History — 25 copies" required>{{ old('requirements') }}</textarea></div>
                <div class="form-group"><label>Additional notes <span style="font-weight:400;color:var(--text-secondary);">(optional)</span></label><textarea name="notes" class="form-control" style="min-height:90px;" placeholder="Delivery deadline, location, invoice requirements, etc.">{{ old('notes') }}</textarea></div>
                <button class="btn btn-primary" style="width:100%;">Submit quote request</button>
            </form>
        </div>
        <div class="card" style="background:var(--surface-alt);">
            <h3 style="margin-top:0;">How it works</h3>
            <ol style="padding-left:20px;line-height:1.8;color:var(--text-secondary);"><li>Submit your required titles and quantities.</li><li>Our team checks stock and publisher availability.</li><li>We contact you with pricing and delivery details.</li><li>Confirm the quote to proceed with the order.</li></ol>
            <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:0;">Submitting this form does not place or charge an order.</p>
        </div>
    </div></div>
</section>
@endsection
