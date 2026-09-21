@extends('layouts.app')
@section('title', 'About Us | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                class="current">About Us</span></div>
    </div>
    <section class="page-hero">
        <div class="container"><span class="eyebrow"><span class="dot"></span> About Us</span>
            <h1>Kolkata's Book Market, Reimagined for the Web</h1>
            <p class="lead">College Street has been the heart of Bengal's book trade for over a century. College Street
                Online brings that same spirit of discovery, trust, and community online.</p>
        </div>
    </section>
    <section class="section" style="padding-top:0;">
        <div class="container">
            <div class="grid grid-3">
                <div class="card"><span class="badge-tag">Mission</span>
                    <p style="color:var(--text-primary);font-weight:600;">Make every genuine book accessible to every
                        reader, everywhere.</p>
                </div>
                <div class="card"><span class="badge-tag">Vision</span>
                    <p style="color:var(--text-primary);font-weight:600;">To be India's most trusted online home for
                        academic and Bengali literature.</p>
                </div>
                <div class="card"><span class="badge-tag">Values</span>
                    <p style="color:var(--text-primary);font-weight:600;">Authenticity, fair pricing, and publisher
                        partnership over marketplace extraction.</p>
                </div>
            </div>
        </div>
    </section>
@endsection