<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title', config('app.name'))</title>
<meta name="description" content="@yield('description', 'College Street Online — books, delivered from Kolkata\'s legendary book market.')">
<link rel="icon" href="{{ asset('images/favicon.png') }}">
<link rel="stylesheet" href="{{ asset('css/site.css') }}">
<script>
    (function () {
        var stored = localStorage.getItem('cso-theme');
        if (stored === 'dark' || (!stored && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        }
    })();
</script>
</head>
<body>
@include('partials.header')
<main>
    @if (session('success'))
        <div class="container" style="padding-top:20px;"><div class="alert alert-success">{{ session('success') }}</div></div>
    @endif
    @if (session('info'))
        <div class="container" style="padding-top:20px;"><div class="alert alert-info">{{ session('info') }}</div></div>
    @endif
    @if ($errors->any() && ! View::hasSection('errors-inside-content'))
        <div class="container" style="padding-top:20px;"><div class="alert alert-danger">{{ $errors->first() }}</div></div>
    @endif
    @yield('content')
</main>
@include('partials.footer')
<script src="{{ asset('js/site.js') }}"></script>
</body>
</html>
