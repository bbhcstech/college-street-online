@extends('layouts.app')
@section('title', 'Bulk & Institutional Orders | College Street Online')
@section('content')
    <div class="container" style="padding-top:12px;">
        <div class="breadcrumb-row" style="margin-bottom:10px;">
            <a href="{{ route('home') }}">Home</a>
            <span class="sep">/</span>
            <span class="current">Bulk Orders</span>
        </div>
    </div>

    <section class="account-section" style="padding: 0 0 40px 0;">
        <div class="container">
            {{-- Page Header --}}
            <div class="shopping-page-head" style="margin-bottom:20px; padding-bottom:14px; border-bottom:1px solid var(--border, #e2e8f0);">
                <div>
                    <span class="eyebrow" style="margin-bottom:4px;"><span class="dot"></span> Institutions, Libraries &amp; Schools</span>
                    <h1 style="font-size:1.75rem; margin:4px 0 2px 0; color:var(--text-primary);">Bulk &amp; Institutional Orders</h1>
                    <p style="margin:0; font-size:0.86rem; color:var(--text-secondary);">Submit your book requirements for bulk volume discounts, library supply, or institutional quotations.</p>
                </div>
            </div>

            {{-- 2 Column Layout --}}
            <div class="bulk-order-grid">
                {{-- Left: Quotation Form --}}
                <div class="bulk-card-form" style="background:#ffffff; border:1px solid var(--border, #e2e8f0); border-radius:14px; padding:24px; box-shadow:0 4px 16px rgba(0,0,0,0.03);">
                    <div style="display:flex; align-items:center; gap:12px; margin-bottom:20px; padding-bottom:12px; border-bottom:1px solid #f1f5f9;">
                        <div style="width:42px; height:42px; border-radius:10px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 10%, #ffffff); display:grid; place-items:center; font-size:1.25rem; color:var(--brand-primary, #1e3a8a);">
                            📋
                        </div>
                        <div>
                            <h3 style="margin:0; font-size:1.08rem; font-weight:700; color:var(--text-primary);">Request an Official Quotation</h3>
                            <p style="margin:2px 0 0 0; font-size:0.78rem; color:var(--text-secondary);">Fill in your institution details and required book list below.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('bulk-orders.store') }}" id="bulk_order_form">
                        @csrf
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                    Institution / Business Name <span style="color:#ef4444;">*</span>
                                </label>
                                <input name="institution_name" value="{{ old('institution_name') }}" class="form-control" required
                                       placeholder="e.g. St. Xavier's College Library"
                                       style="width:100%; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                    Contact Person <span style="color:#ef4444;">*</span>
                                </label>
                                <input name="contact_name" value="{{ old('contact_name', auth()->user()?->name) }}" class="form-control" required
                                       placeholder="Full name"
                                       style="width:100%; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none;">
                            </div>
                        </div>

                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:14px; margin-bottom:14px;">
                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                    Email Address <span style="color:#ef4444;">*</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email', auth()->user()?->email) }}" class="form-control" required
                                       placeholder="official@institution.edu"
                                       style="width:100%; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none;">
                            </div>
                            <div>
                                <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                    Phone Number <span style="color:#ef4444;">*</span>
                                </label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="phone_code" id="bulk_phone_code" class="form-control" style="max-width: 135px; flex: 0 0 135px; font-size:0.82rem; font-weight: 700; border-radius:8px; border:1px solid var(--border, #cbd5e1); padding:6px 8px;">
                                        <option value="+91" selected>🇮🇳 +91 (IN)</option>
                                        <option value="+1">🇺🇸 +1 (USA)</option>
                                        <option value="+44">🇬🇧 +44 (UK)</option>
                                        <option value="+1">🇨🇦 +1 (Canada)</option>
                                        <option value="+65">🇸🇬 +65 (Singapore)</option>
                                        <option value="+971">🇦🇪 +971 (Dubai)</option>
                                        <option value="+49">🇩🇪 +49 (DE)</option>
                                        <option value="+61">🇦🇺 +61 (AU)</option>
                                        <option value="+33">🇫🇷 +33 (FR)</option>
                                        <option value="+880">🇧🇩 +880 (BD)</option>
                                        <option value="+977">🇳🇵 +977 (NP)</option>
                                        <option value="+94">🇱🇰 +94 (LK)</option>
                                        <option value="+81">🇯🇵 +81 (JP)</option>
                                        <option value="">Other</option>
                                    </select>
                                    <input name="phone_number" value="{{ old('phone_number', old('phone')) }}" class="form-control" placeholder="Mobile number" required style="flex: 1; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px;">
                                </div>
                                <input type="hidden" name="phone" id="bulk_phone_combined">
                            </div>
                        </div>

                        <div style="margin-bottom:14px;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                Books and Quantities Needed <span style="color:#ef4444;">*</span>
                            </label>
                            <textarea name="requirements" class="form-control" rows="5" required
                                      placeholder="List book titles, authors, or ISBNs along with quantities&#10;Example:&#10;1. Organic Chemistry by Morrison — 40 copies&#10;2. Modern Indian History by Bipan Chandra — 25 copies"
                                      style="width:100%; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none; resize:vertical; font-family:inherit;">{{ old('requirements') }}</textarea>
                        </div>

                        <div style="margin-bottom:20px;">
                            <label style="display:block; font-size:0.82rem; font-weight:700; color:var(--text-primary); margin-bottom:6px;">
                                Additional Notes <span style="font-weight:400; color:var(--text-muted);">(Optional)</span>
                            </label>
                            <textarea name="notes" class="form-control" rows="3"
                                      placeholder="Specify delivery deadline, shipping address, or GST invoice requirements..."
                                      style="width:100%; padding:10px 12px; font-size:0.88rem; border:1px solid var(--border, #cbd5e1); border-radius:8px; outline:none; resize:vertical; font-family:inherit;">{{ old('notes') }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; font-size:0.95rem; font-weight:700; border-radius:8px; display:flex; align-items:center; justify-content:center; gap:8px;">
                            <span>🚀</span> Submit Quote Request
                        </button>
                    </form>
                    <script>
                        document.getElementById('bulk_order_form')?.addEventListener('submit', function() {
                            const code = document.getElementById('bulk_phone_code')?.value || '';
                            const num = this.querySelector('[name="phone_number"]')?.value || '';
                            const combined = document.getElementById('bulk_phone_combined');
                            if (combined) {
                                combined.value = code ? `${code} ${num.trim()}` : num.trim();
                            }
                        });
                    </script>
                </div>

                {{-- Right: How It Works & Benefits --}}
                <div class="bulk-card-info" style="display:flex; flex-direction:column; gap:16px;">
                    {{-- How It Works --}}
                    <div style="background:#ffffff; border:1px solid var(--border, #e2e8f0); border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.03);">
                        <h3 style="margin:0 0 14px 0; font-size:1.05rem; font-weight:700; color:var(--text-primary); display:flex; align-items:center; gap:8px;">
                            <span>⚙️</span> How It Works
                        </h3>
                        <div style="display:flex; flex-direction:column; gap:14px;">
                            <div style="display:flex; gap:12px;">
                                <div style="width:28px; height:28px; border-radius:50%; background:var(--brand-primary, #1e3a8a); color:#fff; font-weight:800; font-size:0.8rem; display:grid; place-items:center; flex-shrink:0;">1</div>
                                <div>
                                    <strong style="display:block; font-size:0.86rem; color:var(--text-primary);">Submit Requirements</strong>
                                    <span style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; display:block;">List required titles, ISBNs, and desired quantities.</span>
                                </div>
                            </div>
                            <div style="display:flex; gap:12px;">
                                <div style="width:28px; height:28px; border-radius:50%; background:var(--brand-primary, #1e3a8a); color:#fff; font-weight:800; font-size:0.8rem; display:grid; place-items:center; flex-shrink:0;">2</div>
                                <div>
                                    <strong style="display:block; font-size:0.86rem; color:var(--text-primary);">Publisher Stock Verification</strong>
                                    <span style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; display:block;">We check inventory across 100+ College Street publishers.</span>
                                </div>
                            </div>
                            <div style="display:flex; gap:12px;">
                                <div style="width:28px; height:28px; border-radius:50%; background:var(--brand-primary, #1e3a8a); color:#fff; font-weight:800; font-size:0.8rem; display:grid; place-items:center; flex-shrink:0;">3</div>
                                <div>
                                    <strong style="display:block; font-size:0.86rem; color:var(--text-primary);">Receive Formal Quote</strong>
                                    <span style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; display:block;">Our team sends bulk volume discounts &amp; delivery timeline within 24h.</span>
                                </div>
                            </div>
                            <div style="display:flex; gap:12px;">
                                <div style="width:28px; height:28px; border-radius:50%; background:var(--brand-primary, #1e3a8a); color:#fff; font-weight:800; font-size:0.8rem; display:grid; place-items:center; flex-shrink:0;">4</div>
                                <div>
                                    <strong style="display:block; font-size:0.86rem; color:var(--text-primary);">Direct Campus Dispatch</strong>
                                    <span style="font-size:0.8rem; color:var(--text-secondary); line-height:1.4; display:block;">Confirm quote to initiate tracked dispatch to your institution.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Institutional Guarantee Callout --}}
                    <div style="background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 4%, #ffffff); border:1px solid color-mix(in srgb, var(--brand-primary, #1e3a8a) 18%, #ffffff); border-radius:14px; padding:18px;">
                        <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px; font-weight:700; font-size:0.88rem; color:var(--brand-primary, #1e3a8a);">
                            <span>🛡️</span> Zero Commitment Quote
                        </div>
                        <p style="margin:0; font-size:0.82rem; color:var(--text-secondary); line-height:1.45;">
                            Submitting this form does not charge or commit you to an order. Full GST invoices and official purchase order support provided for institutional billing.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <style>
        .bulk-order-grid {
            display: grid;
            grid-template-columns: 1.35fr 0.65fr;
            gap: 24px;
            align-items: start;
        }
        @media (max-width: 860px) {
            .bulk-order-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection