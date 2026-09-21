@unless(auth()->check() || session()->has('country_confirmed'))
    @php
        $activeCountries = \Illuminate\Support\Facades\Cache::remember('active_countries_list', 86400, fn () =>
            \App\Models\Country::where('is_active', true)->orderBy('name')->get()
        );
        $selectedCountryCode = session('customer_country', 'IN');
    @endphp
    <div id="guest-country-modal" style="position:fixed;bottom:20px;right:20px;z-index:9999;max-width:380px;width:calc(100% - 40px);background:var(--card-bg, #ffffff);border:1px solid var(--border-color, #e2e8f0);box-shadow:0 10px 25px -5px rgba(0,0,0,0.15);border-radius:12px;padding:20px;font-family:var(--font-body, inherit);">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:10px;">
            <div style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:1.2rem;">🌐</span>
                <strong style="font-size:1rem;color:var(--text-primary, #1e293b);">Confirm your region</strong>
            </div>
            <button type="button" onclick="document.getElementById('guest-country-modal').style.display='none';" style="background:none;border:none;font-size:1.2rem;cursor:pointer;color:var(--text-muted, #94a3b8);padding:0;line-height:1;">&times;</button>
        </div>
        <p style="font-size:0.85rem;color:var(--text-secondary, #64748b);margin:0 0 14px 0;line-height:1.4;">
            We use your country to show accurate book prices and local shipping rules.
        </p>
        <form method="POST" action="{{ route('country.switch') }}">
            @csrf
            <div style="display:flex;gap:8px;margin-bottom:12px;">
                <select name="country" class="form-control" style="font-size:0.88rem;padding:8px 12px;" required>
                    @foreach($activeCountries as $c)
                        <option value="{{ $c->code }}" @selected($selectedCountryCode === $c->code)>
                            {{ $c->name }} ({{ $c->currency_code }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div style="display:flex;gap:8px;justify-content:flex-end;">
                <button type="submit" class="btn btn-gold" style="font-size:0.85rem;padding:8px 16px;width:100%;">
                    Confirm Region
                </button>
            </div>
        </form>
    </div>
@endunless

