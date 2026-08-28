<footer class="site-footer">
    <div class="container">
        <div class="footer-cta reveal in-view">
            <span class="eyebrow"
                style="background:rgba(255,255,255,0.08);color:var(--accent-gold-light);border-color:rgba(237,161,58,0.3);">Stay
                in the Loop</span>
            <h2>Never miss a <em>new arrival</em></h2>
            <p style="color:#B8C4D6;max-width:480px;margin:0 auto;">Subscribe for new releases, bestseller drops, and
                exclusive coupons.</p>
            <form method="POST" action="{{ route('newsletter.subscribe') }}"
                style="display:flex;gap:10px;max-width:420px;margin:20px auto 0;flex-wrap:wrap;justify-content:center;">
                @csrf
                <input type="email" name="email" required placeholder="you@email.com" class="form-control"
                    style="flex:1;min-width:220px;">
                <button type="submit" class="btn btn-gold">Subscribe</button>
            </form>
        </div>
        <div class="footer-grid">
            <div>
                <a href="{{ route('home') }}" class="brand" style="color:#fff;margin-bottom:16px;"><img
                        src="{{ asset('images/logo-square.jpg') }}" alt="College Street Online"
                        style="height:40px;border-radius:8px;"></a>
                <p style="color:#9AA9C2;max-width:320px;margin-top:16px;">College Street Online brings Kolkata's
                    legendary book market to your doorstep &mdash; academic titles, Bengali literature, and everything
                    in between, from publishers you trust.</p>
            </div>
            <div>
                <h4>Shop</h4>
                <ul style="display:flex;flex-direction:column;gap:10px;">
                    <li><a href="{{ route('books.index') }}">Browse Books</a></li>
                    <li><a href="{{ route('bulk-orders') }}">Bulk Orders</a></li>
                    <li><a href="{{ route('book-rights') }}">Book Rights</a></li>
                    <li><a href="{{ route('cart.index') }}">Cart</a></li>
                </ul>
            </div>
            <div>
                <h4>Company</h4>
                <ul style="display:flex;flex-direction:column;gap:10px;">
                    <li><a href="{{ route('about') }}">About Us</a></li>
                    <li><a href="{{ route('publisher.register') }}">Sell With Us</a></li>
                    <li style="margin-top:6px;"><strong style="color:#fff;">Login Portals</strong></li>
                    <li><a href="{{ route('account.login') }}"
                            style="color:var(--accent-gold-light);font-weight:700;">Customer Login</a></li>
                    <li><a href="{{ route('publisher.login') }}"
                            style="color:var(--accent-gold-light);font-weight:700;">Publisher Login</a></li>
                    <li><a href="{{ route('admin.login') }}"
                            style="color:var(--accent-gold-light);font-weight:700;">Admin Login</a></li>
                </ul>
            </div>
            <div>
                <h4>Get in Touch</h4>
                <ul style="display:flex;flex-direction:column;gap:10px;">
                    <li>College Street, Kolkata, West Bengal, India</li>
                    <li><a href="tel:+919230653975">+91 92306 53975</a></li>
                    <li><a href="mailto:support@collegestreetonline.com">support@collegestreetonline.com</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} College Street Online, a Bengal IT Hub product. All rights reserved.</span>
            <span>Terms &amp; Conditions &middot; Privacy Policy</span>
        </div>
    </div>
</footer>