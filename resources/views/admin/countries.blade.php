@extends('layouts.dashboard', [
    'logoutRoute' => route('admin.logout'),
    'homeRoute' => route('admin.dashboard'),
    'brandLabel' => 'Admin Console',
    'crumb' => 'Settings / Market Configuration'
])

@section('title', 'Market Configuration & Destination Countries')

@section('nav')
    @include('admin.partials.nav', ['active' => 'countries'])
@endsection

@section('content')
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:12px;">
        <p style="margin:0;color:var(--a-text-muted, #64748b);font-size:0.92rem;">
            Configure exchange rates, tax rates, shipping rules, payment gateways, and delivery lead times per market.
        </p>
        <button type="button" class="btn btn-primary" onclick="toggleCountryForm()">
            <span id="toggle-btn-text">+ Add Market Destination</span>
        </button>
    </div>

    <div class="country-summary-grid">
        <div><span>Markets Configured</span><strong>{{ $countries->count() }}</strong><small>Active & inactive destinations</small></div>
        <div><span>Active Checkout Routes</span><strong>{{ $countries->where('is_active', true)->count() }}</strong><small>Available to buyers</small></div>
        <div><span>Supported Currencies</span><strong>{{ $countries->pluck('currency_code')->unique()->count() }}</strong><small>Market currencies</small></div>
    </div>

    <div class="a-grid" style="grid-template-columns:minmax(0, 1fr);gap:24px;align-items:start;">
        <!-- Form Panel (Toggleable) -->
        <div class="a-card country-form-card" id="country-form-card" style="display:none;margin-bottom:8px;border:1px solid var(--a-border-accent, #3b82f6);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 id="form-title" style="margin:0;font-size:1.1rem;">Add Market Destination</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleCountryForm(false)">Close Panel ✕</button>
            </div>
            <form id="country-form" method="POST" action="{{ route('admin.countries.store') }}">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(220px, 1fr));gap:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Country Code (ISO 2-letters) *</label>
                        <input type="text" name="code" id="cnt-code" class="a-input" placeholder="e.g. US" maxlength="2" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Country Name *</label>
                        <input type="text" name="name" id="cnt-name" class="a-input" placeholder="e.g. United States" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Assigned Currency *</label>
                        <select name="currency_code" id="cnt-currency" class="a-select" required>
                            @foreach($currencies as $curr)
                                <option value="{{ $curr->code }}">{{ $curr->name }} ({{ $curr->code }} - {{ $curr->symbol }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Price Markup %</label>
                        <input type="number" step="0.1" name="markup_percentage" id="cnt-markup" class="a-input" placeholder="0.0" value="0.0">
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Tax Rate %</label>
                        <input type="number" step="0.01" name="tax_rate" id="cnt-tax-rate" class="a-input" placeholder="e.g. 18.00" value="0.00">
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Min Order Value</label>
                        <input type="number" step="0.01" name="min_order_value" id="cnt-min-order" class="a-input" placeholder="0.00" value="0.00">
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Base Shipping Fee *</label>
                        <input type="number" step="0.01" name="base_shipping_fee" id="cnt-shipping-base" class="a-input" placeholder="50.00" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Per Item Shipping Fee *</label>
                        <input type="number" step="0.01" name="per_item_shipping_fee" id="cnt-shipping-per-item" class="a-input" placeholder="10.00" value="0.00" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Free Shipping Threshold</label>
                        <input type="number" step="0.01" name="free_shipping_threshold" id="cnt-free-shipping" class="a-input" placeholder="Leave empty if none">
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Estimated Delivery Time</label>
                        <input type="text" name="estimated_delivery_days" id="cnt-delivery-time" class="a-input" placeholder="e.g. 7 - 12 business days">
                    </div>
                </div>

                <div style="margin-top:16px;display:flex;flex-wrap:wrap;gap:20px;align-items:center;">
                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;">
                        <input type="checkbox" name="is_tax_inclusive" id="cnt-tax-inclusive" value="1" style="width:16px;height:16px;">
                        <span>Prices are Tax Inclusive for this market</span>
                    </label>

                    <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:0.88rem;">
                        <input type="checkbox" name="is_active" id="cnt-active" value="1" checked style="width:16px;height:16px;">
                        <span>Active Destination Market</span>
                    </label>
                </div>

                <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--a-border, #e2e8f0);">
                    <label style="display:block;margin-bottom:8px;font-size:0.85rem;font-weight:600;">Allowed Payment Gateways</label>
                    <div style="display:flex;gap:16px;flex-wrap:wrap;">
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.88rem;cursor:pointer;">
                            <input type="checkbox" name="payment_methods[]" value="stripe" class="cnt-pm" checked> Credit/Debit Card (Stripe)
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.88rem;cursor:pointer;">
                            <input type="checkbox" name="payment_methods[]" value="paypal" class="cnt-pm" checked> PayPal Express
                        </label>
                        <label style="display:flex;align-items:center;gap:6px;font-size:0.88rem;cursor:pointer;">
                            <input type="checkbox" name="payment_methods[]" value="cod" class="cnt-pm" checked> Cash on Delivery (COD)
                        </label>
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:20px;">
                    <button type="submit" class="btn btn-primary" id="form-submit-btn">Save Market Rules</button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()" id="cancel-btn" style="display:none;">Cancel Edit</button>
                </div>
            </form>
        </div>

        <div class="a-card country-table-card">
            <h3 style="margin-top:0;margin-bottom:16px;font-size:1.15rem;">Configured Market Rules</h3>

            <div class="country-table-scroll"><table class="a-table country-table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Currency</th>
                        <th>Tax Rate</th>
                        <th>Shipping Fee</th>
                        <th>Free Above</th>
                        <th>Min Order</th>
                        <th>Est. Delivery</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($countries as $c)
                        <tr>
                            <td>
                                <strong>{{ $c->name }}</strong> <code>({{ $c->code }})</code>
                            </td>
                            <td>{{ $c->currency_code }} ({{ $c->symbol }})</td>
                            <td>
                                {{ number_format($c->tax_rate ?? 0, 1) }}%
                                <small style="display:block;color:var(--a-text-muted);font-size:0.72rem;">
                                    {{ $c->is_tax_inclusive ? 'Inclusive' : 'Exclusive' }}
                                </small>
                            </td>
                            <td>
                                {{ $c->symbol }}{{ number_format($c->base_shipping_fee, 2) }}
                                <small style="display:block;color:var(--a-text-muted);font-size:0.72rem;">
                                    +{{ $c->symbol }}{{ number_format($c->per_item_shipping_fee, 2) }}/item
                                </small>
                            </td>
                            <td>
                                @if($c->free_shipping_threshold)
                                    {{ $c->symbol }}{{ number_format($c->free_shipping_threshold, 2) }}
                                @else
                                    <span style="color:var(--a-text-muted);">&mdash;</span>
                                @endif
                            </td>
                            <td>
                                @if($c->min_order_value && $c->min_order_value > 0)
                                    {{ $c->symbol }}{{ number_format($c->min_order_value, 2) }}
                                @else
                                    <span style="color:var(--a-text-muted);">&mdash;</span>
                                @endif
                            </td>
                            <td>{{ $c->estimated_delivery_days ?: '5-10 business days' }}</td>
                            <td>
                                <span class="badge {{ $c->is_active ? 'badge-success' : 'badge-muted' }}">
                                    {{ $c->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="editCountry({{ json_encode($c) }})" style="margin-right:6px;">Edit Rules</button>
                                @if($c->code !== 'IN')
                                    <form method="POST" action="{{ route('admin.countries.destroy', $c) }}" style="display:inline-block;" onsubmit="return confirm('Remove {{ $c->name }} destination?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger-outline btn-sm">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
    </div>

    <script>
        function toggleCountryForm(forceState) {
            const card = document.getElementById('country-form-card');
            const btnText = document.getElementById('toggle-btn-text');
            const isHidden = card.style.display === 'none';
            const shouldShow = forceState !== undefined ? forceState : isHidden;

            if (shouldShow) {
                card.style.display = 'block';
                btnText.innerText = 'Close Panel';
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                card.style.display = 'none';
                btnText.innerText = '+ Add Market Destination';
                resetForm();
            }
        }

        function editCountry(c) {
            toggleCountryForm(true);
            document.getElementById('form-title').innerText = 'Edit Market Rules for ' + c.name;
            document.getElementById('country-form').action = '/admin/countries/' + c.id;
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('cnt-code').value = c.code;
            document.getElementById('cnt-code').disabled = true;
            document.getElementById('cnt-name').value = c.name;
            document.getElementById('cnt-currency').value = c.currency_code;
            document.getElementById('cnt-markup').value = c.markup_percentage;
            document.getElementById('cnt-tax-rate').value = c.tax_rate ?? 0;
            document.getElementById('cnt-tax-inclusive').checked = !!c.is_tax_inclusive;
            document.getElementById('cnt-min-order').value = c.min_order_value ?? 0;
            document.getElementById('cnt-shipping-base').value = c.base_shipping_fee;
            document.getElementById('cnt-shipping-per-item').value = c.per_item_shipping_fee;
            document.getElementById('cnt-free-shipping').value = c.free_shipping_threshold ?? '';
            document.getElementById('cnt-delivery-time').value = c.estimated_delivery_days ?? '';
            document.getElementById('cnt-active').checked = c.is_active;

            const pms = c.payment_methods || ['stripe', 'paypal', 'cod'];
            document.querySelectorAll('.cnt-pm').forEach(cb => {
                cb.checked = pms.includes(cb.value);
            });

            document.getElementById('cancel-btn').style.display = 'inline-flex';
            document.getElementById('form-submit-btn').innerText = 'Update Market Rules';
        }

        function resetForm() {
            document.getElementById('form-title').innerText = 'Add Market Destination';
            document.getElementById('country-form').action = "{{ route('admin.countries.store') }}";
            document.getElementById('form-method').value = 'POST';
            document.getElementById('cnt-code').disabled = false;
            document.getElementById('country-form').reset();
            document.getElementById('cancel-btn').style.display = 'none';
            document.getElementById('form-submit-btn').innerText = 'Save Market Rules';
        }
    </script>
@endsection
