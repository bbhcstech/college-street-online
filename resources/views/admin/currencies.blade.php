@extends('layouts.dashboard', [
    'logoutRoute' => route('admin.logout'),
    'homeRoute' => route('admin.dashboard'),
    'brandLabel' => 'Admin Console',
    'crumb' => 'Settings / Currencies'
])

@section('title', 'Currency & Exchange Rate Management')

@section('nav')
    @include('admin.partials.nav', ['active' => 'currencies'])
@endsection

@section('content')
    <div class="publisher-page-head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:16px;">
        <div>
            <span class="analytics-eyebrow">Settings / Currencies</span>
            <h2>Currency &amp; Exchange Rate Management</h2>
            <p>Base system currency is <strong>INR (₹)</strong>. Define foreign exchange rates used to calculate international book prices.</p>
        </div>
        <div>
            <button type="button" class="btn btn-primary" onclick="toggleCurrencyForm()">
                <span id="toggle-btn-text">+ Add New Currency</span>
            </button>
        </div>
    </div>

    <div class="a-grid" style="grid-template-columns:minmax(0, 1fr);gap:24px;align-items:start;" id="currencies-container">
        <!-- Add/Edit Form Card (Initially toggled based on action or edit) -->
        <div class="a-card" id="currency-form-card" style="display:none;margin-bottom:8px;border:1px solid var(--a-border-accent, #3b82f6);">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
                <h3 id="form-title" style="margin:0;font-size:1.1rem;">Add New Currency</h3>
                <button type="button" class="btn btn-outline btn-sm" onclick="toggleCurrencyForm(false)">Close Panel ✕</button>
            </div>
            <form id="currency-form" method="POST" action="{{ route('admin.currencies.store') }}">
                @csrf
                <input type="hidden" name="_method" id="form-method" value="POST">
                
                <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(200px, 1fr));gap:16px;">
                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Currency Code (3 letters)</label>
                        <input type="text" name="code" id="curr-code" class="a-input" placeholder="e.g. EUR" maxlength="3" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Currency Name</label>
                        <input type="text" name="name" id="curr-name" class="a-input" placeholder="e.g. Euro" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Symbol</label>
                        <input type="text" name="symbol" id="curr-symbol" class="a-input" placeholder="e.g. €" required>
                    </div>

                    <div>
                        <label style="display:block;margin-bottom:6px;font-size:0.85rem;font-weight:600;">Exchange Rate (1 INR = X Foreign)</label>
                        <input type="number" step="0.000001" name="exchange_rate" id="curr-rate" class="a-input" placeholder="e.g. 0.011000" required>
                    </div>
                </div>

                <div style="margin-top:14px;margin-bottom:18px;display:flex;align-items:center;gap:10px;">
                    <input type="checkbox" name="is_active" id="curr-active" value="1" checked style="width:18px;height:18px;cursor:pointer;">
                    <label for="curr-active" style="margin:0;cursor:pointer;font-size:0.9rem;">Active for Customer Checkout</label>
                </div>

                <div style="display:flex;gap:10px;">
                    <button type="submit" class="btn btn-primary" id="form-submit-btn">Save Currency</button>
                    <button type="button" class="btn btn-outline" onclick="resetForm()" id="cancel-btn" style="display:none;">Cancel Edit</button>
                </div>
            </form>
        </div>

        <div class="a-card">
            <h3 style="margin-top:0;margin-bottom:16px;font-size:1.15rem;">Active Currencies &amp; Exchange Rates</h3>

            <table class="a-table">
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Symbol</th>
                        <th>Exchange Rate (vs INR)</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($currencies as $currency)
                        <tr>
                            <td><strong>{{ $currency->code }}</strong></td>
                            <td>{{ $currency->name }}</td>
                            <td><code>{{ $currency->symbol }}</code></td>
                            <td>
                                <span>1 INR = {{ number_format($currency->exchange_rate, 6) }} {{ $currency->code }}</span>
                            </td>
                            <td>
                                <span class="badge {{ $currency->is_active ? 'badge-success' : 'badge-muted' }}">
                                    {{ $currency->is_active ? 'Active' : 'Disabled' }}
                                </span>
                            </td>
                            <td style="text-align:right;white-space:nowrap;">
                                <button type="button" class="btn btn-outline btn-sm" onclick="editCurrency({{ json_encode($currency) }})" style="margin-right:6px;">Edit Rate</button>
                                @if($currency->code !== 'INR')
                                    <form method="POST" action="{{ route('admin.currencies.destroy', $currency) }}" style="display:inline-block;" onsubmit="return confirm('Remove {{ $currency->code }} currency?')">
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
        function toggleCurrencyForm(forceState) {
            const card = document.getElementById('currency-form-card');
            const btnText = document.getElementById('toggle-btn-text');
            const isHidden = card.style.display === 'none';
            const shouldShow = forceState !== undefined ? forceState : isHidden;

            if (shouldShow) {
                card.style.display = 'block';
                btnText.innerText = 'Close Form';
                card.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else {
                card.style.display = 'none';
                btnText.innerText = '+ Add New Currency';
                resetForm();
            }
        }

        function editCurrency(c) {
            toggleCurrencyForm(true);
            document.getElementById('form-title').innerText = 'Edit Rate for ' + c.code;
            document.getElementById('currency-form').action = '/admin/currencies/' + c.id;
            document.getElementById('form-method').value = 'PUT';
            document.getElementById('curr-code').value = c.code;
            document.getElementById('curr-code').disabled = true;
            document.getElementById('curr-name').value = c.name;
            document.getElementById('curr-symbol').value = c.symbol;
            document.getElementById('curr-rate').value = c.exchange_rate;
            document.getElementById('curr-active').checked = c.is_active;
            document.getElementById('cancel-btn').style.display = 'inline-flex';
            document.getElementById('form-submit-btn').innerText = 'Update Exchange Rate';
        }

        function resetForm() {
            document.getElementById('form-title').innerText = 'Add New Currency';
            document.getElementById('currency-form').action = "{{ route('admin.currencies.store') }}";
            document.getElementById('form-method').value = 'POST';
            document.getElementById('curr-code').disabled = false;
            document.getElementById('currency-form').reset();
            document.getElementById('cancel-btn').style.display = 'none';
            document.getElementById('form-submit-btn').innerText = 'Save Currency';
        }
    </script>
@endsection
