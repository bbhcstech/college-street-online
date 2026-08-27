@extends('layouts.app')
@section('title', 'Checkout | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><a
                href="{{ route('cart.index') }}">Cart</a><span class="sep">/</span><span class="current">Checkout</span>
        </div>
    </div>
    <section class="section" style="padding-top:0;">
        <div class="container">
            <h1>Checkout</h1>
            <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">@csrf
                <div class="grid grid-2" style="grid-template-columns:1.6fr 1fr;align-items:start;gap:32px;">
                    <div>
                        <div class="card" style="margin-bottom:20px;">
                            <h3 style="margin-top:0;">Shipping Address</h3>
                            <div class="form-group"><label>Address</label><textarea name="shipping_address" required
                                    class="form-control" style="min-height:80px;">{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="grid grid-2" style="gap:14px;">
                                <div class="form-group"><label>Phone</label><input name="shipping_phone"
                                        value="{{ old('shipping_phone') }}" class="form-control"></div>
                                <div class="form-group"><label>Country</label><select name="country" class="form-control"
                                        data-country data-quote-url="{{ route('checkout.quote') }}">
                                        <option value="IN">India — INR (₹)</option>
                                        <option value="BD">Bangladesh — BDT (৳)</option>
                                        <option value="GB">United Kingdom — GBP (£)</option>
                                        <option value="US">United States — USD ($)</option>
                                    </select></div>
                            </div>
                        </div>
                        <div class="card">
                            <h3 style="margin-top:0;">Payment — Manual Bank Transfer</h3>
                            <p style="font-size:.88rem;">Transfer the order total, then enter your UTR so our team can
                                verify and confirm your order.</p>
                            @if($paymentQrUrl)
                                <div
                                    style="display:flex;align-items:center;gap:20px;padding:16px;margin-bottom:14px;border:1px solid var(--border);border-radius:14px;background:var(--surface-alt);">
                                    <img src="{{ $paymentQrUrl }}" alt="Scan to pay"
                                        style="width:160px;height:160px;object-fit:contain;padding:8px;background:#fff;border-radius:10px;">
                                    <div><strong>Scan to pay</strong>
                                        <p style="margin:7px 0 0;font-size:.82rem;color:var(--text-secondary);">Pay the exact
                                            total shown, then enter the UTR and upload proof.</p>
                                    </div>
                            </div>@else<div class="alert alert-danger">QR payment is currently unavailable. Use the bank
                            details below.</div>@endif
                            <div class="card" style="background:var(--surface-alt);padding:16px;">
                                <p style="margin:0;font-size:.85rem;"><strong>Account Name:</strong> College Street Online
                                    Pvt Ltd<br><strong>Account No:</strong> 0123 4567 8901<br><strong>IFSC:</strong>
                                    SBIN0001234</p>
                            </div>
                            <div class="grid grid-2" style="gap:14px;margin-top:14px;">
                                <div class="form-group"><label>UTR / Reference Number</label><input name="utr_number"
                                        required class="form-control"></div>
                                <div class="form-group"><label>Upload Payment Proof</label><input type="file" name="proof"
                                        class="form-control"></div>
                            </div>
                        </div>
                    </div>
                    <div class="order-summary-card">
                        <h3 style="margin-top:0;">Order Summary</h3>
                        <div class="summary-line"><span>Subtotal</span><span
                                data-checkout-subtotal>{{ $quote['symbol'] }}{{ number_format($quote['subtotal'], 2) }}</span>
                        </div>
                        <div class="summary-line"><span>Shipping</span><span
                                data-checkout-shipping>{{ $quote['shipping'] == 0 ? 'Free' : $quote['symbol'] . number_format($quote['shipping'], 2) }}</span>
                        </div>
                        <div class="summary-line"><span>Platform Fee</span><span
                                data-checkout-platform-fee>{{ $quote['symbol'] }}{{ number_format($quote['platformFee'], 2) }}</span>
                        </div>
                        <div class="summary-line" data-checkout-discount-row
                            style="{{ $quote['discount'] > 0 ? '' : 'display:none;' }}"><span>Discount</span><span
                                data-checkout-discount>−{{ $quote['symbol'] }}{{ number_format($quote['discount'], 2) }}</span>
                        </div>
                        <div class="summary-line total"><span>Total (<span
                                    data-checkout-currency>{{ $quote['currency'] }}</span>)</span><span
                                data-checkout-total>{{ $quote['symbol'] }}{{ number_format($quote['total'], 2) }}</span>
                        </div>
                        <div class="form-group" style="margin-top:16px;"><label>Coupon Code</label>
                            <div style="display:flex;gap:8px;"><input name="coupon_code"
                                    value="{{ old('coupon_code', $appliedCoupon?->code) }}" class="form-control"
                                    placeholder="Enter coupon code"><button type="button" class="btn btn-outline"
                                    data-apply-coupon data-url="{{ route('checkout.coupon') }}">Apply</button></div><small
                                data-coupon-message style="display:block;margin-top:8px;"></small>
                        </div><button class="btn btn-primary" style="width:100%;margin-top:16px;">Place Order</button>
                        <p style="font-size:.74rem;color:var(--text-secondary);">Your order stays Pending Payment until the
                            UTR is verified.</p>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script>
        const couponButton = document.querySelector('[data-apply-coupon]'), form = couponButton.closest('form'); let activeQuote = @json($quote);
        const money = (value, q = activeQuote) => `${q.symbol}${Number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const renderQuote = q => { activeQuote = q; form.querySelector('[data-checkout-subtotal]').textContent = money(q.subtotal, q); form.querySelector('[data-checkout-shipping]').textContent = Number(q.shipping) === 0 ? 'Free' : money(q.shipping, q); form.querySelector('[data-checkout-platform-fee]').textContent = money(q.platformFee, q); form.querySelector('[data-checkout-discount]').textContent = `−${money(q.discount, q)}`; form.querySelector('[data-checkout-discount-row]').style.display = q.discount > 0 ? '' : 'none'; form.querySelector('[data-checkout-total]').textContent = money(q.total, q); form.querySelector('[data-checkout-currency]').textContent = q.currency; };
        document.querySelector('[data-country]').addEventListener('change', async e => { const p = new FormData(); p.append('_token', form.querySelector('[name="_token"]').value); p.append('country', e.target.value); const r = await fetch(e.target.dataset.quoteUrl, { method: 'POST', headers: { Accept: 'application/json' }, body: p }); if (r.ok) renderQuote((await r.json()).quote); });
        couponButton.addEventListener('click', async () => { const input = form.querySelector('[name="coupon_code"]'), message = form.querySelector('[data-coupon-message]'), p = new FormData(); p.append('_token', form.querySelector('[name="_token"]').value); p.append('coupon_code', input.value); p.append('country', form.querySelector('[name="country"]').value); couponButton.disabled = true; message.textContent = 'Applying coupon...'; try { const r = await fetch(couponButton.dataset.url, { method: 'POST', headers: { Accept: 'application/json' }, body: p }), data = await r.json(); if (!r.ok) throw new Error(data.message || 'Unable to apply coupon.'); input.value = data.code; renderQuote(data.quote); message.textContent = data.message; message.style.color = 'var(--success)'; } catch (e) { message.textContent = e.message; message.style.color = 'var(--danger,#c0392b)'; } finally { couponButton.disabled = false; } });
    </script>
@endsection