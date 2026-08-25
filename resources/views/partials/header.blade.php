@php
    $cartCount = auth()->check() && auth()->user()->role === 'customer'
        ? \App\Models\Cart::where('customer_id', auth()->id())->count()
        : 0;
    $profileRoute = ! auth()->check() ? route('account.login') : match (auth()->user()->role) {
        'admin' => route('admin.profile.edit'),
        'publisher' => route('publisher.profile.edit'),
        default => route('account.profile'),
    };
@endphp
<header class="site-header">
    <div class="container header-inner">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online logo" style="border-radius:8px;">
            <span>College Street Online</span>
        </a>
        <nav class="main-nav" aria-label="Primary">
            <a href="{{ route('books.index') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">Browse Books</a>
            <a href="{{ route('bulk-orders') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">Bulk Orders</a>
            <a href="{{ route('about') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">About Us</a>
        </nav>
        <div class="header-actions">
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode"><span class="knob"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg></span></button>
            <a href="{{ $profileRoute }}" class="icon-btn-nav" aria-label="Account"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></a>
            <a href="{{ route('cart.index') }}" class="icon-btn-nav" aria-label="Cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>@if($cartCount)<span class="cart-count">{{ $cartCount }}</span>@endif</a>
            <a href="{{ route('books.index') }}" class="btn btn-gold header-cta">Shop Now</a>
            <details class="auth-portal">
                <summary class="btn auth-portal-button">{{ auth()->check() ? auth()->user()->name : 'Login Portal' }}</summary>
                <div class="auth-portal-menu">
                    @auth
                    <a href="{{ $profileRoute }}">My Profile</a>
                    @if(auth()->user()->isCustomer())<a href="{{ route('account.orders') }}">My Orders</a>@endif
                    <form method="POST" action="{{ route('account.logout') }}">
                        @csrf
                        <button type="submit">Logout</button>
                    </form>
                    @else
                    <a href="{{ route('account.login') }}">Customer Login</a>
                    <a href="{{ route('publisher.login') }}">Publisher Login</a>
                    <a href="{{ route('admin.login') }}">Admin Login</a>
                    @endauth
                </div>
            </details>
            <button type="button" class="hamburger" data-hamburger aria-label="Open menu"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
        </div>
    </div>
    <div class="search-strip">
        <div class="container">
            <form class="search-box" action="{{ route('books.index') }}" method="GET">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.3-4.3"/></svg>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by title, author, or ISBN — try Bengali script too">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
            </form>
        </div>
    </div>
</header>

<div class="mobile-nav">
    <div class="flex items-center" style="justify-content:space-between;margin-bottom:24px;">
        <a href="{{ route('home') }}" class="brand"><img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online" style="height:32px;border-radius:6px;"></a>
        <button type="button" data-close-nav aria-label="Close menu" style="background:none;border:none;cursor:pointer;"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg></button>
    </div>
    <a href="{{ route('books.index') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">Browse Books</a>
    <a href="{{ route('bulk-orders') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">Bulk Orders</a>
    <a href="{{ route('about') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">About Us</a>
    <a href="{{ route('cart.index') }}" class="btn btn-primary" style="margin-top:20px;width:100%;">View Cart</a>
</div>
