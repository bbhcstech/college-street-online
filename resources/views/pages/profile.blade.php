@extends('layouts.app')
@section('title', 'My Profile | College Street Online')
@section('content')
    <section class="section">
        <div class="container" style="max-width:1000px;">
            <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                    class="current">My Profile</span></div>
            <h1>My Profile</h1>
            <div class="grid grid-2" style="align-items:start;gap:24px;">
                <div class="card">
                    <h3 style="margin-top:0;">Profile information</h3>
                    <form method="POST" action="{{ route('account.profile.update') }}">@csrf @method('PUT')
                        <div class="form-group"><label>Name</label><input name="name" class="form-control"
                                value="{{ old('name', $user->name) }}" required></div>
                        <div class="form-group"><label>Email address</label><input type="email" name="email"
                                class="form-control" value="{{ old('email', $user->email) }}" required></div><button
                            class="btn btn-primary">Save profile</button>
                    </form>
                </div>
                <div class="card">
                    <h3 style="margin-top:0;">Change password</h3>
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
    </section>
@endsection