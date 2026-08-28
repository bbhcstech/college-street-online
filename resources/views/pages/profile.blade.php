@extends('layouts.app')
@section('title', 'My Profile | College Street Online')
@section('content')
    <section class="section account-section">
        <div class="container" style="max-width:1000px;">
            <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                    class="current">My Profile</span></div>
            <div class="shopping-page-head">
                <div><span class="eyebrow"><span class="dot"></span> My account</span>
                    <h1>Profile &amp; Security</h1>
                    <p>Manage your personal details and password.</p>
                </div><a class="btn btn-outline" href="{{ route('account.orders') }}">View my orders</a>
            </div>
            <div class="account-layout">
                <aside class="account-summary-card">
                    <div class="account-avatar">@if($user->profile_image_url)<img src="{{ $user->profile_image_url }}"
                    alt="{{ $user->name }}">@else{{ strtoupper(substr($user->name, 0, 1)) }}@endif</div>
                    <h2>{{ $user->name }}</h2>
                    <p>{{ $user->email }}</p><span>Customer account</span>
                        <nav><a class="active" href="{{ route('account.profile') }}">Profile settings</a><a
                            href="{{ route('account.orders') }}">Orders &amp; tracking</a><a
                            href="{{ route('books.index') }}">Browse books</a></nav>
                </aside>
                <div class="account-forms">
                    <div class="card account-form-card">
                        <div class="account-card-head"><span>&#128100;</span>
                            <div>
                                <h3>Profile information</h3>
                                <p>Keep your contact details up to date.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('account.profile.update') }}" enctype="multipart/form-data">
                            @csrf @method('PUT')
                            <div class="form-group"><label>Name</label><input name="name" class="form-control"
                                    value="{{ old('name', $user->name) }}" required></div>
                            <div class="form-group"><label>Email address</label><input type="email" name="email"
                                    class="form-control" value="{{ old('email', $user->email) }}" required></div>
                            <div class="form-group"><label for="customer-profile-image">Profile image</label><input
                                    id="customer-profile-image" type="file" name="profile_image"
                                    accept="image/png,image/jpeg,image/webp" class="form-control"><small
                                    class="auth-field-help">JPG, PNG or WebP. Maximum 2 MB.</small></div><button
                                class="btn btn-primary">Save profile</button>
                        </form>
                    </div>
                    <div class="card account-form-card">
                        <div class="account-card-head"><span>&#128274;</span>
                            <div>
                                <h3>Change password</h3>
                                <p>Use at least eight characters for security.</p>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('account.password.update') }}">@csrf @method('PUT')
                            <div class="form-group"><label>Current password</label><input type="password"
                                    name="current_password" class="form-control" required></div>
                            <div class="form-group"><label>New password</label><input type="password" name="password"
                                    class="form-control" minlength="8" required></div>
                            <div class="form-group"><label>Confirm new password</label><input type="password"
                                    name="password_confirmation" class="form-control" minlength="8" required></div><button
                                class="btn btn-primary">Change password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
