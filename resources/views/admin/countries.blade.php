@extends('layouts.dashboard', [
    'logoutRoute' => route('admin.logout'),
    'homeRoute' => route('admin.dashboard'),
    'brandLabel' => 'Admin Console',
    'crumb' => 'Settings / Countries & Shipping'
])

@section('title', 'Country & Shipping Management')

@section('nav')
    @include('admin.partials.nav', ['active' => 'countries'])
@endsection

@section('content')
    <div class="publisher-page-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div>
            <span class="analytics-eyebrow">Settings / Countries &amp; Shipping</span>
            <h2>Destination Countries &amp; Shipping Rates</h2>
            <p>Set shipping fees, currency assignment, and price markups per country.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="toggleCountryForm()">
                <span id="toggle-btn-text">+ Add Destination Country</span>
            </button>
        </div>
    </div>

    <div class="a-grid" style="grid-template-columns:minmax(0, 1fr);gap:24px;align-items:start;">
        <!-- Form Panel (Toggleable) -->
        <div class="a-card" id="country-form-card" style="display:none;margin-bottom:8px;border:1px solid var(--a-border-accent, #3b82f6);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 id="form-title" style="margin:0;font-size:1.1rem;">Add Destination Country</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleCountryForm(false)">Close Panel ✕</button>
            </div>
            <form id="country-form" method="POST" action="{{ route('admin.countries.store') }}">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Country Code (ISO 2-letters)</label>
                        <input type="text" name="code" id="cnt-code" class="a-input" placeholder="e.g. US" maxlength="2" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Country Name</label>
                        <input type="text" name="name" id="cnt-name" class="a-input" placeholder="e.g. United States" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Assigned Currency</label>
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
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Base Shipping Fee</label>
                        <input type="number" step="0.01" name="base_shipping_fee" id="cnt-shipping-base" class="a-input" placeholder="50.00" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Per Item Additional Fee</label>
                        <input type="number" step="0.01" name="per_item_shipping_fee" id="cnt-shipping-per-item" class="a-input" placeholder="10.00" value="0.00" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Free Shipping Threshold</label>
                        <input type="number" step="0.01" name="free_shipping_threshold" id="cnt-free-shipping" class="a-input" placeholder="Leave empty if none">
                    </div>
                </div>

                <div style="margin-top:14px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="is_active" id="cnt-active" value="1" checked style="width:18px;height:18px;cursor:pointer;">
                    <label for="cnt-active" style="margin:0;cursor:pointer;font-size:0.9rem;">Active Destination</label>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" id="form-submit-btn">Save Country</button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()" id="cancel-btn" style="display:none;">Cancel Edit</button>
                </div>
            </form>
        </div>

        <div class="a-card">
            <h3 style="margin-top:0;margin-bottom:16px;font-size:1.15rem;">Active Destination Countries</h3>

            <table class="a-table">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Currency</th>
                        <th>Markup %</th>
                        <th>Base Shipping</th>
                        <th>Free Above</th>
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
                            <td>{{ number_format($c->markup_percentage, 1) }}%</td>
                            <td>{{ $c->symbol }}{{ number_format($c->base_shipping_fee, 2) }}</td>
                            <td>
                                @if($c->free_shipping_threshold)
                                    {{ $c->symbol }}{{ number_format($c->free_shipping_threshold, 2) }}
                                @else
                                    <span style="color:var(--a-text-muted);">&mdash;</span>
                                @endif
                            </td>
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
            </table>
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
                btnText.innerText = '+ Add Destination Country';
                resetForm();
            }
        }

        function editCountry(c) {
            toggleCountryForm(true);
            document.getElementById('form-title').innerText = 'Edit Shipping Rules for ' + c.name;
            document.getElementById('country-form').action = '/admin/countries/' + c.id;
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('cnt-code').value = c.code;
            document.getElementById('cnt-code').disabled = true;
            document.getElementById('cnt-name').value = c.name;
            document.getElementById('cnt-currency').value = c.currency_code;
            document.getElementById('cnt-markup').value = c.markup_percentage;
            document.getElementById('cnt-shipping-base').value = c.base_shipping_fee;
            document.getElementById('cnt-shipping-per-item').value = c.per_item_shipping_fee;
            document.getElementById('cnt-free-shipping').value = c.free_shipping_threshold ?? '';
            document.getElementById('cnt-active').checked = c.is_active;
            document.getElementById('cancel-btn').style.display = 'inline-flex';
            document.getElementById('form-submit-btn').innerText = 'Update Country Rules';
        }

        function resetForm() {
            document.getElementById('form-title').innerText = 'Add Destination Country';
            document.getElementById('country-form').action = "{{ route('admin.countries.store') }}";
            document.getElementById('form-method').value = 'POST';
            document.getElementById('cnt-code').disabled = false;
            document.getElementById('country-form').reset();
            document.getElementById('cancel-btn').style.display = 'none';
            document.getElementById('form-submit-btn').innerText = 'Save Country';
        }
    </script>
@endsection
