@extends('layouts.app')
@section('title', 'My Account | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My Account</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:520px;">
            <div class="card">
                <h3 style="margin-top:0;">Login</h3>
                <form method="POST" action="{{ route('account.login.submit') }}">
                    @csrf
                    <div class="form-group"><label>Email</label><input type="email" name="email" required class="form-control"></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required class="form-control"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                </form>
                <p class="text-center" style="margin:20px 0 0;">New customer? <a href="{{ route('account.register.form') }}" style="color:var(--brand-primary);font-weight:700;">Create an account</a></p>
            </div>
    </div>
</section>
@endsection
