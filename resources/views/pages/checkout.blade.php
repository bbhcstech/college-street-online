@extends('layouts.app')
@section('title', 'Checkout | College Street Online')
@section('content')
<div class="container" style="padding-top:24px;"><div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><a href="{{ route('cart.index') }}">Cart</a><span class="sep">/</span><span class="current">Checkout</span></div></div>
<section class="section" style="padding-top:0;">
    <div class="container">
        <h1>Checkout</h1>
        <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="grid grid-2" style="grid-template-columns:1.6fr 1fr;align-items:start;gap:32px;">
                <div>
                    <div class="card" style="margin-bottom:20px;">
                        <h3 style="margin-top:0;">Shipping Address</h3>
                        <div class="form-group"><label>Address</label><textarea name="shipping_address" required class="form-control" style="min-height:80px;"></textarea></div>
                        <div class="grid grid-2" style="gap:14px;">
                            <div class="form-group"><label>Phone</label><input type="text" name="shipping_phone" class="form-control"></div>
                            <div class="form-group"><label>Country</label><select name="country" class="form-control"><option value="IN">India</option><option value="BD">Bangladesh</option><option value="GB">United Kingdom</option><option value="US">United States</option></select></div>
                        </div>
                    </div>
                    <div class="card">
                        <h3 style="margin-top:0;">Payment &mdash; Manual Bank Transfer</h3>
                        <p style="font-size:0.88rem;">Transfer the order total, then upload your UTR so our team can verify and confirm your order.</p>
                        <div class="card" style="background-color:var(--surface-alt);padding:16px;"><p style="margin:0;font-size:0.85rem;"><strong>Account Name:</strong> College Street Online Pvt Ltd<br><strong>Account No:</strong> 0123 4567 8901<br><strong>IFSC:</strong> SBIN0001234</p></div>
                        <div class="grid grid-2" style="gap:14px;margin-top:14px;">
                            <div class="form-group"><label>UTR / Reference Number</label><input type="text" name="utr_number" required class="form-control"></div>
                            <div class="form-group"><label>Upload Payment Proof</label><input type="file" name="proof" class="form-control"></div>
                        </div>
                    </div>
                </div>
                <div class="order-summary-card">
                    <h3 style="margin-top:0;">Order Summary</h3>
                    <div class="summary-line"><span>Subtotal</span><span data-checkout-subtotal>&#8377;{{ number_format($quote['subtotal'], 0) }}</span></div>
                    <div class="summary-line"><span>Shipping</span><span data-checkout-shipping>{{ $quote['shipping'] == 0 ? 'Free' : '₹'.number_format($quote['shipping'],0) }}</span></div>
                    <div class="summary-line"><span>Platform Fee</span><span data-checkout-platform-fee>&#8377;{{ number_format($quote['platformFee'], 0) }}</span></div>
                    <div class="summary-line" data-checkout-discount-row style="{{ $quote['discount'] > 0 ? '' : 'display:none;' }}"><span>Discount</span><span data-checkout-discount>&minus;&#8377;{{ number_format($quote['discount'], 0) }}</span></div>
                    <div class="summary-line total"><span>Total</span><span data-checkout-total>&#8377;{{ number_format($quote['total'], 0) }}</span></div>
                    <div class="form-group" style="margin-top:16px;">
                        <label>Coupon Code</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" name="coupon_code" value="{{ old('coupon_code', $appliedCoupon?->code) }}" class="form-control" placeholder="Enter coupon code">
                            <button type="button" class="btn btn-outline" data-apply-coupon data-url="{{ route('checkout.coupon') }}">Apply</button>
                        </div>
                        <small data-coupon-message style="display:block;margin-top:8px;"></small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:16px;">Place Order</button>
                    <p style="font-size:0.74rem;color:var(--text-secondary);margin-top:10px;">Your order will show as Pending Payment until our team verifies your UTR.</p>
                </div>
            </div>
        </form>
    </div>
</section>
<script>
const couponButton = document.querySelector('[data-apply-coupon]');
couponButton?.addEventListener('click', async () => {
    const form = couponButton.closest('form');
    const couponInput = form.querySelector('[name="coupon_code"]');
    const message = form.querySelector('[data-coupon-message]');
    const payload = new FormData();
    payload.append('_token', form.querySelector('[name="_token"]').value);
    payload.append('coupon_code', couponInput.value);
    payload.append('country', form.querySelector('[name="country"]').value);
    couponButton.disabled = true;
    message.textContent = 'Applying coupon...';
    message.style.color = 'var(--text-secondary)';

    try {
        const response = await fetch(couponButton.dataset.url, {
            method: 'POST',
            headers: { 'Accept': 'application/json' },
            body: payload,
        });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message || 'Unable to apply coupon.');

        const money = (value) => `₹${Number(value).toLocaleString('en-IN', { maximumFractionDigits: 0 })}`;
        couponInput.value = data.code;
        form.querySelector('[data-checkout-subtotal]').textContent = money(data.quote.subtotal);
        form.querySelector('[data-checkout-shipping]').textContent = Number(data.quote.shipping) === 0 ? 'Free' : money(data.quote.shipping);
        form.querySelector('[data-checkout-platform-fee]').textContent = money(data.quote.platformFee);
        form.querySelector('[data-checkout-discount]').textContent = `−${money(data.quote.discount)}`;
        form.querySelector('[data-checkout-discount-row]').style.display = data.quote.discount > 0 ? '' : 'none';
        form.querySelector('[data-checkout-total]').textContent = money(data.quote.total);
        message.textContent = data.message;
        message.style.color = 'var(--success)';
    } catch (error) {
        message.textContent = error.message;
        message.style.color = 'var(--danger, #c0392b)';
    } finally {
        couponButton.disabled = false;
    }
});
</script>
@endsection
