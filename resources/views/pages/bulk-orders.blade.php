@extends('layouts.app')
@section('title', 'Bulk Orders | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Bulk Orders</span></div></div>
<section class="page-hero"><div class="container"><span class="eyebrow"><span class="dot"></span> Institutions &amp; Libraries</span><h1>Bulk &amp; Institutional Orders</h1><p class="lead">Structure designed per the SRS — pricing tiers and minimum quantities are not yet implemented; this form routes a request to our team.</p></div></section>
<section class="section" style="padding-top:0;">
    <div class="container">
        <div class="card" style="max-width:640px;">
            <div class="form-group"><label>Institution Name</label><input type="text" class="form-control"></div>
            <div class="form-group"><label>Titles &amp; Quantities Needed</label><textarea class="form-control"></textarea></div>
            <button type="button" class="btn btn-primary" style="width:100%;">Request a Quote</button>
        </div>
    </div>
</section>
@endsection
