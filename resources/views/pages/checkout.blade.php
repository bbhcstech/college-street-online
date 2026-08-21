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
                    <div class="summary-line"><span>Subtotal</span><span>&#8377;{{ number_format($quote['subtotal'], 0) }}</span></div>
                    <div class="summary-line"><span>Shipping</span><span>{{ $quote['shipping'] == 0 ? 'Free' : '₹'.number_format($quote['shipping'],0) }}</span></div>
                    <div class="summary-line"><span>Platform Fee</span><span>&#8377;{{ number_format($quote['platformFee'], 0) }}</span></div>
                    @if($quote['discount'] > 0)
                        <div class="summary-line"><span>Discount</span><span>&minus;&#8377;{{ number_format($quote['discount'], 0) }}</span></div>
                    @endif
                    <div class="summary-line total"><span>Total</span><span>&#8377;{{ number_format($quote['total'], 0) }}</span></div>
                    <div class="form-group" style="margin-top:16px;">
                        <label>Coupon Code</label>
                        <div style="display:flex;gap:8px;">
                            <input type="text" name="coupon_code" value="{{ old('coupon_code', $appliedCoupon?->code) }}" class="form-control" placeholder="Enter coupon code">
                            <button type="submit" formaction="{{ route('checkout.coupon') }}" formmethod="POST" formnovalidate class="btn btn-outline">Apply</button>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;margin-top:16px;">Place Order</button>
                    <p style="font-size:0.74rem;color:var(--text-secondary);margin-top:10px;">Your order will show as Pending Payment until our team verifies your UTR.</p>
                </div>
            </div>
        </form>
    </div>
</section>
@endsection
