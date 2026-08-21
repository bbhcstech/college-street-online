@extends('layouts.app')
@section('title', 'My Account | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span class="current">My Account</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container" style="max-width:960px;">
        <div class="grid grid-2" style="gap:32px;">
            <div class="card">
                <h3 style="margin-top:0;">Login</h3>
                <form method="POST" action="{{ route('account.login.submit') }}">
                    @csrf
                    <div class="form-group"><label>Email</label><input type="email" name="email" required class="form-control"></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required class="form-control"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                </form>
            </div>
            <div class="card">
                <h3 style="margin-top:0;">Create Account</h3>
                <form method="POST" action="{{ route('account.register') }}">
                    @csrf
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" required class="form-control"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" required class="form-control"></div>
                    <div class="form-group"><label>Password</label><input type="password" name="password" required minlength="8" class="form-control"></div>
                    <button type="submit" class="btn btn-gold" style="width:100%;">Register</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
