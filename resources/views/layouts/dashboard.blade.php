<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') | College Street Online</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('images/favicon.png') }}">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    <script>
        (function () {
            var t = localStorage.getItem('cso-theme');
            if (t === 'dark' || (!t && matchMedia('(prefers-color-scheme: dark)').matches)) document.documentElement.classList.add('dark');
        })();
    </script>
</head>

<body>
    <div class="admin-shell">
        <aside class="admin-sidebar">
            <a href="{{ $homeRoute ?? '#' }}" class="brand">
                <img src="{{ asset('images/logo-square.jpg') }}" alt="CSO" style="height:32px;width:32px;border-radius:7px;object-fit:cover;"> {{ $brandLabel ?? 'Panel' }}
            </a>
            @yield('nav')
            <form method="POST" action="{{ $logoutRoute }}" class="sidebar-logout-form">
                @csrf
                <button type="submit" class="sidebar-logout-button">
                    <span class="nav-icon">&#8594;</span><span>Logout</span>
                </button>
            </form>
        </aside>
        <div class="admin-main">
            <div class="admin-topbar">
                <div class="topbar-titles">
                    <div class="crumb">{{ $crumb ?? '' }}</div>
                    <h1>@yield('title', 'Dashboard')</h1>
                </div>
                <div class="topbar-actions">
                    <button type="button" class="admin-theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                        <span class="knob">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="5" />
                                <path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
                            </svg>
                        </span>
                    </button>
                    <a href="{{ route('home') }}" target="_blank" class="btn btn-outline btn-sm">View Site &#8599;</a>
                    @php($profileRoute = auth()->user()?->isAdmin() ? route('admin.profile.edit') : route('publisher.profile.edit'))
                    <a href="{{ $profileRoute }}" class="user-chip">
                        <div class="avatar" style="overflow:hidden;flex:0 0 30px;">
                            @if(auth()->user()->profile_image_url)
                                <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}"style="display:block;width:30px;height:30px;max-width:30px;object-fit:cover;border-radius:50%;">
                            @else{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            
                            @endif
                        </div>
                        <div class="meta">
                            <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                            <span>Profile</span>
                        </div>
                    </a>
                    <form method="POST" action="{{ $logoutRoute }}">
                        @csrf
                        <button type="submit" class="btn btn-outline btn-sm">Logout</button>
                    </form>
                </div>
            </div>
            <div class="admin-content">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>@endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>@endif
                @yield('content')
            </div>
        </div>
    </div>
    <script src="{{ asset('js/admin.js') }}"></script>
    <script>
        (function () {
            var r = document.documentElement;
            document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
                b.addEventListener('click', function () {
                    var n = r.classList.contains('dark') ? 'light' : 'dark';
                    r.classList.toggle('dark');
                    localStorage.setItem('cso-theme', n);
                });
            });
        })();
    </script>
</body>

</html>