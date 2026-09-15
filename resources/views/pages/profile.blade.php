@extends('layouts.app')
@section('title', 'My Profile | College Street Online')
@section('content')
    <style>
        .cust-profile-container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 32px 16px 64px 16px;
        }
        .cust-profile-head {
            margin-bottom: 28px;
        }
        .cust-profile-head .breadcrumb-row {
            margin-bottom: 10px;
            font-size: 0.88rem;
            color: var(--text-muted, #94a3b8);
        }
        .cust-profile-head .breadcrumb-row a {
            color: var(--text-secondary, #64748b);
            text-decoration: none;
        }
        .cust-profile-head h1 {
            font-size: 1.8rem;
            margin: 0 0 6px 0;
            color: var(--text-primary, #0f172a);
            font-weight: 700;
        }
        .cust-profile-head p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-secondary, #64748b);
        }
        .cust-profile-grid {
            display: grid;
            grid-template-columns: 320px 1fr;
            gap: 28px;
            align-items: start;
        }
        @media (max-width: 860px) {
            .cust-profile-grid {
                grid-template-columns: 1fr;
            }
        }
        .cust-card {
            background: var(--card-bg, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 14px;
            padding: 28px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .cust-identity-card {
            text-align: center;
        }
        .cust-avatar {
            width: 88px;
            height: 88px;
            border-radius: 50%;
            margin: 0 auto 16px auto;
            background: var(--accent-gold, #c59b27);
            color: #ffffff;
            font-size: 2.2rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .cust-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .cust-identity-card h3 {
            font-size: 1.3rem;
            margin: 0 0 4px 0;
            color: var(--text-primary, #0f172a);
        }
        .cust-identity-card p {
            font-size: 0.88rem;
            color: var(--text-secondary, #64748b);
            margin: 0 0 12px 0;
        }
        .cust-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--success, #1f9d6c);
            background: rgba(31, 157, 108, 0.1);
            margin-bottom: 20px;
        }
        .cust-meta-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            padding: 16px 0;
            border-top: 1px solid var(--border-color, #f1f5f9);
            border-bottom: 1px solid var(--border-color, #f1f5f9);
            margin-bottom: 20px;
            text-align: left;
        }
        .cust-meta-grid small {
            display: block;
            font-size: 0.74rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--text-muted, #94a3b8);
            margin-bottom: 2px;
        }
        .cust-meta-grid strong {
            font-size: 0.95rem;
            color: var(--text-primary, #1e293b);
        }
        .cust-actions-stack {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .cust-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 22px;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--border-color, #f1f5f9);
        }
        .cust-card-head h3 {
            font-size: 1.18rem;
            margin: 0;
            color: var(--text-primary, #0f172a);
        }
        .cust-card-head p {
            font-size: 0.82rem;
            color: var(--text-secondary, #64748b);
            margin: 4px 0 0 0;
        }
        .profile-input-readonly {
            background-color: var(--bg-secondary, #f8fafc) !important;
            border-color: var(--border-color, #e2e8f0) !important;
            color: var(--text-primary, #1e293b) !important;
            cursor: default !important;
            pointer-events: none;
        }
        .profile-input-readonly:focus {
            outline: none !important;
            box-shadow: none !important;
        }
        .cust-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }
        @media (max-width: 600px) {
            .cust-form-grid {
                grid-template-columns: 1fr;
            }
        }
        .cust-form-full {
            grid-column: 1 / -1;
        }
        .cust-card .form-group {
            margin-bottom: 16px;
        }
        .cust-card label {
            display: block;
            font-size: 0.84rem;
            font-weight: 600;
            color: var(--text-primary, #334155);
            margin-bottom: 6px;
        }
        .cust-card .form-control {
            width: 100%;
            padding: 10px 14px;
            font-size: 0.92rem;
            border-radius: 8px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--input-bg, #ffffff);
            color: var(--text-primary, #0f172a);
        }
        .cust-card .form-control:focus {
            outline: none;
            border-color: var(--accent-gold, #c59b27);
            box-shadow: 0 0 0 3px rgba(197, 155, 39, 0.18);
        }
        .btn-toggle-edit {
            font-size: 0.84rem;
            padding: 6px 14px;
            border-radius: 6px;
            border: 1px solid var(--border-color, #cbd5e1);
            background: var(--card-bg, #ffffff);
            color: var(--text-primary, #334155);
            cursor: pointer;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-toggle-edit:hover {
            background: var(--bg-secondary, #f1f5f9);
            border-color: var(--accent-gold, #c59b27);
        }
    </style>

    <div class="cust-profile-container">
        <div class="cust-profile-head">
            <div class="breadcrumb-row">
                <a href="{{ route('home') }}">Home</a>
                <span class="sep">/</span>
                <span class="current">My Profile</span>
            </div>
            <h1>Account & Profile</h1>
            <p>Manage your personal profile, localized market preferences, and security settings.</p>
        </div>

        <div class="cust-profile-grid">
            <!-- Left Identity Card -->
            <div class="cust-card cust-identity-card">
                <div class="cust-avatar">
                    @if($user->profile_image_url)
                        <img src="{{ $user->profile_image_url }}" alt="{{ $user->name }}">
                    @else
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    @endif
                </div>
                <h3>{{ $user->name }}</h3>
                <p>{{ $user->email }}</p>
                <span class="cust-badge">Customer Account</span>

                <div class="cust-meta-grid">
                    <div>
                        <small>Member Since</small>
                        <strong>{{ $user->created_at ? $user->created_at->format('M Y') : 'N/A' }}</strong>
                    </div>
                    <div>
                        <small>Total Orders</small>
                        <strong>{{ $totalOrdersCount ?? 0 }}</strong>
                    </div>
                </div>

                <div class="cust-actions-stack">
                    <a class="btn btn-outline" href="{{ route('account.orders') }}" style="width:100%;text-align:center;">View My Orders</a>
                    <a class="btn btn-outline" href="{{ route('books.index') }}" style="width:100%;text-align:center;">Browse Books</a>
                </div>
            </div>

            <!-- Right Column Forms -->
            <div style="display:flex;flex-direction:column;gap:28px;">
                <!-- Profile Information Card -->
                <div class="cust-card">
                    <div class="cust-card-head">
                        <div>
                            <h3>Profile Information</h3>
                            <p>Keep your personal details and market preferences up to date.</p>
                        </div>
                        <button type="button" id="btn-toggle-edit" class="btn-toggle-edit">
                            <span id="toggle-icon">✏️</span>
                            <span id="toggle-label">Edit Profile</span>
                        </button>
                    </div>

                    <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data" id="customer-profile-form">
                        @csrf @method('PUT')
                        <div class="cust-form-grid">
                            <div class="form-group">
                                <label for="input-name">Full Name</label>
                                <input id="input-name" name="name" class="form-control profile-field profile-input-readonly" value="{{ old('name', $user->name) }}" readonly required>
                            </div>

                            <div class="form-group">
                                <label for="input-email">Email Address</label>
                                <input id="input-email" type="email" name="email" class="form-control profile-field profile-input-readonly" value="{{ old('email', $user->email) }}" readonly required>
                            </div>

                            @if(isset($countries))
                                <div class="form-group">
                                    <label for="profile-country">Country / Region</label>
                                    <select id="profile-country" name="country_code" class="form-control profile-field profile-input-readonly" disabled>
                                        <option value="">-- Select Country --</option>
                                        @foreach($countries as $country)
                                            <option value="{{ $country->code }}" @selected(old('country_code', $user->country_code ?? session('customer_country', 'IN')) === $country->code)>
                                                {{ $country->name }} ({{ $country->currency_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="auth-field-help">Sets localized pricing & shipping rules.</small>
                                </div>
                            @endif

                            <div class="form-group">
                                <label for="profile-phone">Contact Phone</label>
                                <div style="display:flex;gap:6px;">
                                    <select id="profile-phone-code" name="phone_code" class="form-control profile-field profile-input-readonly" style="max-width:110px;" disabled>
                                        <option value="+91" @selected(old('phone_code', $user->phone_code) === '+91')>+91 (IN)</option>
                                        <option value="+1" @selected(old('phone_code', $user->phone_code) === '+1')>+1 (US/CA)</option>
                                        <option value="+44" @selected(old('phone_code', $user->phone_code) === '+44')>+44 (UK)</option>
                                        <option value="+65" @selected(old('phone_code', $user->phone_code) === '+65')>+65 (SG)</option>
                                        <option value="+971" @selected(old('phone_code', $user->phone_code) === '+971')>+971 (UAE)</option>
                                        <option value="+49" @selected(old('phone_code', $user->phone_code) === '+49')>+49 (DE)</option>
                                        <option value="+61" @selected(old('phone_code', $user->phone_code) === '+61')>+61 (AU)</option>
                                    </select>
                                    <input id="profile-phone" type="tel" name="phone_number" class="form-control profile-field profile-input-readonly" value="{{ old('phone_number', $user->phone_number) }}" placeholder="Mobile number" readonly>
                                </div>
                            </div>

                            @if(isset($currencies))
                                <div class="form-group">
                                    <label for="profile-currency">Preferred Display Currency</label>
                                    <select id="profile-currency" name="preferred_currency" class="form-control profile-field profile-input-readonly" disabled>
                                        <option value="">Default (From Country)</option>
                                        @foreach($currencies as $curr)
                                            <option value="{{ $curr->code }}" @selected(old('preferred_currency', $user->preferred_currency) === $curr->code)>
                                                {{ $curr->code }} - {{ $curr->name }} ({{ $curr->symbol }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="cust-form-full form-group" style="margin-top:4px;">
                                <input type="hidden" name="marketing_consent_submitted" value="1">
                                <label style="display:flex;align-items:flex-start;gap:8px;font-weight:normal;cursor:pointer;">
                                    <input id="profile-marketing" type="checkbox" name="marketing_consent" value="1" @checked(old('marketing_consent', $user->marketing_consent)) class="profile-field" disabled style="margin-top:3px;accent-color:var(--accent-gold, #c59b27);">
                                    <span>Receive news on book launches, literary festivals & promotions</span>
                                </label>
                            </div>

                            <div class="cust-form-full form-group" id="profile-image-group" style="display: none;">
                                <label for="customer-profile-image">Profile Avatar Image</label>
                                <input id="customer-profile-image" type="file" name="profile_image" accept="image/png,image/jpeg,image/webp" class="form-control">
                                <small class="auth-field-help">JPG, PNG or WebP. Maximum 2 MB.</small>
                            </div>

                            <div class="cust-form-full" id="profile-actions" style="display: none; margin-top: 12px; padding-top: 16px; border-top: 1px solid var(--border-color, #f1f5f9);">
                                <button type="submit" class="btn btn-gold">Save Changes</button>
                                <button type="button" id="btn-cancel-edit" class="btn btn-outline" style="margin-left: 8px;">Cancel</button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Security Card -->
                <div class="cust-card">
                    <div class="cust-card-head">
                        <div>
                            <h3>Security & Password</h3>
                            <p>Ensure your account uses a strong and unique password.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('account.password.update') }}">
                        @csrf @method('PUT')
                        <div class="form-group">
                            <label for="current_password">Current password</label>
                            <input id="current_password" type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="cust-form-grid">
                            <div class="form-group">
                                <label for="new_password">New password</label>
                                <input id="new_password" type="password" name="password" class="form-control" minlength="8" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm new password</label>
                                <input id="confirm_password" type="password" name="password_confirmation" class="form-control" minlength="8" required>
                            </div>
                        </div>
                        <button class="btn btn-gold" style="margin-top:8px;">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('btn-toggle-edit');
            const cancelBtn = document.getElementById('btn-cancel-edit');
            const toggleIcon = document.getElementById('toggle-icon');
            const toggleLabel = document.getElementById('toggle-label');
            const fields = document.querySelectorAll('.profile-field');
            const imageGroup = document.getElementById('profile-image-group');
            const actionsDiv = document.getElementById('profile-actions');
            let isEditing = false;

            function enableEditing() {
                isEditing = true;
                fields.forEach(field => {
                    field.removeAttribute('readonly');
                    field.removeAttribute('disabled');
                    field.classList.remove('profile-input-readonly');
                });
                imageGroup.style.display = 'block';
                actionsDiv.style.display = 'block';
                toggleIcon.textContent = '🔒';
                toggleLabel.textContent = 'Lock Fields';
            }

            function disableEditing() {
                isEditing = false;
                fields.forEach(field => {
                    if (field.tagName === 'SELECT' || field.type === 'checkbox') {
                        field.setAttribute('disabled', 'disabled');
                    } else {
                        field.setAttribute('readonly', 'readonly');
                    }
                    field.classList.add('profile-input-readonly');
                });
                imageGroup.style.display = 'none';
                actionsDiv.style.display = 'none';
                toggleIcon.textContent = '✏️';
                toggleLabel.textContent = 'Edit Profile';
            }

            toggleBtn.addEventListener('click', function () {
                if (isEditing) {
                    disableEditing();
                } else {
                    enableEditing();
                }
            });

            cancelBtn.addEventListener('click', function () {
                disableEditing();
            });

            @if($errors->has('name') || $errors->has('email') || $errors->has('country_code') || $errors->has('phone_number') || $errors->has('profile_image'))
                enableEditing();
            @endif
        });
    </script>
@endsection