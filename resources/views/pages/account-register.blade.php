@extends('layouts.app')
@section('title', 'Create Account | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">Create Account</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:520px;">
        <div class="card">
            <h3 style="margin-top:0;">Create Account</h3>
            <form method="POST" action="{{ route('account.register') }}">
                @csrf
                <div class="form-group"><label>Full Name</label><input type="text" name="name" value="{{ old('name') }}" required class="form-control"></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" value="{{ old('email') }}" required class="form-control"></div>
                <div class="form-group"><label>Password</label><input type="password" name="password" required minlength="8" class="form-control"></div>
                <button type="submit" class="btn btn-gold" style="width:100%;">Register</button>
            </form>
            <p class="text-center" style="margin:20px 0 0;">Already registered? <a href="{{ route('account.login') }}" style="color:var(--brand-primary);font-weight:700;">Login</a></p>
        </div>
    </div>
</section>
@endsection
