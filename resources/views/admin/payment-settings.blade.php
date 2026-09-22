@extends('layouts.dashboard', [
    'logoutRoute' => route('admin.logout'),
    'homeRoute' => route('admin.dashboard'),
    'brandLabel' => 'Admin Console',
    'crumb' => 'Operations / Payment Settings'
])

@section('title', 'Payment & Bank Settings')

@section('nav')
    @include('admin.partials.nav', ['active' => 'payment-settings'])
@endsection

@section('content')
    <!-- Active Methods & Bank Details Form -->
    <div class="a-card payment-methods-card" style="margin-bottom:28px;">
        <h3 style="margin-top:0;margin-bottom:18px;font-size:1.15rem;">Enabled Checkout Payment Methods</h3>
        <form method="POST" action="{{ route('admin.payment-settings.bank') }}" id="bank-settings-form">
            @csrf @method('PUT')

            <div class="payment-method-options" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;padding:16px;background:var(--a-bg-surface-alt, #f8fafc);border-radius:10px;margin-bottom:24px;border:1px solid var(--a-border);">
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.92rem;">
                    <input type="checkbox" name="method_upi_qr" value="1" @checked($enabledMethods['upi_qr']) style="width:18px;height:18px;cursor:pointer;">
                    <strong>Enable UPI / QR Code Transfer</strong>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.92rem;">
                    <input type="checkbox" name="method_bank_transfer" value="1" @checked($enabledMethods['bank_transfer']) style="width:18px;height:18px;cursor:pointer;">
                    <strong>Enable Domestic Bank Transfer</strong>
                </label>
                <label style="display:flex;align-items:center;gap:10px;cursor:pointer;font-size:0.92rem;">
                    <input type="checkbox" name="method_intl_wire" value="1" @checked($enabledMethods['international_wire']) style="width:18px;height:18px;cursor:pointer;">
                    <strong>Enable International Wire Transfer</strong>
                </label>
            </div>

            <div class="payment-bank-grid" style="display:grid;grid-template-columns:repeat(auto-fit, minmax(320px, 1fr));gap:24px;">
                <!-- Domestic Bank Details Card -->
                <div class="payment-bank-card" style="border:1px solid var(--a-border);padding:20px;border-radius:12px;background:var(--a-bg-surface, #ffffff);position:relative;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h4 style="margin:0;color:var(--a-brand-primary, #1e293b);font-size:1.05rem;">Domestic Bank Account Details (India)</h4>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleDomesticForm()" id="dom-toggle-btn">Edit Details</button>
                    </div>

                    <!-- Summary Display -->
                    <div id="dom-summary" style="display:block;font-size:0.88rem;line-height:1.7;">
                        @if($upiId || $domesticBank['account_name'] || $domesticBank['account_number'])
                            <div style="margin-bottom:6px;"><strong>UPI ID:</strong> <span style="color:var(--a-brand-primary, #2563eb);">{{ $upiId ?: '—' }}</span></div>
                            <div style="margin-bottom:6px;"><strong>Account Name:</strong> {{ $domesticBank['account_name'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>Account Number:</strong> {{ $domesticBank['account_number'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>Bank Name:</strong> {{ $domesticBank['bank_name'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>IFSC Code:</strong> {{ $domesticBank['ifsc'] ?: '—' }}</div>
                            <div><strong>Branch:</strong> {{ $domesticBank['branch'] ?: '—' }}</div>
                        @else
                            <p style="color:var(--a-text-muted);margin:0;">No domestic bank details configured yet. Click <strong>Edit Details</strong> to add.</p>
                        @endif
                    </div>

                    <!-- Form Inputs (Toggleable) -->
                    <div id="dom-form-inputs" style="display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--a-border);">
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">UPI ID (VPA)</label>
                            <input type="text" name="upi_id" class="a-input" value="{{ old('upi_id', $upiId) }}" placeholder="e.g. collegestreet@upi">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Account Holder Name</label>
                            <input type="text" name="bank_account_name" class="a-input" value="{{ old('bank_account_name', $domesticBank['account_name']) }}" placeholder="e.g. College Street Online Pvt Ltd">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Account Number</label>
                            <input type="text" name="bank_account_number" class="a-input" value="{{ old('bank_account_number', $domesticBank['account_number']) }}" placeholder="e.g. 9180200389100">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Bank Name</label>
                            <input type="text" name="bank_name" class="a-input" value="{{ old('bank_name', $domesticBank['bank_name']) }}" placeholder="e.g. HDFC Bank">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">IFSC Code</label>
                            <input type="text" name="bank_ifsc" class="a-input" value="{{ old('bank_ifsc', $domesticBank['ifsc']) }}" placeholder="e.g. HDFC0001234">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Branch Name</label>
                            <input type="text" name="bank_branch" class="a-input" value="{{ old('bank_branch', $domesticBank['branch']) }}" placeholder="e.g. College Street Branch, Kolkata">
                        </div>
                    </div>
                </div>

                <!-- International Bank Details Card -->
                <div class="payment-bank-card" style="border:1px solid var(--a-border);padding:20px;border-radius:12px;background:var(--a-bg-surface, #ffffff);position:relative;">
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                        <h4 style="margin:0;color:var(--a-brand-primary, #1e293b);font-size:1.05rem;">International Bank Wire Details</h4>
                        <button type="button" class="btn btn-outline btn-sm" onclick="toggleIntlForm()" id="intl-toggle-btn">Edit Details</button>
                    </div>
                    
                    <!-- Summary Display -->
                    <div id="intl-summary" style="display:block;font-size:0.88rem;line-height:1.7;">
                        @if($intlBank['account_name'] || $intlBank['account_number'] || $intlBank['swift_bic'])
                            <div style="margin-bottom:6px;"><strong>Beneficiary Name:</strong> {{ $intlBank['account_name'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>Account / IBAN:</strong> {{ $intlBank['account_number'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>SWIFT / BIC Code:</strong> <span style="color:var(--a-brand-primary, #2563eb);">{{ $intlBank['swift_bic'] ?: '—' }}</span></div>
                            <div style="margin-bottom:6px;"><strong>Bank Name:</strong> {{ $intlBank['bank_name'] ?: '—' }}</div>
                            <div style="margin-bottom:6px;"><strong>Routing / ABA:</strong> {{ $intlBank['routing_number'] ?: '—' }}</div>
                            <div><strong>Bank Address:</strong> {{ $intlBank['bank_address'] ?: '—' }}</div>
                        @else
                            <p style="color:var(--a-text-muted);margin:0;">No international wire details configured yet. Click <strong>Edit Details</strong> to add.</p>
                        @endif
                    </div>

                    <!-- Form Inputs (Toggleable) -->
                    <div id="intl-form-inputs" style="display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--a-border);">
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Beneficiary Account Name</label>
                            <input type="text" name="intl_bank_account_name" class="a-input" value="{{ old('intl_bank_account_name', $intlBank['account_name']) }}" placeholder="e.g. College Street Online Pvt Ltd">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Account / IBAN Number</label>
                            <input type="text" name="intl_bank_account_number" class="a-input" value="{{ old('intl_bank_account_number', $intlBank['account_number']) }}" placeholder="e.g. GB29NWBK60161331926819">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">SWIFT / BIC Code</label>
                            <input type="text" name="intl_bank_swift" class="a-input" value="{{ old('intl_bank_swift', $intlBank['swift_bic']) }}" placeholder="e.g. HDFCINBBXXX">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Bank Name</label>
                            <input type="text" name="intl_bank_name" class="a-input" value="{{ old('intl_bank_name', $intlBank['bank_name']) }}" placeholder="e.g. HDFC Bank International">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Routing / ABA Number (if applicable)</label>
                            <input type="text" name="intl_bank_routing" class="a-input" value="{{ old('intl_bank_routing', $intlBank['routing_number']) }}" placeholder="e.g. 021000021">
                        </div>
                        <div style="margin-bottom:14px;">
                            <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Bank Address</label>
                            <input type="text" name="intl_bank_address" class="a-input" value="{{ old('intl_bank_address', $intlBank['bank_address']) }}" placeholder="e.g. Kolkata, West Bengal, India">
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary payment-bank-save" style="margin-top:24px;">Save Payment Instructions &amp; Bank Details</button>
        </form>
    </div>

    <div class="payment-settings-grid">
        <section class="a-card payment-upload-card">
            <div class="payment-card-head"><span>1</span>
                <div>
                    <h3>Upload replacement QR</h3>
                    <p>The new image becomes visible at checkout immediately.</p>
                </div>
            </div>
            <form method="POST" action="{{ route('admin.payment-settings.update') }}" enctype="multipart/form-data" data-payment-qr-form>
                @csrf @method('PUT')
                <label for="payment-qr" class="payment-drop-zone" data-payment-drop>
                    <div class="payment-upload-icon">▣</div>
                    <strong>Choose a QR image</strong>
                    <span>PNG, JPG or WebP · Maximum 3 MB</span>
                    <input id="payment-qr" type="file" name="payment_qr" accept="image/png,image/jpeg,image/webp" required data-payment-input>
                </label>
                <div class="payment-file-preview" data-payment-preview hidden>
                    <img alt="Selected QR preview" data-payment-preview-image>
                    <div><strong data-payment-file-name></strong><span>Ready to upload</span></div>
                    <button type="button" class="btn btn-outline btn-sm" data-payment-clear>Change</button>
                </div>
                <div class="payment-warning">
                    <strong>Before replacing</strong>
                    <p>Confirm the QR belongs to the correct payment account.</p>
                </div>
                <button class="btn btn-primary payment-submit" data-payment-submit disabled>{{ $qrUrl ? 'Replace payment QR' : 'Upload payment QR' }}</button>
            </form>
        </section>

        <section class="a-card payment-preview-card">
            <div class="payment-card-head"><span>2</span>
                <div>
                    <h3>Current customer preview</h3>
                    <p>This is the image shown on the checkout page.</p>
                </div>
            </div>
            @if($qrUrl)
                <div class="payment-qr-frame"><img src="{{ $qrUrl }}" alt="Current checkout payment QR code"></div>
                <div class="payment-current-meta">
                    <div><span>Status</span><strong class="payment-status-active">Active</strong></div>
                    <div><span>Last updated</span><strong>{{ $qr?->updated_at?->format('d M Y, h:i A') }}</strong></div>
                </div>
            @else
                <div class="payment-empty"><span>▦</span>
                    <h4>No payment QR configured</h4>
                    <p>Upload a QR image so customers can complete checkout payments.</p>
                </div>
            @endif
        </section>
    </div>

    <div class="payment-bottom-grid">
    <section class="a-card payment-commission-card">
        <div class="payment-card-head"><span>%</span>
            <div>
                <h3>Publisher deduction</h3>
                <p>Set the commission deducted when an administrator verifies a customer payment.</p>
            </div>
        </div>
        <form method="POST" action="{{ route('admin.payment-settings.commission') }}">
            @csrf @method('PUT')
            <div style="margin-bottom:14px;">
                <label for="publisher-commission" style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Commission percentage</label>
                <input id="publisher-commission" class="a-input" type="number" name="publisher_commission_rate" value="{{ old('publisher_commission_rate', $commissionRate) }}" min="0" max="100" step="0.01" required>
                <small style="display:block;margin-top:7px;color:var(--a-text-muted)">Example: 10% means the publisher receives 90%.</small>
            </div>
            <button class="btn btn-primary">Save deduction rate</button>
        </form>
    </section>
    <section class="a-card payment-instructions-card">
        <div class="payment-card-head"><span>i</span>
            <div><h3>Payment instructions</h3><p>Guidance shown throughout the payment workflow.</p></div>
        </div>
        <ol>
            <li>Choose an enabled payment method at checkout.</li>
            <li>Complete payment using the displayed QR or bank details.</li>
            <li>Enter the exact amount and keep the transaction reference.</li>
            <li>Upload proof of payment for administrator verification.</li>
        </ol>
    </section>
    </div>

    <script>
        function toggleDomesticForm() {
            const inputs = document.getElementById('dom-form-inputs');
            const summary = document.getElementById('dom-summary');
            const btn = document.getElementById('dom-toggle-btn');
            if (inputs.style.display === 'none') {
                inputs.style.display = 'block';
                summary.style.display = 'none';
                btn.innerText = 'Close Form';
            } else {
                inputs.style.display = 'none';
                summary.style.display = 'block';
                btn.innerText = 'Edit Details';
            }
        }

        function toggleIntlForm() {
            const inputs = document.getElementById('intl-form-inputs');
            const summary = document.getElementById('intl-summary');
            const btn = document.getElementById('intl-toggle-btn');
            if (inputs.style.display === 'none') {
                inputs.style.display = 'block';
                summary.style.display = 'none';
                btn.innerText = 'Close Form';
            } else {
                inputs.style.display = 'none';
                summary.style.display = 'block';
                btn.innerText = 'Edit Details';
            }
        }

        (() => {
            const form = document.querySelector('[data-payment-qr-form]'),
                  input = form?.querySelector('[data-payment-input]'),
                  drop = form?.querySelector('[data-payment-drop]'),
                  preview = form?.querySelector('[data-payment-preview]'),
                  image = form?.querySelector('[data-payment-preview-image]'),
                  name = form?.querySelector('[data-payment-file-name]'),
                  submit = form?.querySelector('[data-payment-submit]'),
                  clear = form?.querySelector('[data-payment-clear]');
            input?.addEventListener('change', () => {
                const file = input.files[0];
                if (!file) return;
                image.src = URL.createObjectURL(file);
                name.textContent = file.name;
                drop.hidden = true;
                preview.hidden = false;
                submit.disabled = false;
            });
            clear?.addEventListener('click', () => {
                input.value = '';
                preview.hidden = true;
                drop.hidden = false;
                submit.disabled = true;
            });
        })();
    </script>
@endsection
