<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Publisher Application | College Street Online</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <style>
        .pub-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 14px;
        }
        @media (max-width: 540px) {
            .pub-form-grid {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>

<body>
    <main class="admin-login-page">
        <section class="admin-login-brand">
            <a href="{{ route('home') }}" class="admin-login-logo">
                <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online">
                <span>College Street Online</span>
            </a>
            <div class="admin-login-copy">
                <span class="admin-login-eyebrow">Publisher Onboarding</span>
                <h1>Partner with College Street Online.</h1>
                <p>Join Bengal's premier online literary marketplace. Reach thousands of readers, distribute your catalog, manage inventory, and fulfill book orders seamlessly.</p>
                <div class="admin-login-features">
                    <span>✓ Direct reader reach</span>
                    <span>✓ Catalog & stock control</span>
                    <span>✓ Automated settlement</span>
                    <span>✓ Verified publisher badge</span>
                </div>
            </div>
            <p class="admin-login-foot">Verified partner onboarding &bull; College Street Online</p>
        </section>

        <section class="admin-login-form-panel">
            <div class="admin-login-form-wrap" style="max-width: 480px;">
                <div class="admin-login-mobile-logo">
                    <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online">
                </div>
                <span class="admin-login-kicker">Publisher Portal</span>
                <h2>Publisher Application</h2>
                <p class="admin-login-subtitle">Submit your details. An admin will review and approve your account.</p>

                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ route('publisher.register.submit') }}">
                    @csrf
                    <div class="pub-form-grid">
                        <div class="a-form-group">
                            <label for="contact-name">Contact Person</label>
                            <input id="contact-name" type="text" name="name" value="{{ old('name') }}" class="a-input"
                                placeholder="Full name" required autofocus>
                        </div>
                        <div class="a-form-group">
                            <label for="business-name">Press / Business Name</label>
                            <input id="business-name" type="text" name="business_name" value="{{ old('business_name') }}" class="a-input"
                                placeholder="e.g. Dey's Publishing" required>
                        </div>
                    </div>

                    <div class="a-form-group">
                        <label for="publisher-email">Business Email Address</label>
                        <input id="publisher-email" type="email" name="email" value="{{ old('email') }}" class="a-input"
                            autocomplete="email" placeholder="publisher@example.com" required>
                    </div>

                    <div class="a-form-group">
                        <label for="contact-details">Contact Details & Address</label>
                        <textarea id="contact-details" name="contact_details" class="a-textarea" rows="2"
                            placeholder="Phone number, office address, or website notes (optional)">{{ old('contact_details') }}</textarea>
                    </div>

                    <div class="pub-form-grid">
                        <div class="a-form-group">
                            <label for="pub-password">Password</label>
                            <div class="admin-password-field">
                                <input id="pub-password" type="password" name="password" class="a-input"
                                    autocomplete="new-password" placeholder="Min. 8 characters" required minlength="8">
                                <button type="button" data-toggle="pub-password" aria-label="Show password">Show</button>
                            </div>
                        </div>
                        <div class="a-form-group">
                            <label for="pub-password-confirm">Confirm Password</label>
                            <div class="admin-password-field">
                                <input id="pub-password-confirm" type="password" name="password_confirmation" class="a-input"
                                    autocomplete="new-password" placeholder="Repeat password" required minlength="8">
                                <button type="button" data-toggle="pub-password-confirm" aria-label="Show password">Show</button>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary admin-login-submit">Submit Application &rarr;</button>
                </form>

                <div class="security-note">📋 Applications are manually vetted to ensure verified catalog listings. You will be notified once approved.</div>
                <p class="admin-back-link">Already registered? <a href="{{ route('publisher.login') }}">Sign in to Publisher Portal</a></p>
                <a href="{{ route('home') }}" class="admin-back-link">&larr; Return to website</a>
            </div>
        </section>
    </main>

    <script>
        document.querySelectorAll('[data-toggle]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const targetId = this.getAttribute('data-toggle');
                const input = document.getElementById(targetId);
                if (input) {
                    const show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    this.textContent = show ? 'Hide' : 'Show';
                }
            });
        });
    </script>
</body>

</html>