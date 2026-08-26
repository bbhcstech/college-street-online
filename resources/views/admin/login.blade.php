<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Admin Login | College Street Online</title><link rel="icon" href="{{ asset('images/favicon.png') }}"><link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head><body>
<main class="admin-login-page">
    <section class="admin-login-brand">
        <a href="{{ route('home') }}" class="admin-login-logo"><img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"><span>College Street Online</span></a>
        <div class="admin-login-copy"><span class="admin-login-eyebrow">Secure administration</span><h1>Manage the marketplace with clarity.</h1><p>Review sales, publishers, inventory, payments, and customer activity from one protected workspace.</p><div class="admin-login-features"><span>✓ Role-protected access</span><span>✓ Operational overview</span><span>✓ Secure session handling</span></div></div>
        <p class="admin-login-foot">Authorized administrators only</p>
    </section>
    <section class="admin-login-form-panel">
        <div class="admin-login-form-wrap">
            <div class="admin-login-mobile-logo"><img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"></div>
            <span class="admin-login-kicker">Admin Console</span><h2>Welcome back</h2><p class="admin-login-subtitle">Sign in to continue to your dashboard.</p>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.login.submit') }}">@csrf
                <div class="a-form-group"><label for="admin-email">Email address</label><input id="admin-email" type="email" name="email" value="{{ old('email') }}" class="a-input" autocomplete="email" placeholder="admin@example.com" required autofocus></div>
                <div class="a-form-group"><label for="admin-password">Password</label><div class="admin-password-field"><input id="admin-password" type="password" name="password" class="a-input" autocomplete="current-password" placeholder="Enter your password" required><button type="button" data-password-toggle aria-label="Show password">Show</button></div></div>
                <label class="a-checkbox admin-remember"><input type="checkbox" name="remember" value="1"> Keep me signed in</label>
                <button type="submit" class="btn btn-primary admin-login-submit">Sign in to dashboard →</button>
            </form>
            <div class="security-note">🔒 Your login is protected by rate limiting and secure sessions.</div>
            <a href="{{ route('home') }}" class="admin-back-link">← Return to website</a>
        </div>
    </section>
</main>
<script>document.querySelector('[data-password-toggle]').addEventListener('click',function(){const input=document.querySelector('#admin-password'),show=input.type==='password';input.type=show?'text':'password';this.textContent=show?'Hide':'Show';});</script>
</body></html>
