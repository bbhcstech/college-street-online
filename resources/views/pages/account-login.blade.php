@extends('layouts.app')
@section('title', 'My Account | College Street Online')
@section('errors-inside-content', 'true')
@section('content')
    <section class="section customer-auth-section">
        <div class="container customer-auth-shell">
            <div class="customer-auth-intro">
                <span class="eyebrow">Welcome back</span>
                <h1>Your books are waiting.</h1>
                <p>Sign in to manage your cart, place orders, and track every delivery.</p>
                <a href="{{ route('books.index') }}">Browse books first →</a>
            </div>
            <div class="card customer-auth-card">
                <div class="customer-auth-heading">
                    <span>Customer account</span>
                    <h2>Login</h2>
                    <p>Enter the details used when you registered.</p>
                </div>
                @if($errors->any())
                    <div class="alert alert-danger customer-login-error">{{ $errors->first() }}</div>
                @endif
                <form method="POST" action="{{ route('account.login.submit') }}">
                    @csrf
                    <div class="form-group"><label for="login-email">Email address</label><input id="login-email"
                            type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="you@example.com" class="form-control"></div>
                    <div class="form-group"><label for="login-password">Password</label><input id="login-password"
                            type="password" name="password" required autocomplete="current-password"
                            placeholder="Enter your password" class="form-control"></div>
                    <button type="submit" class="btn btn-primary" style="width:100%;">Login</button>
                </form>
                <p class="customer-auth-switch">New customer? <a href="{{ route('account.register.form') }}">Create an
                        account</a></p>
            </div>
        </div>
    </section>
@endsection