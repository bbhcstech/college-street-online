@extends('layouts.app')
@section('title', 'Checkout | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <a href="{{ route('cart.index') }}">Cart</a>
            <span class="sep">/</span>
            <span class="current">Checkout</span>
        </div>
    </div>
    <section class="section shopping-section">
        <div class="container">
            <div class="shopping-page-head">
                <div>
                    <span class="eyebrow"><span class="dot"></span> Final step</span>
                    <h1>Secure Checkout</h1>
                    <p>Confirm delivery address, payment method, and complete your order.</p>
                </div>
                <div class="checkout-progress">
                    <span class="done">1</span><i></i><span class="current">2</span>
                    <small>Cart</small><small>Checkout</small>
                </div>
            </div>

            <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-2" style="grid-template-columns:1.6fr 1fr;align-items:start;gap:32px;">
                    <div>
                        <!-- Shipping Address Card -->
                        <div class="card checkout-card" style="margin-bottom:20px;">
                            <div class="checkout-card-head">
                                <span>1</span>
                                <div>
                                    <h3>Shipping Address &amp; Destination Country</h3>
                                    <p>Where should we deliver your books?</p>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Destination Country</label>
                                <select name="country" class="form-control" data-country data-quote-url="{{ route('checkout.quote') }}">
                                    @foreach($countries as $cnt)
                                        <option value="{{ $cnt->code }}" @selected($cnt->code === $selectedCountry->code)>
                                            {{ $cnt->name }} ({{ $cnt->symbol }} {{ $cnt->currency_code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Full Shipping Address</label>
                                <textarea name="shipping_address" required class="form-control" style="min-height:85px;" placeholder="House/Flat No, Street, City, State/Province, Postal Code">{{ old('shipping_address') }}</textarea>
                            </div>
                            <div class="form-group">
                                <label>Contact Phone Number</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="phone_code" id="phone_code_select" class="form-control" style="max-width: 145px; flex: 0 0 145px; font-weight: 700;">
                                        <option value="+91" @selected($selectedCountry->code === 'IN')>🇮🇳 +91 (IN)</option>
                                        <option value="+1" @selected($selectedCountry->code === 'US')>🇺🇸 +1 (USA)</option>
                                        <option value="+44" @selected($selectedCountry->code === 'GB')>🇬🇧 +44 (UK)</option>
                                        <option value="+1" @selected($selectedCountry->code === 'CA')>🇨🇦 +1 (Canada)</option>
                                        <option value="+65" @selected($selectedCountry->code === 'SG')>🇸🇬 +65 (Singapore)</option>
                                        <option value="+971" @selected($selectedCountry->code === 'AE')>🇦🇪 +971 (Dubai/UAE)</option>
                                        <option value="+49" @selected($selectedCountry->code === 'DE')>🇩🇪 +49 (Germany)</option>
                                        <option value="+61" @selected($selectedCountry->code === 'AU')>🇦🇺 +61 (Australia)</option>
                                        <option value="+33" @selected($selectedCountry->code === 'FR')>🇫🇷 +33 (France)</option>
                                        <option value="+880" @selected($selectedCountry->code === 'BD')>🇧🇩 +880 (Bangladesh)</option>
                                        <option value="+977" @selected($selectedCountry->code === 'NP')>🇳🇵 +977 (Nepal)</option>
                                        <option value="+94" @selected($selectedCountry->code === 'LK')>🇱🇰 +94 (Sri Lanka)</option>
                                        <option value="+81" @selected($selectedCountry->code === 'JP')>🇯🇵 +81 (Japan)</option>
                                        <option value="">Other</option>
                                    </select>
                                    <input name="shipping_phone_number" value="{{ old('shipping_phone_number', old('shipping_phone')) }}" class="form-control" placeholder="10-digit mobile number" required style="flex: 1;">
                                </div>
                                <input type="hidden" name="shipping_phone" id="shipping_phone_combined">
                            </div>
                        </div>

                        <!-- Payment Method Card -->
                        <div class="card checkout-card">
                            <div class="checkout-card-head">
                                <span>2</span>
                                <div>
                                    <h3>Select Payment Method &amp; Submit Proof</h3>
                                    <p>Choose your preferred payment option, complete the transfer, and upload your payment receipt.</p>
                                </div>
                            </div>

                            <div class="payment-method-selector" style="display:flex;flex-direction:column;gap:12px;margin-bottom:20px;">
                                @if($enabledMethods['upi_qr'])
                                    <label class="payment-option-label" style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;">
                                        <input type="radio" name="payment_method" value="upi_qr" checked onclick="switchPaymentTab('upi_qr')">
                                        <div>
                                            <strong>UPI / QR Code Transfer</strong>
                                            <small style="display:block;color:var(--text-secondary);">Scan QR or use UPI ID for instant transfer</small>
                                        </div>
                                    </label>
                                @endif

                                @if($enabledMethods['bank_transfer'])
                                    <label class="payment-option-label" style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;">
                                        <input type="radio" name="payment_method" value="bank_transfer" @if(!$enabledMethods['upi_qr']) checked @endif onclick="switchPaymentTab('bank_transfer')">
                                        <div>
                                            <strong>Domestic Bank Transfer (NEFT/RTGS/IMPS)</strong>
                                            <small style="display:block;color:var(--text-secondary);">Direct bank transfer to Indian bank account</small>
                                        </div>
                                    </label>
                                @endif

                                @if($enabledMethods['international_wire'])
                                    <label class="payment-option-label" style="display:flex;align-items:center;gap:12px;padding:14px;border:1.5px solid var(--border);border-radius:10px;cursor:pointer;">
                                        <input type="radio" name="payment_method" value="international_wire" @if(!$enabledMethods['upi_qr'] && !$enabledMethods['bank_transfer']) checked @endif onclick="switchPaymentTab('international_wire')">
                                        <div>
                                            <strong>International Wire Transfer (SWIFT / IBAN)</strong>
                                            <small style="display:block;color:var(--text-secondary);">Wire transfer for international orders</small>
                                        </div>
                                    </label>
                                @endif
                            </div>

                            <!-- UPI / QR Payment Tab -->
                            @if($enabledMethods['upi_qr'])
                                <div id="tab-upi_qr" class="payment-tab-content" style="padding:16px;background:var(--surface-alt);border-radius:12px;margin-bottom:20px;">
                                    @if($paymentQrUrl)
                                        <div style="display:flex;align-items:center;gap:20px;flex-wrap:wrap;">
                                            <img src="{{ $paymentQrUrl }}" alt="Scan to pay" style="width:160px;height:160px;object-fit:contain;padding:8px;background:#fff;border-radius:10px;border:1px solid var(--border);">
                                            <div>
                                                <strong style="font-size:1.05rem;">Scan to Pay via Any UPI App</strong>
                                                @if($upiId)
                                                    <p style="margin:6px 0;font-size:0.9rem;"><strong>UPI ID:</strong> <code>{{ $upiId }}</code></p>
                                                @endif
                                                <p style="margin:4px 0 0;font-size:.82rem;color:var(--text-secondary);">Scan the QR code, complete payment for the exact order total, then enter the UTR &amp; upload proof below.</p>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-info" style="margin:0;">QR payment instructions are set up. Please transfer using the UPI ID: <strong>{{ $upiId ?: 'collegestreet@upi' }}</strong>.</div>
                                    @endif
                                </div>
                            @endif

                            <!-- Domestic Bank Transfer Tab -->
                            @if($enabledMethods['bank_transfer'])
                                <div id="tab-bank_transfer" class="payment-tab-content" style="display:none;padding:16px;background:var(--surface-alt);border-radius:12px;margin-bottom:20px;">
                                    <h4 style="margin:0 0 10px;color:var(--brand-primary);">Bank Account Details</h4>
                                    <div style="font-size:0.9rem;line-height:1.7;">
                                        <div><strong>Beneficiary:</strong> {{ $domesticBank['account_name'] ?: 'College Street Online Pvt Ltd' }}</div>
                                        <div><strong>Account Number:</strong> <code>{{ $domesticBank['account_number'] ?: '0123 4567 8901' }}</code></div>
                                        <div><strong>Bank Name:</strong> {{ $domesticBank['bank_name'] ?: 'HDFC Bank' }}</div>
                                        <div><strong>IFSC Code:</strong> <code>{{ $domesticBank['ifsc'] ?: 'HDFC0001234' }}</code></div>
                                        <div><strong>Branch:</strong> {{ $domesticBank['branch'] ?: 'College Street, Kolkata' }}</div>
                                    </div>
                                </div>
                            @endif

                            <!-- International Wire Transfer Tab -->
                            @if($enabledMethods['international_wire'])
                                <div id="tab-international_wire" class="payment-tab-content" style="display:none;padding:16px;background:var(--surface-alt);border-radius:12px;margin-bottom:20px;">
                                    <h4 style="margin:0 0 10px;color:var(--brand-primary);">International Wire Transfer Details</h4>
                                    <div style="font-size:0.9rem;line-height:1.7;">
                                        <div><strong>Beneficiary Name:</strong> {{ $intlBank['account_name'] ?: 'College Street Online Pvt Ltd' }}</div>
                                        <div><strong>Account / IBAN:</strong> <code>{{ $intlBank['account_number'] ?: 'GB29NWBK60161331926819' }}</code></div>
                                        <div><strong>SWIFT / BIC Code:</strong> <code>{{ $intlBank['swift_bic'] ?: 'HDFCINBBXXX' }}</code></div>
                                        <div><strong>Bank Name:</strong> {{ $intlBank['bank_name'] ?: 'HDFC Bank International' }}</div>
                                        @if($intlBank['routing_number'])
                                            <div><strong>Routing Number:</strong> {{ $intlBank['routing_number'] }}</div>
                                        @endif
                                        @if($intlBank['bank_address'])
                                            <div><strong>Bank Address:</strong> {{ $intlBank['bank_address'] }}</div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- UTR / Screenshot Upload Section -->
                            <div class="grid grid-2" style="gap:16px;">
                                <div class="form-group">
                                    <label>Transaction ID / UTR / Reference No.</label>
                                    <input name="utr_number" required class="form-control" placeholder="e.g. 425612349012" value="{{ old('utr_number') }}">
                                </div>
                                <div class="form-group">
                                    <label>Upload Payment Proof Screenshot</label>
                                    <input type="file" name="proof" class="form-control" accept="image/png,image/jpeg,image/webp,application/pdf" required>
                                    <small style="display:block;margin-top:4px;color:var(--text-secondary);font-size:0.75rem;">JPG, PNG, WebP or PDF max 5 MB.</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Summary Card -->
                    <div class="order-summary-card checkout-summary">
                        <div class="summary-heading"><span>Order summary</span><small>Review totals</small></div>
                        <div class="summary-line"><span>Subtotal</span><span data-checkout-subtotal>{{ $quote['symbol'] }}{{ number_format($quote['subtotal'], 2) }}</span></div>
                        <div class="summary-line"><span>Shipping</span><span data-checkout-shipping>{{ $quote['shipping'] == 0 ? 'Free' : $quote['symbol'] . number_format($quote['shipping'], 2) }}</span></div>
                        <div class="summary-line" data-checkout-discount-row style="{{ $quote['discount'] > 0 ? '' : 'display:none;' }}"><span>Discount</span><span data-checkout-discount>−{{ $quote['symbol'] }}{{ number_format($quote['discount'], 2) }}</span></div>
                        <div class="summary-line total">
                            <span>Total (<span data-checkout-currency>{{ $quote['currency'] }}</span>)</span>
                            <span data-checkout-total>{{ $quote['symbol'] }}{{ number_format($quote['total'], 2) }}</span>
                        </div>

                        <div class="form-group" style="margin-top:16px;">
                            <label>Coupon Code</label>
                            <div style="display:flex;gap:8px;">
                                <input name="coupon_code" value="{{ old('coupon_code', $appliedCoupon?->code) }}" class="form-control" placeholder="Enter coupon code">
                                <button type="button" class="btn btn-outline" data-apply-coupon data-url="{{ route('checkout.coupon') }}">Apply</button>
                            </div>
                            <small data-coupon-message style="display:block;margin-top:8px;"></small>
                        </div>

                        <button class="btn btn-primary" style="width:100%;margin-top:16px;">Place Order</button>
                        <div class="summary-assurance">&#128274; Price snapshot saved upon placement</div>
                        <p style="font-size:.74rem;color:var(--text-secondary);margin-top:8px;">Your order will be verified by admin after submitting the transaction reference.</p>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <script>
        function switchPaymentTab(method) {
            document.querySelectorAll('.payment-tab-content').forEach(el => el.style.display = 'none');
            const target = document.getElementById('tab-' + method);
            if (target) target.style.display = 'block';
        }

        const couponButton = document.querySelector('[data-apply-coupon]'), form = couponButton.closest('form');
        let activeQuote = @json($quote);

        const money = (value, q = activeQuote) => `${q.symbol}${Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        const renderQuote = q => {
            activeQuote = q;
            form.querySelector('[data-checkout-subtotal]').textContent = money(q.subtotal, q);
            form.querySelector('[data-checkout-shipping]').textContent = Number(q.shipping) === 0 ? 'Free' : money(q.shipping, q);
            form.querySelector('[data-checkout-discount]').textContent = `−${money(q.discount, q)}`;
            form.querySelector('[data-checkout-discount-row]').style.display = q.discount > 0 ? '' : 'none';
            form.querySelector('[data-checkout-total]').textContent = money(q.total, q);
            form.querySelector('[data-checkout-currency]').textContent = q.currency;
        };

        const countryDialMap = {
            'IN': '+91', 'US': '+1', 'GB': '+44', 'CA': '+1', 'SG': '+65',
            'AE': '+971', 'DE': '+49', 'AU': '+61', 'FR': '+33', 'BD': '+880',
            'NP': '+977', 'LK': '+94', 'JP': '+81'
        };

        document.querySelector('[data-country]')?.addEventListener('change', async e => {
            const code = e.target.value;
            const phoneSelect = document.getElementById('phone_code_select');
            if (phoneSelect && countryDialMap[code]) {
                phoneSelect.value = countryDialMap[code];
            }
            const p = new FormData();
            p.append('_token', form.querySelector('[name="_token"]').value);
            p.append('country', code);
            const r = await fetch(e.target.dataset.quoteUrl, { method: 'POST', headers: { Accept: 'application/json' }, body: p });
            if (r.ok) renderQuote((await r.json()).quote);
        });

        form.addEventListener('submit', () => {
            const code = document.getElementById('phone_code_select')?.value || '';
            const num = form.querySelector('[name="shipping_phone_number"]')?.value || '';
            const combined = document.getElementById('shipping_phone_combined');
            if (combined) {
                combined.value = code ? `${code} ${num.trim()}` : num.trim();
            }
        });

        couponButton.addEventListener('click', async () => {
            const input = form.querySelector('[name="coupon_code"]'), message = form.querySelector('[data-coupon-message]'), p = new FormData();
            p.append('_token', form.querySelector('[name="_token"]').value);
            p.append('coupon_code', input.value);
            p.append('country', form.querySelector('[name="country"]').value);
            couponButton.disabled = true;
            message.textContent = 'Applying coupon...';
            try {
                const r = await fetch(couponButton.dataset.url, { method: 'POST', headers: { Accept: 'application/json' }, body: p }), data = await r.json();
                if (!r.ok) throw new Error(data.message || 'Unable to apply coupon.');
                input.value = data.code;
                renderQuote(data.quote);
                message.textContent = data.message;
                message.style.color = 'var(--success)';
            } catch (e) {
                message.textContent = e.message;
                message.style.color = 'var(--danger,#c0392b)';
            } finally {
                couponButton.disabled = false;
            }
        });
    </script>
@endsection