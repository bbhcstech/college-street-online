<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Publisher Login | College Street Online</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>
<body>
    <main class="admin-login-page">
        <section class="admin-login-brand">
            <a href="{{ route('home') }}" class="admin-login-logo">
                <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online">
                <span>College Street Online</span>
            </a>
            <div class="admin-login-copy">
                <span class="admin-login-eyebrow">Publisher workspace</span>
                <h1>Grow your catalogue and reach more readers.</h1>
                <p>Manage books, inventory, customer orders, offers, payments, and sales reports from one secure dashboard.</p>
                <div class="admin-login-features">
                    <span>Catalogue management</span><span>Order fulfilment</span><span>Sales insights</span>
                </div>
            </div>
            <p class="admin-login-foot">Secure access for approved publishers</p>
        </section>

        <section class="admin-login-form-panel">
            <div class="admin-login-form-wrap">
                <div class="admin-login-mobile-logo"><img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"></div>
                <span class="admin-login-kicker">Publisher Panel</span>
                <h2>Welcome back</h2>
                <p class="admin-login-subtitle">Sign in to manage your publishing business.</p>

                @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
                @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif

                <form method="POST" action="{{ route('publisher.login.submit') }}">
                    @csrf
                    <div class="a-form-group">
                        <label for="publisher-email">Email address</label>
                        <input id="publisher-email" type="email" name="email" value="{{ old('email') }}" class="a-input"
                            autocomplete="email" placeholder="publisher@example.com" required autofocus>
                    </div>
                    <div class="a-form-group">
                        <label for="publisher-password">Password</label>
                        <div class="admin-password-field">
                            <input id="publisher-password" type="password" name="password" class="a-input"
                                autocomplete="current-password" placeholder="Enter your password" required>
                            <button type="button" data-password-toggle aria-label="Show password">Show</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary admin-login-submit">Sign in to dashboard</button>
                </form>

                <div class="security-note">Your account is protected with secure sessions and login rate limiting.</div>
                <p class="admin-back-link">New publisher? <a href="{{ route('publisher.register') }}">Apply to join</a></p>
                <a href="{{ route('home') }}" class="admin-back-link">Return to website</a>
            </div>
        </section>
    </main>
    <script>
        document.querySelector('[data-password-toggle]').addEventListener('click', function () {
            const input = document.querySelector('#publisher-password');
            const show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            this.textContent = show ? 'Hide' : 'Show';
        });
    </script>
</body>
</html>
