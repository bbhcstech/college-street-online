<div class="nav-group"><div class="nav-group-title">Overview</div>
    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ $active === 'dashboard' ? 'active' : '' }}"><span class="nav-icon">&#9635;</span><span>Dashboard</span></a>
    <a href="{{ route('admin.analytics.index') }}" class="nav-link {{ $active === 'analytics' ? 'active' : '' }}"><span class="nav-icon">&#128200;</span><span>Analytics &amp; Reports</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Marketplace</div>
    <a href="{{ route('admin.publishers.index') }}" class="nav-link {{ $active === 'publishers' ? 'active' : '' }}"><span class="nav-icon">&#128100;</span><span>Publishers</span></a>
    <a href="{{ route('admin.books.index') }}" class="nav-link {{ $active === 'books' ? 'active' : '' }}"><span class="nav-icon">&#128214;</span><span>Books</span></a>
    <a href="{{ route('admin.categories.index') }}" class="nav-link {{ $active === 'categories' ? 'active' : '' }}"><span class="nav-icon">&#127991;</span><span>Categories &amp; Authors</span></a>
    <a href="{{ route('admin.coupons.index') }}" class="nav-link {{ $active === 'coupons' ? 'active' : '' }}"><span class="nav-icon">&#127991;</span><span>Coupons &amp; Offers</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Operations</div>
    <a href="{{ route('admin.orders.index') }}" class="nav-link {{ $active === 'orders' ? 'active' : '' }}"><span class="nav-icon">&#128666;</span><span>All Orders</span></a>
    <a href="{{ route('admin.payment-settings.edit') }}" class="nav-link {{ $active === 'payment-settings' ? 'active' : '' }}"><span class="nav-icon">&#9638;</span><span>Payment QR</span></a>
</div>
<div class="nav-group"><div class="nav-group-title">Growth</div>
    <a href="{{ route('admin.newsletter.index') }}" class="nav-link {{ $active === 'newsletter' ? 'active' : '' }}"><span class="nav-icon">&#9993;</span><span>Newsletter</span></a>
</div>
