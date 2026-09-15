@extends('layouts.app')
@section('title', 'Create Account | College Street Online')
@section('content')
    <style>
        .customer-auth-shell {
            display: grid !important;
            grid-template-columns: 1fr 1.4fr !important;
            gap: 48px !important;
            max-width: 1140px !important;
            margin: 0 auto;
            align-items: center;
        }
        @media (max-width: 900px) {
            .customer-auth-shell {
                grid-template-columns: 1fr !important;
                gap: 32px !important;
            }
        }
        .customer-auth-card {
            padding: 36px 32px !important;
            border-radius: 16px !important;
            border-top: 4px solid var(--accent-gold, #c59b27) !important;
            box-shadow: 0 15px 35px rgba(0,0,0,0.08) !important;
            background: var(--card-bg, #ffffff) !important;
        }
        .form-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        @media (max-width: 600px) {
            .form-grid-2col {
                grid-template-columns: 1fr;
            }
        }
        .form-grid-full {
            grid-column: 1 / -1;
        }
        .reg-feature-list {
            display: flex;
            flex-direction: column;
            gap: 14px;
            margin: 24px 0 28px 0;
        }
        .reg-feature-item {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            color: var(--text-primary);
            font-weight: 500;
        }
        .reg-feature-icon {
            width: 36px;
            height: 36px;
            border-radius: 8px;
            background: rgba(197, 155, 39, 0.12);
            color: var(--accent-gold, #c59b27);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }
        .btn-register-submit {
            width: 100%;
            padding: 13px 20px;
            font-size: 1rem;
            font-weight: 700;
            border-radius: 8px;
            background: var(--btn-gold-bg, linear-gradient(135deg, #d4af37 0%, #b48512 100%));
            color: #ffffff;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
            box-shadow: 0 4px 14px rgba(180, 133, 18, 0.25);
        }
        .btn-register-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(180, 133, 18, 0.35);
        }
    </style>

    <section class="section customer-auth-section">
        <div class="container customer-auth-shell">
            <div class="customer-auth-intro">
                <span class="eyebrow">Join College Street Online</span>
                <h1>Find your next great read.</h1>
                <p>Create an account to save your cart, enjoy localized market pricing, and follow global delivery progress.</p>
                
                <div class="reg-feature-list">
                    <div class="reg-feature-item">
                        <div class="reg-feature-icon">📚</div>
                        <span>Kolkata's legendary book market catalogue</span>
                    </div>
                    <div class="reg-feature-item">
                        <div class="reg-feature-icon">🌍</div>
                        <span>Localized pricing & global express shipping</span>
                    </div>
                    <div class="reg-feature-item">
                        <div class="reg-feature-icon">🔒</div>
                        <span>100% secure checkout in your currency</span>
                    </div>
                </div>

                <a href="{{ route('books.index') }}">Explore the catalogue &rarr;</a>
            </div>

            <div class="card customer-auth-card">
                <div class="customer-auth-heading">
                    <span>New customer</span>
                    <h2>Create account</h2>
                    <p>It takes less than a minute to get started.</p>
                </div>

                <form method="POST" action="{{ route('account.register') }}">
                    @csrf
                    <div class="form-grid-2col">
                        <div class="form-group">
                            <label for="register-name">Full name *</label>
                            <input id="register-name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Your full name" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="register-email">Email address *</label>
                            <input id="register-email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="you@example.com" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="register-country">Country / Region *</label>
                            <select id="register-country" name="country_code" class="form-control" required onchange="onCountryChange(this.value)">
                                <option value="">-- Select Country --</option>
                                @foreach($countries as $country)
                                    <option value="{{ $country->code }}" @selected(old('country_code', session('customer_country', 'IN')) === $country->code)>
                                        {{ $country->name }} ({{ $country->currency_code }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="auth-field-help">Sets market currency & shipping rules.</small>
                        </div>

                        <div class="form-group">
                            <label for="register-phone">Contact Phone</label>
                            <div style="display:flex;gap:6px;">
                                <select id="register-phone-code" name="phone_code" class="form-control" style="max-width:105px;padding-right:2px;">
                                    <option value="+91" @selected(old('phone_code') === '+91')>+91 (IN)</option>
                                    <option value="+1" @selected(old('phone_code') === '+1')>+1 (US/CA)</option>
                                    <option value="+44" @selected(old('phone_code') === '+44')>+44 (UK)</option>
                                    <option value="+65" @selected(old('phone_code') === '+65')>+65 (SG)</option>
                                    <option value="+971" @selected(old('phone_code') === '+971')>+971 (UAE)</option>
                                    <option value="+49" @selected(old('phone_code') === '+49')>+49 (DE)</option>
                                    <option value="+61" @selected(old('phone_code') === '+61')>+61 (AU)</option>
                                </select>
                                <input id="register-phone" type="tel" name="phone_number" value="{{ old('phone_number') }}" placeholder="Mobile number" class="form-control">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="register-currency">Preferred Display Currency</label>
                            <select id="register-currency" name="preferred_currency" class="form-control">
                                <option value="">Default (From Country)</option>
                                @foreach($currencies as $curr)
                                    <option value="{{ $curr->code }}" @selected(old('preferred_currency') === $curr->code)>
                                        {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="register-password">Password *</label>
                            <input id="register-password" type="password" name="password" required minlength="8" autocomplete="new-password" placeholder="Min 8 characters" class="form-control">
                        </div>

                        <div class="form-grid-full form-group" style="margin: 4px 0 14px 0;">
                            <label style="display:flex;align-items:flex-start;gap:8px;font-weight:normal;cursor:pointer;font-size:0.86rem;color:var(--text-secondary);">
                                <input type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent')) style="margin-top:3px;accent-color:var(--accent-gold, #c59b27);">
                                <span>I would like to receive updates on new book releases, literary festivals, and member discounts.</span>
                            </label>
                        </div>

                        <div class="form-grid-full">
                            <button type="submit" class="btn-register-submit">Create Account</button>
                        </div>
                    </div>
                </form>

                <p class="customer-auth-switch">Already registered? <a href="{{ route('account.login') }}">Login</a></p>
            </div>
        </div>
    </section>

    <script>
        const countryPhoneMap = {
            'IN': '+91',
            'US': '+1',
            'CA': '+1',
            'GB': '+44',
            'SG': '+65',
            'AE': '+971',
            'DE': '+49',
            'AU': '+61'
        };

        function onCountryChange(countryCode) {
            const phoneCodeSelect = document.getElementById('register-phone-code');
            if (countryPhoneMap[countryCode] && phoneCodeSelect) {
                phoneCodeSelect.value = countryPhoneMap[countryCode];
            }
        }
    </script>
@endsection