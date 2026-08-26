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
    <div class="login-shell"
        style="min-height:100vh;display:flex;align-items:center;justify-content:center;background-color:var(--a-bg);">
        <div class="a-card" style="max-width:400px;width:100%;">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"
                style="height:48px;border-radius:10px;margin-bottom:16px;">
            <h2 style="margin-top:0;">Publisher Login</h2>
            @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>@endif
            @if($errors->any())
            <div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('publisher.login.submit') }}">
                @csrf
                <div class="a-form-group"><label>Email</label><input type="email" name="email" class="a-input" required>
                </div>
                <div class="a-form-group"><label>Password</label><input type="password" name="password" class="a-input"
                        required></div>
                <button type="submit" class="btn btn-primary" style="width:100%;">Sign In</button>
            </form>
            <p style="text-align:center;margin:18px 0 0;">Want to sell books? <a
                    href="{{ route('publisher.register') }}" style="color:var(--a-primary);font-weight:700;">Apply as
                    Publisher</a></p>
        </div>
    </div>
</body>

</html>