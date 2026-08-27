<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Publisher Application | College Street Online</title>
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
</head>

<body>
    <div class="login-shell"
        style="min-height:100vh; display:flex; align-items:center; justify-content:center; background-color:var(--a-bg);padding:24px;">
        <div class="a-card" style="max-width:520px;width:100%;">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"
                style="height:48px;border-radius:10px;margin-bottom:16px;">
            <h2 style="margin-top:0;">Publisher Application</h2>
            <p style="color:var(--a-text-muted);">
                Submit your details. An admin must approve your account before you cansign in.
            </p>
            @if($errors->any())
                <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('publisher.register.submit') }}">
                @csrf
                <div class="a-form-group"><label>Contact Name</label><input type="text" name="name"
                        value="{{ old('name') }}" class="a-input" required></div>
                <div class="a-form-group">
                    <label>Business Name</label>
                    <input type="text" name="business_name" value="{{ old('business_name') }}" class="a-input" required></div>
                <div class="a-form-group"><label>Email</label><input type="email" name="email"
                        value="{{ old('email') }}" class="a-input" required></div>
                <div class="a-form-group"><label>Contact Details</label><textarea name="contact_details"
                        class="a-textarea">{{ old('contact_details') }}</textarea></div>
                <div class="a-form-group"><label>Password</label><input type="password" name="password" class="a-input"
                        required minlength="8"></div>
                <div class="a-form-group"><label>Confirm Password</label><input type="password"
                        name="password_confirmation" class="a-input" required minlength="8"></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Submit Application</button>
            </form>
            <p style="text-align:center;margin:18px 0 0;"><a href="{{ route('publisher.login') }}"
                    style="color:var(--a-primary);font-weight:700;">Back to Publisher Login</a></p>
        </div>
    </div>
</body>

</html>