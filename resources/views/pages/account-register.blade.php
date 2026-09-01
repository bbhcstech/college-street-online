@extends('layouts.app')
@section('title', 'Create Account | College Street Online')
@section('content')
    <section class="section customer-auth-section">
        <div class="container customer-auth-shell">
            <div class="customer-auth-intro">
                <span class="eyebrow">Join College Street Online</span>
                <h1>Find your next great read.</h1>
                <p>Create an account to save your cart, order securely, and follow delivery progress.</p>
                <a href="{{ route('books.index') }}">Explore the catalogue →</a>
            </div>
            <div class="card customer-auth-card">
                <div class="customer-auth-heading">
                    <span>New customer</span>
                    <h2>Create account</h2>
                    <p>It only takes a moment to get started.</p>
                </div>
                <form method="POST" action="{{ route('account.register') }}">
                    @csrf
                    <div class="form-group"><label for="register-name">Full name</label><input id="register-name"
                            type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                            placeholder="Your full name" class="form-control"></div>
                    <div class="form-group"><label for="register-email">Email address</label><input id="register-email"
                            type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                            placeholder="you@example.com" class="form-control"></div>
                    <div class="form-group"><label for="register-password">Password</label><input id="register-password"
                            type="password" name="password" required minlength="8" autocomplete="new-password"
                            placeholder="Create a password" class="form-control"><small class="auth-field-help">Use at least
                            8 characters.</small></div>
                    <button type="submit" class="btn btn-gold" style="width:100%;">Register</button>
                </form>
                <p class="customer-auth-switch">Already registered? <a href="{{ route('account.login') }}">Login</a></p>
            </div>
        </div>
    </section>
@endsection