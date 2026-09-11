@php
    $active = $active ?? '';
@endphp

<style>
.admin-sidebar a.nav-link {
    cursor: pointer;
    user-select: none;
}
.nav-arrow {
    margin-left: auto;
    font-size: 0.75rem;
    transition: transform 0.25s ease;
    opacity: 0.7;
    display: inline-block;
}
.nav-group.is-open .nav-arrow,
.nav-group.has-active .nav-arrow {
    transform: rotate(180deg);
    opacity: 1;
}
.nav-group.is-open .nav-sub-items,
.nav-group.has-active .nav-sub-items {
    max-height: 500px !important;
    opacity: 1 !important;
    pointer-events: auto !important;
    margin-top: 4px !important;
    margin-bottom: 8px !important;
}
</style>

<!-- 📊 Dashboard -->
<div class="nav-group {{ $active === 'dashboard' ? 'has-active' : '' }}">
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
        <span class="nav-icon">📊</span><span>Dashboard</span>
    </a>
</div>

<!-- 📚 Books -->
<div class="nav-group {{ in_array($active, ['books', 'books_create', 'categories', 'inventory']) || request()->routeIs('admin.books.*') || request()->routeIs('admin.inventory.*') ? 'has-active' : '' }}">
    <a href="{{ route('admin.books.index') }}" class="nav-link {{ in_array($active, ['books', 'books_create', 'categories', 'inventory']) || request()->routeIs('admin.books.*') || request()->routeIs('admin.inventory.*') ? 'active' : '' }}">
        <span class="nav-icon">📚</span><span>Books</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.books.index') }}" class="nav-sub-link {{ (request()->routeIs('admin.books.index') || $active === 'books') && !request()->routeIs('admin.books.create') && $active !== 'books_create' ? 'active' : '' }}">All Books</a>
        <a href="{{ route('admin.books.create') }}" class="nav-sub-link {{ request()->routeIs('admin.books.create') || $active === 'books_create' ? 'active' : '' }}">Add Book</a>
        <a href="{{ route('admin.categories.index') }}" class="nav-sub-link {{ request()->routeIs('admin.categories.*') || $active === 'categories' ? 'active' : '' }}">Authors &amp; Categories</a>
        <a href="{{ route('admin.inventory.index') }}" class="nav-sub-link {{ request()->routeIs('admin.inventory.*') || $active === 'inventory' ? 'active' : '' }}">Inventory</a>
    </div>
</div>

<!-- 🛒 Orders -->
@php
    $isPaymentQuery = request()->filled('payment');
    $isOrdersActive = (in_array($active, ['orders', 'bulk-orders']) || request()->routeIs('admin.orders.*')) && !$isPaymentQuery;
    $isPaymentsActive = $active === 'payment-settings' || request()->routeIs('admin.payment-settings.*') || $isPaymentQuery;
@endphp
<div class="nav-group {{ $isOrdersActive ? 'has-active' : '' }}">
    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $isOrdersActive ? 'active' : '' }}">
        <span class="nav-icon">🛒</span><span>Orders</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.orders.index') }}" class="nav-sub-link {{ $isOrdersActive && request()->routeIs('admin.orders.index') && !request()->filled('status') ? 'active' : '' }}">All Orders</a>
        <a href="{{ route('admin.orders.index', ['status' => 'pending_payment']) }}" class="nav-sub-link {{ $isOrdersActive && request('status') === 'pending_payment' ? 'active' : '' }}">Pending</a>
        <a href="{{ route('admin.orders.index', ['status' => 'processing']) }}" class="nav-sub-link {{ $isOrdersActive && request('status') === 'processing' ? 'active' : '' }}">Processing</a>
        <a href="{{ route('admin.orders.index', ['status' => 'shipped']) }}" class="nav-sub-link {{ $isOrdersActive && request('status') === 'shipped' ? 'active' : '' }}">Shipped</a>
        <a href="{{ route('admin.orders.index', ['status' => 'delivered']) }}" class="nav-sub-link {{ $isOrdersActive && request('status') === 'delivered' ? 'active' : '' }}">Delivered</a>
        <a href="{{ route('admin.orders.index', ['status' => 'return_requested']) }}" class="nav-sub-link {{ $isOrdersActive && request('status') === 'return_requested' ? 'active' : '' }}">Returns &amp; Refunds</a>
    </div>
</div>

<!-- 💳 Payments -->
<div class="nav-group {{ $isPaymentsActive ? 'has-active' : '' }}">
    <a href="{{ route('admin.payment-settings.edit') }}" class="nav-link {{ $isPaymentsActive ? 'active' : '' }}">
        <span class="nav-icon">💳</span><span>Payments</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.orders.index', ['payment' => 'pending']) }}" class="nav-sub-link {{ request('payment') === 'pending' ? 'active' : '' }}">Payment Verification</a>
        <a href="{{ route('admin.orders.index', ['payment' => 'verified']) }}" class="nav-sub-link {{ request('payment') === 'verified' ? 'active' : '' }}">Transactions</a>
        <a href="{{ route('admin.payment-settings.edit') }}" class="nav-sub-link {{ $active === 'payment-settings' || request()->routeIs('admin.payment-settings.*') ? 'active' : '' }}">Payment Settings</a>
    </div>
</div>

<!-- 🌍 International -->
<div class="nav-group {{ in_array($active, ['countries', 'currencies']) || request()->routeIs('admin.countries.*') || request()->routeIs('admin.currencies.*') ? 'has-active' : '' }}">
    <a href="{{ route('admin.countries.index') }}" class="nav-link {{ in_array($active, ['countries', 'currencies']) || request()->routeIs('admin.countries.*') || request()->routeIs('admin.currencies.*') ? 'active' : '' }}">
        <span class="nav-icon">🌍</span><span>International</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.countries.index') }}" class="nav-sub-link {{ $active === 'countries' || request()->routeIs('admin.countries.*') ? 'active' : '' }}">Countries &amp; Shipping Rates</a>
        <a href="{{ route('admin.currencies.index') }}" class="nav-sub-link {{ $active === 'currencies' || request()->routeIs('admin.currencies.*') ? 'active' : '' }}">Currency Rates</a>
    </div>
</div>

<!-- 🏢 Publishers -->
<div class="nav-group {{ $active === 'publishers' || request()->routeIs('admin.publishers.*') ? 'has-active' : '' }}">
    <a href="{{ route('admin.publishers.index') }}" class="nav-link {{ $active === 'publishers' || request()->routeIs('admin.publishers.*') ? 'active' : '' }}">
        <span class="nav-icon">🏢</span><span>Publishers</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.publishers.index') }}" class="nav-sub-link {{ request()->routeIs('admin.publishers.index') || ($active === 'publishers' && !request()->routeIs('admin.publishers.create')) ? 'active' : '' }}">All Publishers</a>
        <a href="{{ route('admin.publishers.create') }}" class="nav-sub-link {{ request()->routeIs('admin.publishers.create') ? 'active' : '' }}">Add Publisher</a>
    </div>
</div>

<!-- ⭐ Reviews -->
<div class="nav-group {{ $active === 'reviews' || request()->routeIs('admin.reviews.*') ? 'has-active' : '' }}">
    <a href="{{ route('admin.reviews.index') }}" class="nav-link {{ $active === 'reviews' || request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
        <span class="nav-icon">⭐</span><span>Reviews</span>
    </a>
</div>

<!-- 🎁 Offers & Coupons -->
<div class="nav-group {{ $active === 'coupons' ? 'has-active' : '' }}">
    <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ $active === 'coupons' ? 'active' : '' }}">
        <span class="nav-icon">🎁</span><span>Offers &amp; Coupons</span>
    </a>
</div>

<!-- 📈 Analytics & Reports -->
<div class="nav-group {{ $active === 'analytics' || request()->routeIs('admin.analytics.*') ? 'has-active' : '' }}">
    <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ $active === 'analytics' || request()->routeIs('admin.analytics.*') ? 'active' : '' }}">
        <span class="nav-icon">📈</span><span>Analytics &amp; Reports</span>
    </a>
</div>

<!-- ⚙️ Settings -->
<div class="nav-group {{ $active === 'profile' ? 'has-active' : '' }}">
    <a href="{{ route('admin.payment-settings.edit') }}" class="nav-link {{ $active === 'profile' ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span><span>Settings</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('admin.payment-settings.edit') }}" class="nav-sub-link">General Settings</a>
        <a href="{{ route('admin.profile.edit') }}" class="nav-sub-link {{ $active === 'profile' ? 'active' : '' }}">Admin Profile</a>
        <a href="{{ route('admin.payment-settings.edit') }}" class="nav-sub-link">System Settings</a>
    </div>
</div>

<!-- Help & Support -->
<div class="nav-group {{ $active === 'support' ? 'has-active' : '' }}" style="margin-top: 16px; padding-top: 14px; border-top: 1px solid rgba(255,255,255,0.12);">
    <a href="{{ route('admin.support.index') }}" class="nav-link {{ $active === 'support' ? 'active' : '' }}">
        <span class="nav-icon">❓</span><span>Help &amp; Support</span>
    </a>
</div>

<script>
(function() {
    // 1. Preserve and restore sidebar scroll position
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebar) {
        const savedPos = sessionStorage.getItem('admin_sidebar_scroll');
        if (savedPos !== null) {
            sidebar.scrollTop = parseInt(savedPos, 10);
        }

        sidebar.addEventListener('scroll', function() {
            sessionStorage.setItem('admin_sidebar_scroll', sidebar.scrollTop);
        }, { passive: true });

        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('admin_sidebar_scroll', sidebar.scrollTop);
            });
        });
    }

    // 2. Setup accordion dropdown behavior for parent items
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.admin-sidebar .nav-group').forEach(function(group) {
            const parentLink = group.querySelector(':scope > a.nav-link');
            const subItems = group.querySelector('.nav-sub-items');
            
            if (parentLink && subItems) {
                if (!parentLink.querySelector('.nav-arrow')) {
                    const arrow = document.createElement('span');
                    arrow.className = 'nav-arrow';
                    arrow.innerHTML = '▾';
                    parentLink.appendChild(arrow);
                }
                
                parentLink.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const isOpen = group.classList.contains('is-open');
                    if (isOpen) {
                        group.classList.remove('is-open');
                    } else {
                        group.classList.add('is-open');
                    }
                });
            }
        });

        // 3. Auto-scroll active item into visible view if out of viewport bounds
        if (sidebar) {
            const activeItem = sidebar.querySelector('.nav-sub-link.active, .nav-link.active');
            if (activeItem) {
                const itemRect = activeItem.getBoundingClientRect();
                const sidebarRect = sidebar.getBoundingClientRect();
                if (itemRect.top < sidebarRect.top || itemRect.bottom > sidebarRect.bottom) {
                    activeItem.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            }
        }
    });
})();
</script>