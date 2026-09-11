@extends('layouts.app')
@section('title', 'Bulk Orders | College Street Online')
@section('content')
    <div class="container" style="padding-top:24px;">
        <div class="breadcrumb-row"><a href="{{ route('home') }}">Home</a><span class="sep">/</span><span
                class="current">Bulk Orders</span></div>
    </div>
    <section class="page-hero">
        <div class="container"><span class="eyebrow"><span class="dot"></span> Institutions &amp; Libraries</span>
            <h1>Bulk &amp; Institutional Orders</h1>
            <p class="lead">Tell us which books and quantities you need. Our team will review availability and contact you
                with a quotation.</p>
        </div>
    </section>
    <section class="section" style="padding-top:0;">
        <div class="container">
            <div class="grid grid-2" style="grid-template-columns:1.35fr .65fr;align-items:start;gap:28px;">
                <div class="card">
                    <h3 style="margin-top:0;">Request a quotation</h3>
                    <form method="POST" action="{{ route('bulk-orders.store') }}" id="bulk_order_form">@csrf
                        <div class="grid grid-2" style="gap:14px;">
                            <div class="form-group"><label>Institution / Business Name</label><input name="institution_name"
                                    value="{{ old('institution_name') }}" class="form-control" required></div>
                            <div class="form-group"><label>Contact Person</label><input name="contact_name"
                                    value="{{ old('contact_name', auth()->user()?->name) }}" class="form-control" required>
                            </div>
                            <div class="form-group"><label>Email Address</label><input type="email" name="email"
                                    value="{{ old('email', auth()->user()?->email) }}" class="form-control" required></div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <div style="display: flex; gap: 8px;">
                                    <select name="phone_code" id="bulk_phone_code" class="form-control" style="max-width: 145px; flex: 0 0 145px; font-weight: 700;">
                                        <option value="+91" selected>🇮🇳 +91 (IN)</option>
                                        <option value="+1">🇺🇸 +1 (USA)</option>
                                        <option value="+44">🇬🇧 +44 (UK)</option>
                                        <option value="+1">🇨🇦 +1 (Canada)</option>
                                        <option value="+65">🇸🇬 +65 (Singapore)</option>
                                        <option value="+971">🇦🇪 +971 (Dubai/UAE)</option>
                                        <option value="+49">🇩🇪 +49 (Germany)</option>
                                        <option value="+61">🇦🇺 +61 (Australia)</option>
                                        <option value="+33">🇫🇷 +33 (France)</option>
                                        <option value="+880">🇧🇩 +880 (Bangladesh)</option>
                                        <option value="+977">🇳🇵 +977 (Nepal)</option>
                                        <option value="+94">🇱🇰 +94 (Sri Lanka)</option>
                                        <option value="+81">🇯🇵 +81 (Japan)</option>
                                        <option value="">Other</option>
                                    </select>
                                    <input name="phone_number" value="{{ old('phone_number', old('phone')) }}" class="form-control" placeholder="Mobile number" required style="flex: 1;">
                                </div>
                                <input type="hidden" name="phone" id="bulk_phone_combined">
                            </div>
                        </div>
                        <div class="form-group"><label>Books and quantities needed</label><textarea name="requirements"
                                class="form-control" style="min-height:150px;"
                                placeholder="Example: Organic Chemistry — 40 copies&#10;Modern Indian History — 25 copies"
                                required>{{ old('requirements') }}</textarea></div>
                        <div class="form-group"><label>Additional notes <span
                                    style="font-weight:400;color:var(--text-secondary);">(optional)</span></label><textarea
                                name="notes" class="form-control" style="min-height:90px;"
                                placeholder="Delivery deadline, location, invoice requirements, etc.">{{ old('notes') }}</textarea>
                        </div>
                        <button class="btn btn-primary" style="width:100%;">Submit quote request</button>
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
                <div class="card" style="background:var(--surface-alt);">
                    <h3 style="margin-top:0;">How it works</h3>
                    <ol style="padding-left:20px;line-height:1.8;color:var(--text-secondary);">
                        <li>Submit your required titles and quantities.</li>
                        <li>Our team checks stock and publisher availability.</li>
                        <li>We contact you with pricing and delivery details.</li>
                        <li>Confirm the quote to proceed with the order.</li>
                    </ol>
                    <p style="font-size:.82rem;color:var(--text-secondary);margin-bottom:0;">Submitting this form does not
                        place or charge an order.</p>
                </div>
            </div>
        </div>
    </section>
@endsection