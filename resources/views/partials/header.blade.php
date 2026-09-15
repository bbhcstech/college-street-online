@php
    $cartCount = auth()->check() && auth()->user()->role === 'customer'
        ? \App\Models\Cart::where('customer_id', auth()->id())->count()
        : 0;
    $profileRoute = !auth()->check() ? route('account.login') : match (auth()->user()->role) {
        'admin' => route('admin.profile.edit'),
        'publisher' => route('publisher.profile.edit'),
        default => route('account.profile'),
    };
    $searchCategories = \App\Models\Category::orderBy('name')->get(['name', 'slug']);
@endphp

<header class="site-header cso-enhanced-header">
    <div class="container header-inner">
        {{-- Logo --}}
        <a href="{{ route('home') }}" class="brand cso-brand">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online logo" style="border-radius:10px; width:38px; height:38px; object-fit:cover; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
            <span style="font-family:var(--font-heading); font-weight:800; font-size:1.15rem; color:var(--text-primary); letter-spacing:-0.01em;">College Street <span style="color:var(--brand-primary, #1e3a8a);">Online</span></span>
        </a>

        {{-- Primary Navigation --}}
        <nav class="main-nav cso-main-nav" aria-label="Primary">
            <a href="{{ route('home') }}" class="cso-nav-item {{ request()->routeIs('home') ? 'active' : '' }}">
                Home
            </a>
            <a href="{{ route('books.index') }}" class="cso-nav-item {{ request()->routeIs('books.*') ? 'active' : '' }}">
                Browse Books
            </a>
            <a href="{{ route('bulk-orders') }}" class="cso-nav-item {{ request()->routeIs('bulk-orders') ? 'active' : '' }}">
                Bulk Orders
            </a>
            <a href="{{ route('about') }}" class="cso-nav-item {{ request()->routeIs('about') ? 'active' : '' }}">
                About Us
            </a>
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('account.support') }}" class="cso-nav-item {{ request()->routeIs('account.support') ? 'active' : '' }}">
                        Support
                    </a>
                @endif
            @endauth
        </nav>

        {{-- Search Box --}}
        <form class="search-box header-search cso-search-box" action="{{ route('books.index') }}" method="GET" data-search-form
            data-suggestions-url="{{ url('/books/suggestions') }}">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" class="cso-search-icon">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.3-4.3" />
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, author or ISBN..." autocomplete="off">
            <div class="search-select-divider"></div>
            <select name="category" aria-label="Filter by category" data-search-category>
                <option value="">All categories</option>
                @foreach($searchCategories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" aria-label="Search" class="cso-search-btn">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <circle cx="11" cy="11" r="8" /><path d="M21 21l-4.3-4.3" />
                </svg>
            </button>
            <div class="search-suggestions" data-search-suggestions hidden></div>
        </form>

        {{-- Header Actions --}}
        <div class="header-actions cso-header-actions">
            @auth
                @if(auth()->user()->isCustomer())
                    <button type="button" class="btn auth-portal-button customer-profile-trigger cso-profile-btn" data-customer-sidebar-toggle aria-label="Open Profile Menu">
                        <span class="header-profile-avatar">
                            @if(auth()->user()->profile_image_url)
                                <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}">
                            @else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="header-profile-name">{{ Illuminate\Support\Str::limit(auth()->user()->name, 12) }}</span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="opacity:0.6; transition:transform 0.2s ease;">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </button>
                @else
                    <details class="auth-portal">
                        <summary class="btn auth-portal-button cso-profile-btn">
                            <span class="header-profile-avatar">
                                @if(auth()->user()->profile_image_url)
                                    <img src="{{ auth()->user()->profile_image_url }}" alt="">
                                @else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="header-profile-name">{{ Illuminate\Support\Str::limit(auth()->user()->name, 12) }}</span>
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="opacity:0.6;">
                                <path d="M6 9l6 6 6-6"/>
                            </svg>
                        </summary>
                        <div class="auth-portal-menu">
                            <a href="{{ $profileRoute }}">My Profile</a>
                            <form method="POST" action="{{ route('account.logout') }}">
                                @csrf
                                <button type="submit">Logout</button>
                            </form>
                        </div>
                    </details>
                @endif
            @else
                <details class="auth-portal">
                    <summary class="btn auth-portal-button cso-login-btn">
                        <span>Login Portal</span>
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="opacity:0.6;">
                            <path d="M6 9l6 6 6-6"/>
                        </svg>
                    </summary>
                    <div class="auth-portal-menu">
                        <a href="{{ route('account.login') }}">Customer Login</a>
                        <a href="{{ route('publisher.login') }}">Publisher Login</a>
                        <a href="{{ route('admin.login') }}">Admin Login</a>
                    </div>
                </details>
            @endauth

            {{-- Cart Link --}}
            <a href="{{ route('cart.index') }}" class="icon-btn-nav cso-cart-btn" aria-label="Cart" title="Shopping Cart">
                <svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" />
                </svg>
                @if($cartCount)
                    <span class="cart-count cso-cart-badge">{{ $cartCount }}</span>
                @endif
            </a>

            {{-- Theme Toggle --}}
            <button type="button" class="theme-toggle cso-theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                <span class="knob">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                        <path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
                    </svg>
                </span>
            </button>

            {{-- Mobile Hamburger --}}
            <button type="button" class="hamburger" data-hamburger aria-label="Open menu" aria-expanded="false">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M3 6h18M3 12h18M3 18h18" />
                </svg>
            </button>
        </div>
    </div>
</header>

@include('partials.customer-sidebar')

<div class="mobile-nav">
    <div class="flex items-center" style="justify-content:space-between;margin-bottom:24px;">
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online" style="height:32px;border-radius:6px;">
        </a>
        <button type="button" data-close-nav aria-label="Close menu" style="background:none;border:none;cursor:pointer;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M18 6L6 18M6 6l12 12" />
            </svg>
        </button>
    </div>
    @auth
        <div class="mobile-profile-summary">
            <span class="header-profile-avatar">
                @if(auth()->user()->profile_image_url)
                    <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}">
                @else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                @endif
            </span>
            <div>
                <strong>{{ auth()->user()->name }}</strong>
                <small>{{ auth()->user()->email }}</small>
            </div>
        </div>
    @endauth
    <a href="{{ route('books.index') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
        Browse Books
    </a>
    <a href="{{ route('bulk-orders') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
        Bulk Orders
    </a>
    <a href="{{ route('about') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
        About Us
    </a>
    @auth
        <a href="{{ $profileRoute }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
            My Profile
        </a>
        @if(auth()->user()->isCustomer())
            <a href="{{ route('account.orders') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
                My Orders
            </a>
            <a href="{{ route('account.support') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
                Contact Support
            </a>
        @endif
        <form method="POST" action="{{ route('account.logout') }}" class="mobile-logout-form">
            @csrf
            <button type="submit" class="btn btn-outline">Logout</button>
        </form>
    @else
        <a href="{{ route('account.login') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
            Customer Login
        </a>
        <a href="{{ route('publisher.login') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
            Publisher Login
        </a>
        <a href="{{ route('admin.login') }}" style="display:block;padding:15px 4px;font-family:var(--font-heading);font-weight:700;border-bottom:1px solid var(--border);">
            Admin Login
        </a>
    @endauth
    <a href="{{ route('cart.index') }}" class="btn btn-primary mobile-cart-link">View Cart
        @if($cartCount)
            ({{ $cartCount }})
        @endif
    </a>
</div>

{{-- Navbar Custom CSS Enhancements --}}
<style>
    .cso-enhanced-header {
        background: rgba(255, 255, 255, 0.92) !important;
        backdrop-filter: blur(12px) saturate(160%) !important;
        border-bottom: 1px solid var(--border, #e2e8f0) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03) !important;
    }
    html.dark .cso-enhanced-header {
        background: rgba(15, 23, 42, 0.92) !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
    }
    .cso-brand {
        display: flex;
        align-items: center;
        gap: 10px;
        text-decoration: none;
        transition: transform 0.2s ease;
    }
    .cso-brand:hover {
        transform: translateY(-1px);
    }
    .cso-main-nav {
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cso-nav-item {
        padding: 8px 14px;
        font-family: var(--font-heading, inherit);
        font-weight: 600;
        font-size: 0.88rem;
        color: var(--text-secondary, #64748b);
        border-radius: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .cso-nav-item:hover {
        color: var(--brand-primary, #1e3a8a);
        background: color-mix(in srgb, var(--brand-primary, #1e3a8a) 6%, transparent);
    }
    .cso-nav-item.active {
        color: var(--brand-primary, #1e3a8a);
        font-weight: 700;
        background: color-mix(in srgb, var(--brand-primary, #1e3a8a) 10%, transparent);
    }
    html.dark .cso-nav-item.active {
        color: #60a5fa;
        background: rgba(96, 165, 250, 0.15);
    }
    .cso-search-box {
        display: flex;
        align-items: center;
        background: var(--bg-surface-alt, #f8fafc);
        border: 1px solid var(--border, #cbd5e1);
        border-radius: 50px !important;
        padding: 4px 6px 4px 14px;
        transition: all 0.2s ease;
    }
    .cso-search-box:focus-within {
        border-color: var(--brand-primary, #1e3a8a);
        box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand-primary, #1e3a8a) 15%, transparent);
        background: #ffffff;
    }
    .search-select-divider {
        width: 1px;
        height: 20px;
        background: var(--border, #cbd5e1);
        margin: 0 8px;
    }
    .cso-search-btn {
        background: var(--brand-primary, #1e3a8a) !important;
        color: #ffffff !important;
        border-radius: 50% !important;
        width: 32px !important;
        height: 32px !important;
        padding: 0 !important;
        display: grid !important;
        place-items: center !important;
        transition: transform 0.15s ease, background-color 0.2s ease !important;
    }
    .cso-search-btn:hover {
        transform: scale(1.06);
    }
    .cso-profile-btn, .cso-login-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 14px 6px 8px !important;
        border-radius: 50px !important;
        border: 1px solid var(--border, #e2e8f0) !important;
        background: var(--bg-surface, #ffffff) !important;
        font-size: 0.85rem !important;
        font-weight: 600 !important;
        color: var(--text-primary) !important;
        box-shadow: 0 2px 6px rgba(0,0,0,0.02) !important;
        transition: all 0.2s ease !important;
    }
    .cso-profile-btn:hover, .cso-login-btn:hover {
        border-color: var(--brand-primary, #1e3a8a) !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.06) !important;
    }
    .cso-cart-btn {
        position: relative;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        border: 1px solid var(--border, #e2e8f0);
        background: var(--bg-surface, #ffffff);
        color: var(--text-primary);
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .cso-cart-btn:hover {
        border-color: var(--brand-primary, #1e3a8a);
        color: var(--brand-primary, #1e3a8a);
        transform: translateY(-1px);
    }
    .cso-cart-badge {
        position: absolute;
        top: -4px;
        right: -4px;
        background: #ef4444 !important;
        color: #ffffff !important;
        font-size: 0.68rem !important;
        font-weight: 800 !important;
        padding: 2px 6px !important;
        border-radius: 20px !important;
        border: 2px solid #ffffff !important;
        box-shadow: 0 2px 6px rgba(239,68,68,0.4) !important;
    }
</style>
