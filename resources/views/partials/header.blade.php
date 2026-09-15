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
    $headerCountries = \App\Models\Country::where('is_active', true)->orderBy('name')->get();
    $headerCountryCode = session('customer_country', auth()->check() ? (auth()->user()->country_code ?? 'IN') : 'IN');
    $headerCurrentCountry = $headerCountries->firstWhere('code', $headerCountryCode) ?? $headerCountries->firstWhere('code', 'IN');
@endphp
<header class="site-header">
    <div class="container header-inner">
        {{-- logo --}}
        <a href="{{ route('home') }}" class="brand">
            <img src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online logo"
                style="border-radius:8px;">
            <span>College Street Online</span>
        </a>

        {{-- navbar --}}
        <nav class="main-nav" aria-label="Primary">
            <a href="{{ route('home') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">
                Home
            </a>
            <a href="{{ route('books.index') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">
                Browse Books
            </a>
            <a href="{{ route('bulk-orders') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">
                Bulk Orders
            </a>
            <a href="{{ route('about') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">
                About Us
            </a>
            @auth
                @if(auth()->user()->isCustomer())
                    <a href="{{ route('account.support') }}" style="padding:10px 15px;font-family:var(--font-heading);font-weight:500;font-size:0.92rem;color:var(--text-secondary);">
                        Support
                    </a>
                @endif
            @endauth
        </nav>

        {{-- search box --}}
        <form class="search-box header-search" action="{{ route('books.index') }}" method="GET" data-search-form
            data-suggestions-url="{{ url('/books/suggestions') }}">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="11" cy="11" r="8" />
                <path d="M21 21l-4.3-4.3" />
            </svg>
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Title, author or ISBN">
            <select name="category" aria-label="Filter by category" data-search-category>
                <option value="">All categories</option>
                @foreach($searchCategories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" aria-label="Search">
                <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8" /><path d="M21 21l-4.3-4.3" />
                </svg>
            </button>
            <div class="search-suggestions" data-search-suggestions hidden></div>
        </form>

        <div class="header-actions">
            @auth
                @if(auth()->user()->isCustomer())
                    <button type="button" class="btn auth-portal-button customer-profile-trigger" data-customer-sidebar-toggle aria-label="Open Profile Menu">
                        <span class="header-profile-avatar">
                            @if(auth()->user()->profile_image_url)
                                <img src="{{ auth()->user()->profile_image_url }}" alt="{{ auth()->user()->name }}">
                            @else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            @endif
                        </span>
                        <span class="header-profile-name">{{ Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span>
                    </button>
                @else
                    <details class="auth-portal">
                        <summary class="btn auth-portal-button">
                            <span class="header-profile-avatar">
                                @if(auth()->user()->profile_image_url)
                                    <img src="{{ auth()->user()->profile_image_url }}" alt="">
                                @else{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                @endif
                            </span>
                            <span class="header-profile-name">{{ Illuminate\Support\Str::limit(auth()->user()->name, 14) }}</span>
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
                    <summary class="btn auth-portal-button">Login Portal</summary>
                    <div class="auth-portal-menu">
                        <a href="{{ route('account.login') }}">Customer Login</a>
                        <a href="{{ route('publisher.login') }}">Publisher Login</a>
                        <a href="{{ route('admin.login') }}">Admin Login</a>
                    </div>
                </details>
            @endauth

            {{-- Region & Currency Dropdown --}}
            <details class="auth-portal country-switcher-portal" style="position:relative;">
                <summary class="btn auth-portal-button" title="Change Region & Currency" style="font-size:0.8rem; padding:6px 10px; gap:4px; display:inline-flex; align-items:center;">
                    <span>🌐</span>
                    <span style="font-weight:700;">{{ $headerCurrentCountry ? $headerCurrentCountry->code : 'IN' }}</span>
                    <span style="opacity:0.75; font-size:0.75rem;">({{ $headerCurrentCountry ? $headerCurrentCountry->currency_code : 'INR' }})</span>
                </summary>
                <div class="auth-portal-menu" style="min-width:210px; padding:10px;">
                    <form method="POST" action="{{ route('country.switch') }}">
                        @csrf
                        <div style="font-size:0.7rem; font-weight:800; color:var(--text-muted, #64748b); text-transform:uppercase; margin-bottom:6px; letter-spacing:0.04em;">Select Region / Currency</div>
                        <select name="country" onchange="this.form.submit()" class="form-control" style="font-size:0.82rem; padding:6px 8px; width:100%; cursor:pointer;">
                            @foreach($headerCountries as $c)
                                <option value="{{ $c->code }}" @selected(($headerCurrentCountry->code ?? 'IN') === $c->code)>
                                    {{ $c->name }} ({{ $c->currency_code }})
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>
            </details>

            {{-- Cart Link --}}
            <a href="{{ route('cart.index') }}" class="icon-btn-nav" aria-label="Cart">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="9" cy="21" r="1" />
                    <circle cx="20" cy="21" r="1" />
                    <path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6" />
                </svg>
                @if($cartCount)
                    <span class="cart-count">{{ $cartCount }}</span>
                @endif
            </a>

            {{-- Theme Toggle --}}
            <button type="button" class="theme-toggle" data-theme-toggle aria-label="Toggle dark mode">
                <span class="knob">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="5" />
                        <path d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
                    </svg>
                </span>
            </button>

            {{-- Hamburger Menu for Mobile --}}
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
