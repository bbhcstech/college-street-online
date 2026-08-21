@extends('layouts.app')
@section('title', 'Book Rights | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Book Rights</span></div></div>
<section class="page-hero"><div class="container"><span class="eyebrow"><span class="dot"></span> For Publishers</span><h1>Book Rights &amp; Distribution</h1><p class="lead">Concept designed per the SRS; not yet built out.</p></div></section>
<section class="section" style="padding-top:0;"><div class="container"><div class="card" style="max-width:700px;"><p style="margin:0;">Contact our publisher support team for rights-related requests in the meantime.</p><a href="{{ route('publisher.login') }}" class="btn btn-outline btn-sm" style="margin-top:14px;">Publisher Login &rarr;</a></div></div></section>
@endsection
