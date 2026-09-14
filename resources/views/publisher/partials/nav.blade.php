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
    <a href="{{ route('publisher.dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}">
        <span class="nav-icon">📊</span><span>Dashboard</span>
    </a>
</div>

<!-- 📖 My Books -->
@php
    $isBooksActive = in_array($active, ['books', 'books_create']) || (request()->routeIs('publisher.books.*') && !request()->routeIs('publisher.inventory.*'));
@endphp
<div class="nav-group {{ $isBooksActive ? 'has-active' : '' }}">
    <a href="{{ route('publisher.books.index') }}" class="nav-link {{ $isBooksActive ? 'active' : '' }}">
        <span class="nav-icon">📖</span><span>My Books</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('publisher.books.index') }}" class="nav-sub-link {{ (request()->routeIs('publisher.books.index') || $active === 'books') && !request()->routeIs('publisher.books.create') && $active !== 'books_create' ? 'active' : '' }}">All Books</a>
        <a href="{{ route('publisher.books.create') }}" class="nav-sub-link {{ request()->routeIs('publisher.books.create') || $active === 'books_create' ? 'active' : '' }}">Add New Book</a>
    </div>
</div>

<!-- 📦 Inventory -->
<div class="nav-group {{ $active === 'inventory' || request()->routeIs('publisher.inventory.*') ? 'has-active' : '' }}">
    <a href="{{ route('publisher.inventory.index') }}" class="nav-link {{ $active === 'inventory' || request()->routeIs('publisher.inventory.*') ? 'active' : '' }}">
        <span class="nav-icon">📦</span><span>Inventory</span>
    </a>
</div>

<!-- 🚚 Orders -->
@php
    $isOrdersActive = $active === 'orders' || request()->routeIs('publisher.orders.*');
    $currentStatus = request('status');
@endphp
<div class="nav-group {{ $isOrdersActive ? 'has-active' : '' }}">
    <a href="{{ route('publisher.orders.index') }}" class="nav-link {{ $isOrdersActive ? 'active' : '' }}">
        <span class="nav-icon">🚚</span><span>Orders</span>
    </a>
    <div class="nav-sub-items">
        <a href="{{ route('publisher.orders.index') }}" class="nav-sub-link {{ $isOrdersActive && empty($currentStatus) ? 'active' : '' }}">All Orders</a>
        <a href="{{ route('publisher.orders.index', ['status' => 'processing']) }}" class="nav-sub-link {{ $isOrdersActive && $currentStatus === 'processing' ? 'active' : '' }}">Processing</a>
        <a href="{{ route('publisher.orders.index', ['status' => 'shipped']) }}" class="nav-sub-link {{ $isOrdersActive && $currentStatus === 'shipped' ? 'active' : '' }}">Shipped</a>
        <a href="{{ route('publisher.orders.index', ['status' => 'delivered']) }}" class="nav-sub-link {{ $isOrdersActive && $currentStatus === 'delivered' ? 'active' : '' }}">Delivered</a>
    </div>
</div>

<!-- 💳 Sales / Earnings -->
<div class="nav-group {{ $active === 'payments' || request()->routeIs('publisher.payments.*') ? 'has-active' : '' }}">
    <a href="{{ route('publisher.payments.index') }}" class="nav-link {{ $active === 'payments' || request()->routeIs('publisher.payments.*') ? 'active' : '' }}">
        <span class="nav-icon">💳</span><span>Sales / Earnings</span>
    </a>
</div>

<!-- 📈 Reports -->
<div class="nav-group {{ $active === 'analytics' || request()->routeIs('publisher.analytics.*') ? 'has-active' : '' }}">
    <a href="{{ route('publisher.analytics.index') }}" class="nav-link {{ $active === 'analytics' || request()->routeIs('publisher.analytics.*') ? 'active' : '' }}">
        <span class="nav-icon">📈</span><span>Reports</span>
    </a>
</div>

<!-- ⚙️ Profile / Settings -->
<div class="nav-group {{ $active === 'profile' || request()->routeIs('publisher.profile.*') ? 'has-active' : '' }}">
    <a href="{{ route('publisher.profile.edit') }}" class="nav-link {{ $active === 'profile' || request()->routeIs('publisher.profile.*') ? 'active' : '' }}">
        <span class="nav-icon">⚙️</span><span>Profile / Settings</span>
    </a>
</div>

<script>
(function() {
    const sidebar = document.querySelector('.admin-sidebar');
    if (sidebar) {
        const savedPos = sessionStorage.getItem('publisher_sidebar_scroll');
        if (savedPos !== null) {
            sidebar.scrollTop = parseInt(savedPos, 10);
        }

        sidebar.addEventListener('scroll', function() {
            sessionStorage.setItem('publisher_sidebar_scroll', sidebar.scrollTop);
        }, { passive: true });

        sidebar.querySelectorAll('a').forEach(function(link) {
            link.addEventListener('click', function() {
                sessionStorage.setItem('publisher_sidebar_scroll', sidebar.scrollTop);
            });
        });
    }

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
