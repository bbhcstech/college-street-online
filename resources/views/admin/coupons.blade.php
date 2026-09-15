@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Coupons & Offers')
@section('nav')@include('admin.partials.nav', ['active' => 'coupons'])@endsection
@section('content')

{{-- Executive KPI Summary Cards --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px; margin-bottom:22px;">
    <div style="background:linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border:1px solid var(--a-border, #e2e8f0); border-top:3px solid var(--brand-primary, #1e3a8a); border-radius:14px; padding:18px 22px; box-shadow:0 4px 14px rgba(0,0,0,0.02); display:flex; align-items:center; gap:16px;">
        <div style="width:46px; height:46px; border-radius:12px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 12%, #ffffff); display:grid; place-items:center; font-size:1.35rem; box-shadow:0 2px 6px rgba(0,0,0,0.04);">🏷️</div>
        <div>
            <span style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-secondary, #64748b); text-transform:uppercase; letter-spacing:0.05em;">Total Coupons</span>
            <strong style="font-size:1.55rem; font-weight:800; color:var(--text-primary, #0f172a); line-height:1.1;">{{ $coupons->total() }}</strong>
        </div>
    </div>
    <div style="background:linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border:1px solid var(--a-border, #e2e8f0); border-top:3px solid #16a34a; border-radius:14px; padding:18px 22px; box-shadow:0 4px 14px rgba(0,0,0,0.02); display:flex; align-items:center; gap:16px;">
        <div style="width:46px; height:46px; border-radius:12px; background:#dcfce7; display:grid; place-items:center; font-size:1.35rem; box-shadow:0 2px 6px rgba(22,163,74,0.1);">✅</div>
        <div>
            <span style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-secondary, #64748b); text-transform:uppercase; letter-spacing:0.05em;">Active Offers</span>
            <strong style="font-size:1.55rem; font-weight:800; color:#15803d; line-height:1.1;">{{ $activeCount }}</strong>
        </div>
    </div>
    <div style="background:linear-gradient(135deg, #fffbeb 0%, #ffffff 100%); border:1px solid var(--a-border, #e2e8f0); border-top:3px solid #d97706; border-radius:14px; padding:18px 22px; box-shadow:0 4px 14px rgba(0,0,0,0.02); display:flex; align-items:center; gap:16px;">
        <div style="width:46px; height:46px; border-radius:12px; background:#fef3c7; display:grid; place-items:center; font-size:1.35rem; box-shadow:0 2px 6px rgba(217,119,6,0.1);">🎁</div>
        <div>
            <span style="display:block; font-size:0.75rem; font-weight:700; color:var(--text-secondary, #64748b); text-transform:uppercase; letter-spacing:0.05em;">Total Redemptions</span>
            <strong style="font-size:1.55rem; font-weight:800; color:#b45309; line-height:1.1;">{{ $totalUses }}</strong>
        </div>
    </div>
</div>

{{-- Pending Publisher Offers Section --}}
@if($couponRequests->isNotEmpty())
    <section class="a-card coupon-request-review" style="margin-bottom:22px; background:#ffffff; border:1px solid var(--a-border, #e2e8f0); border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.03);">
        <div class="coupon-section-head" style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
            <div>
                <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">Publisher Offer Requests</h3>
                <p style="margin:2px 0 0 0; font-size:0.82rem; color:var(--text-secondary);">Review proposals submitted by publishers before activating for customers.</p>
            </div>
            <span class="badge badge-gold" style="padding:5px 14px; font-size:0.78rem; font-weight:700; border-radius:20px; background:#fef3c7; color:#92400e;">
                {{ $couponRequests->count() }} Pending Approval
            </span>
        </div>
        <div class="coupon-table-wrap">
            <table class="a-table">
                <thead>
                    <tr style="background:#f8fafc;">
                        <th>Publisher</th>
                        <th>Code</th>
                        <th>Offer Details</th>
                        <th>Validity</th>
                        <th>Publisher Note</th>
                        <th style="text-align:right;">Decision</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($couponRequests as $item)
                        <tr>
                            <td><strong>{{ $item->publisher?->business_name ?? 'Publisher unavailable' }}</strong></td>
                            <td>
                                <span style="background:#eff6ff; border:1px dashed #3b82f6; color:#1e40af; font-weight:800; font-family:monospace; padding:4px 10px; border-radius:6px; font-size:0.88rem; display:inline-block;">
                                    {{ $item->code }}
                                </span>
                            </td>
                            <td>
                                <strong style="color:var(--brand-primary, #1e3a8a);">{{ $item->discount_type === 'percentage' ? number_format($item->discount_value, 0) . '%' : '₹' . number_format($item->discount_value, 2) }} OFF</strong>
                                <small style="display:block; color:var(--text-secondary); margin-top:2px;">Min spend: ₹{{ number_format($item->min_order_value ?? 0, 0) }} &bull; Limit: {{ $item->usage_limit ?? 'Unlimited' }}</small>
                            </td>
                            <td>
                                {{ $item->valid_from?->format('d M Y') ?? 'Immediately' }}
                                <small style="display:block; color:var(--text-secondary);">to {{ $item->valid_to?->format('d M Y') ?? 'No expiry' }}</small>
                            </td>
                            <td><small style="color:var(--text-secondary);">{{ $item->publisher_notes ?? '—' }}</small></td>
                            <td style="text-align:right;">
                                <form method="POST" action="{{ route('admin.coupon-requests.review', $item) }}" class="coupon-review-form" style="display:inline-flex; flex-direction:column; gap:6px; align-items:flex-end;">
                                    @csrf 
                                    @method('PATCH')
                                    <input name="admin_notes" class="a-input" maxlength="1000" placeholder="Response note..." style="padding:4px 8px; font-size:0.78rem; width:170px;">
                                    <div style="display:flex; gap:6px;">
                                        <button name="decision" value="approved" class="btn btn-primary btn-sm" style="padding:4px 12px; font-size:0.75rem; font-weight:700; border-radius:6px;" onclick="return confirm('Approve and activate this publisher coupon?')">Approve</button>
                                        <button name="decision" value="rejected" class="btn btn-danger-outline btn-sm" style="padding:4px 12px; font-size:0.75rem; border-radius:6px;" onclick="return confirm('Reject this coupon request?')">Reject</button>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </section>
@endif

{{-- Create Coupon Card with Toggle Button --}}
<section class="a-card coupon-create-card" style="margin-bottom:22px; background:#ffffff; border:1px solid var(--a-border, #e2e8f0); border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.03);">
    <div class="coupon-section-head" style="display:flex; align-items:center; justify-content:space-between; cursor:pointer;" onclick="toggleCreateCouponForm()">
        <div style="display:flex; align-items:center; gap:12px;">
            <div style="width:38px; height:38px; border-radius:10px; background:color-mix(in srgb, var(--brand-primary, #1e3a8a) 8%, #ffffff); display:grid; place-items:center; font-size:1.15rem;">✨</div>
            <div>
                <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">Create Coupon</h3>
                <p style="margin:2px 0 0 0; font-size:0.82rem; color:var(--text-secondary);">Choose a publisher to limit the offer to only that publisher's books, or create a platform-wide code.</p>
            </div>
        </div>
        <button type="button" id="toggleCouponFormBtn" class="btn btn-outline btn-sm" style="font-weight:700; display:flex; align-items:center; gap:6px; border-radius:20px; padding:7px 16px; border-color:var(--brand-primary, #1e3a8a); color:var(--brand-primary, #1e3a8a);">
            <span>+ Create New Coupon</span>
        </button>
    </div>

    <form method="POST" action="{{ route('admin.coupons.store') }}" id="createCouponForm" style="display: {{ $errors->any() || old('code') ? 'block' : 'none' }}; margin-top:18px; border-top:1px solid var(--a-border, #e2e8f0); padding-top:18px;">
        @csrf
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:14px;">
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Coupon Code <span style="color:#ef4444;">*</span></label>
                <input name="code" value="{{ old('code') }}" class="a-input" maxlength="40" placeholder="e.g. FESTIVE20" required style="text-transform:uppercase; font-family:monospace; font-weight:700; letter-spacing:0.05em; padding:8px 12px; border-radius:8px;">
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Offer Owner / Scope</label>
                <select name="publisher_id" class="a-select" style="padding:8px 12px; font-size:0.85rem; border-radius:8px;">
                    <option value="">All publishers (Platform Offer)</option>
                    @foreach($publishers as $publisher)
                        <option value="{{ $publisher->id }}" @selected((string) old('publisher_id') === (string) $publisher->id)>
                            {{ $publisher->business_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Discount Type</label>
                <select name="discount_type" class="a-select" style="padding:8px 12px; font-size:0.85rem; border-radius:8px;">
                    <option value="percentage" @selected(old('discount_type') === 'percentage')>Percentage (%)</option>
                    <option value="fixed" @selected(old('discount_type') === 'fixed')>Fixed Amount (₹)</option>
                </select>
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Discount Value <span style="color:#ef4444;">*</span></label>
                <input type="number" step="0.01" min="0.01" name="discount_value" value="{{ old('discount_value') }}" class="a-input" required placeholder="e.g. 15 or 100" style="padding:8px 12px; border-radius:8px;">
            </div>
        </div>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:18px;">
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Minimum Order (₹)</label>
                <input type="number" step="0.01" min="0" name="min_order_value" value="{{ old('min_order_value') }}" class="a-input" placeholder="Optional (e.g. 499)" style="padding:8px 12px; border-radius:8px;">
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Valid From</label>
                <input type="date" name="valid_from" value="{{ old('valid_from') }}" class="a-input" style="padding:8px 12px; border-radius:8px;">
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Valid Until</label>
                <input type="date" name="valid_to" value="{{ old('valid_to') }}" class="a-input" style="padding:8px 12px; border-radius:8px;">
            </div>
            <div class="a-form-group">
                <label style="font-size:0.8rem; font-weight:700; color:var(--text-primary); margin-bottom:4px; display:block;">Usage Limit</label>
                <input type="number" min="1" name="usage_limit" value="{{ old('usage_limit') }}" class="a-input" placeholder="Unlimited" style="padding:8px 12px; border-radius:8px;">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end;">
            <button class="btn btn-primary" style="padding:10px 24px; font-weight:700; border-radius:8px; display:flex; align-items:center; gap:8px; box-shadow:0 4px 12px rgba(30,58,138,0.2);">
                <span>🎟️</span> Create Coupon
            </button>
        </div>
    </form>
</section>

{{-- Coupon Management List & Single-Row Filter Toolbar --}}
<section class="a-card coupon-list-card" style="background:#ffffff; border:1px solid var(--a-border, #e2e8f0); border-radius:14px; padding:22px; box-shadow:0 4px 16px rgba(0,0,0,0.03);">
    <div class="coupon-section-head" style="margin-bottom:16px;">
        <div>
            <h3 style="margin:0; font-size:1.1rem; font-weight:700; color:var(--text-primary);">Coupon Management</h3>
            <p style="margin:2px 0 0 0; font-size:0.82rem; color:var(--text-secondary);">Search, filter, activate or deactivate platform and publisher offers.</p>
        </div>
    </div>

    {{-- Tightened Single Row Filter Toolbar --}}
    <form method="GET" class="coupon-toolbar" style="display:flex; align-items:center; flex-wrap:wrap; gap:10px; margin-bottom:16px; justify-content:flex-start;">
        <div style="position:relative; width:220px; flex:0 0 220px;">
            <span style="position:absolute; left:12px; top:50%; transform:translateY(-50%); font-size:0.85rem; color:var(--text-muted, #94a3b8);">🔍</span>
            <input name="q" value="{{ request('q') }}" class="a-input" placeholder="Search coupon code..." style="width:100%; padding:8px 12px 8px 34px; margin:0; border-radius:8px;">
        </div>
        <select name="publisher_id" class="a-select" style="width:180px; flex:0 0 180px; padding:8px 12px; margin:0; border-radius:8px;">
            <option value="">All publishers</option>
            @foreach($publishers as $publisher)
                <option value="{{ $publisher->id }}" @selected((string) request('publisher_id') === (string) $publisher->id)>
                    {{ $publisher->business_name }}
                </option>
            @endforeach
        </select>
        <select name="status" class="a-select" style="width:130px; flex:0 0 130px; padding:8px 12px; margin:0; border-radius:8px;">
            <option value="">All statuses</option>
            <option value="active" @selected(request('status') === 'active')>Active</option>
            <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
        </select>
        <button class="btn btn-primary btn-sm" style="padding:8px 18px; font-weight:700; border-radius:8px; flex:0 0 auto; white-space:nowrap; margin:0;">Filter</button>
        @if(request()->query())
            <a href="{{ route('admin.coupons.index') }}" class="btn btn-outline btn-sm" style="padding:8px 14px; border-radius:8px; flex:0 0 auto; white-space:nowrap; margin:0;">Reset</a>
        @endif
    </form>

    {{-- Coupons Table --}}
    <div class="coupon-table-wrap" style="border:1px solid var(--a-border, #e2e8f0); border-radius:10px; overflow:hidden;">
        <table class="a-table coupon-table" style="margin:0;">
            <thead>
                <tr style="background:#f8fafc; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.04em;">
                    <th>Code</th>
                    <th>Publisher / Scope</th>
                    <th>Offer</th>
                    <th>Validity</th>
                    <th>Usage</th>
                    <th>Status</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($coupons as $coupon)
                    @php($expired = $coupon->valid_to && $coupon->valid_to->isPast())
                    <tr style="border-bottom:1px solid #f1f5f9;">
                        <td>
                            <span style="background:#eff6ff; border:1px dashed #3b82f6; color:#1e40af; font-weight:800; font-family:monospace; padding:4px 10px; border-radius:6px; font-size:0.88rem; display:inline-block;">
                                {{ $coupon->code }}
                            </span>
                            <small style="display:block; color:var(--text-muted); margin-top:3px; font-size:0.72rem;">Min ₹{{ number_format($coupon->min_order_value ?? 0, 0) }}</small>
                        </td>
                        <td>
                            <strong style="color:var(--text-primary); font-size:0.86rem;">{{ $coupon->publisher?->business_name ?? 'Platform-wide' }}</strong>
                            <small style="display:block; color:var(--text-secondary); font-size:0.75rem;">{{ $coupon->publisher_id ? 'Publisher books only' : 'All catalogue books' }}</small>
                        </td>
                        <td>
                            <strong style="color:var(--brand-primary, #1e3a8a); font-size:0.92rem;">
                                {{ $coupon->discount_type === 'percentage' ? number_format($coupon->discount_value, 0) . '%' : '₹' . number_format($coupon->discount_value, 2) }} OFF
                            </strong>
                            <small style="display:block; color:var(--text-secondary); font-size:0.74rem;">{{ ucfirst($coupon->discount_type) }} discount</small>
                        </td>
                        <td>
                            <span style="font-size:0.84rem; color:var(--text-primary); font-weight:600;">{{ $coupon->valid_from?->format('d M Y') ?? 'Immediately' }}</span>
                            <small style="display:block; color:var(--text-muted); font-size:0.74rem;">to {{ $coupon->valid_to?->format('d M Y') ?? 'No expiry' }}</small>
                        </td>
                        <td>
                            <strong style="font-size:0.88rem; color:var(--text-primary);">{{ $coupon->times_used }}</strong>
                            <small style="display:block; color:var(--text-muted); font-size:0.74rem;">of {{ $coupon->usage_limit ?? 'unlimited' }}</small>
                        </td>
                        <td>
                            <span class="status-pill {{ (!$coupon->is_active || $expired) ? 'status-muted' : 'status-success' }}" style="padding:4px 12px; font-size:0.74rem; border-radius:20px; font-weight:700;">
                                {{ $expired ? 'Expired' : ($coupon->is_active ? 'Active' : 'Inactive') }}
                            </span>
                        </td>
                        <td style="text-align:right;">
                            <div class="coupon-actions" style="display:inline-flex; gap:6px; justify-content:flex-end;">
                                <form method="POST" action="{{ route('admin.coupons.status', $coupon) }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="is_active" value="{{ $coupon->is_active ? 0 : 1 }}">
                                    <button class="btn btn-outline btn-sm" style="padding:5px 12px; font-size:0.75rem; border-radius:20px; font-weight:600;">
                                        {{ $coupon->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Remove this coupon? Existing order records will remain safe.')">
                                    @csrf 
                                    @method('DELETE')
                                    <button class="btn btn-danger-outline btn-sm" style="padding:5px 12px; font-size:0.75rem; border-radius:20px;">
                                        Remove
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="taxonomy-empty" style="text-align:center; padding:28px; color:var(--text-secondary);">No coupons match your filters.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($coupons->hasPages())
        <div class="coupon-pagination" style="margin-top:16px;">{{ $coupons->links() }}</div>
    @endif
</section>

<script>
    function toggleCreateCouponForm() {
        const form = document.getElementById('createCouponForm');
        const btn = document.getElementById('toggleCouponFormBtn');
        if (!form || !btn) return;
        const isHidden = window.getComputedStyle(form).display === 'none';
        if (isHidden) {
            form.style.display = 'block';
            btn.innerHTML = '<span>✕ Close Form</span>';
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-secondary');
        } else {
            form.style.display = 'none';
            btn.innerHTML = '<span>+ Create New Coupon</span>';
            btn.classList.remove('btn-secondary');
            btn.classList.add('btn-outline');
        }
    }
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('createCouponForm');
        const btn = document.getElementById('toggleCouponFormBtn');
        if (form && window.getComputedStyle(form).display !== 'none' && btn) {
            btn.innerHTML = '<span>✕ Close Form</span>';
            btn.classList.remove('btn-outline');
            btn.classList.add('btn-secondary');
        }
    });
</script>
@endsection