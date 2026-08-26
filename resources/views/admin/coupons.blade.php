@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Coupons & Offers')
@section('nav')@include('admin.partials.nav', ['active' => 'coupons'])@endsection
@section('content')
<div class="coupon-summary">
    <div><span>Total coupons</span><strong>{{ $coupons->total() }}</strong></div>
    <div><span>Active offers</span><strong>{{ $activeCount }}</strong></div>
    <div><span>Total redemptions</span><strong>{{ $totalUses }}</strong></div>
</div>

@if($couponRequests->isNotEmpty())
<section class="a-card coupon-request-review">
    <div class="coupon-section-head"><div><h3>Publisher offer requests</h3><p>Review proposals before they become available to customers.</p></div><span>{{ $couponRequests->count() }} pending</span></div>
    <div class="coupon-table-wrap"><table class="a-table"><thead><tr><th>Publisher</th><th>Code</th><th>Offer</th><th>Validity</th><th>Publisher note</th><th>Decision</th></tr></thead><tbody>
    @foreach($couponRequests as $item)<tr><td><strong>{{ $item->publisher?->business_name??'Publisher unavailable' }}</strong></td><td><strong class="coupon-code">{{ $item->code }}</strong></td><td>{{ $item->discount_type==='percentage'?number_format($item->discount_value,0).'%':'₹'.number_format($item->discount_value,2) }}<small>Min ₹{{ number_format($item->min_order_value??0,0) }} · Limit {{ $item->usage_limit??'Unlimited' }}</small></td><td>{{ $item->valid_from?->format('d M Y')??'Immediately' }}<small>to {{ $item->valid_to?->format('d M Y')??'No expiry' }}</small></td><td>{{ $item->publisher_notes??'—' }}</td><td><form method="POST" action="{{ route('admin.coupon-requests.review',$item) }}" class="coupon-review-form">@csrf @method('PATCH')<input name="admin_notes" class="a-input" maxlength="1000" placeholder="Response note (optional)"><div><button name="decision" value="approved" class="btn btn-primary btn-sm" onclick="return confirm('Approve and activate this publisher coupon?')">Approve</button><button name="decision" value="rejected" class="btn btn-danger-outline btn-sm" onclick="return confirm('Reject this coupon request?')">Reject</button></div></form></td></tr>@endforeach
    </tbody></table></div>
</section>
@endif

<section class="a-card coupon-create-card">
    <div class="coupon-section-head"><div><h3>Create coupon</h3><p>Choose a publisher to limit the offer to only that publisher's books.</p></div><span>New offer</span></div>
    <form method="POST" action="{{ route('admin.coupons.store') }}" class="coupon-form">@csrf
        <div class="a-form-group coupon-code-field"><label>Coupon code</label><input name="code" value="{{ old('code') }}" class="a-input" maxlength="40" placeholder="SUMMER20" required style="text-transform:uppercase"></div>
        <div class="a-form-group"><label>Offer owner</label><select name="publisher_id" class="a-select"><option value="">All publishers (platform offer)</option>@foreach($publishers as $publisher)<option value="{{ $publisher->id }}" @selected((string)old('publisher_id')===(string)$publisher->id)>{{ $publisher->business_name }}</option>@endforeach</select></div>
        <div class="a-form-group"><label>Discount type</label><select name="discount_type" class="a-select"><option value="percentage" @selected(old('discount_type')==='percentage')>Percentage (%)</option><option value="fixed" @selected(old('discount_type')==='fixed')>Fixed amount (₹)</option></select></div>
        <div class="a-form-group"><label>Discount value</label><input type="number" step="0.01" min="0.01" name="discount_value" value="{{ old('discount_value') }}" class="a-input" required></div>
        <div class="a-form-group"><label>Minimum order (₹)</label><input type="number" step="0.01" min="0" name="min_order_value" value="{{ old('min_order_value') }}" class="a-input" placeholder="Optional"></div>
        <div class="a-form-group"><label>Valid from</label><input type="date" name="valid_from" value="{{ old('valid_from') }}" class="a-input"></div>
        <div class="a-form-group"><label>Valid until</label><input type="date" name="valid_to" value="{{ old('valid_to') }}" class="a-input"></div>
        <div class="a-form-group"><label>Usage limit</label><input type="number" min="1" name="usage_limit" value="{{ old('usage_limit') }}" class="a-input" placeholder="Unlimited"></div>
        <div class="coupon-create-action"><button class="btn btn-primary">Create coupon</button></div>
    </form>
</section>

<section class="a-card coupon-list-card">
    <div class="coupon-section-head"><div><h3>Coupon management</h3><p>Search, filter, activate or deactivate current offers.</p></div></div>
    <form method="GET" class="coupon-toolbar"><input name="q" value="{{ request('q') }}" class="a-input" placeholder="Search coupon code"><select name="publisher_id" class="a-select"><option value="">All publishers</option>@foreach($publishers as $publisher)<option value="{{ $publisher->id }}" @selected((string)request('publisher_id')===(string)$publisher->id)>{{ $publisher->business_name }}</option>@endforeach</select><select name="status" class="a-select"><option value="">All statuses</option><option value="active" @selected(request('status')==='active')>Active</option><option value="inactive" @selected(request('status')==='inactive')>Inactive</option></select><button class="btn btn-primary btn-sm">Filter</button>@if(request()->query())<a href="{{ route('admin.coupons.index') }}" class="btn btn-outline btn-sm">Reset</a>@endif</form>
    <div class="coupon-table-wrap"><table class="a-table coupon-table"><thead><tr><th>Code</th><th>Publisher / Scope</th><th>Offer</th><th>Validity</th><th>Usage</th><th>Status</th><th>Actions</th></tr></thead><tbody>
    @forelse($coupons as $coupon)
        @php($expired = $coupon->valid_to && $coupon->valid_to->isPast())
        <tr><td><strong class="coupon-code">{{ $coupon->code }}</strong><small>Min ₹{{ number_format($coupon->min_order_value ?? 0, 0) }}</small></td><td><strong>{{ $coupon->publisher?->business_name ?? 'Platform-wide' }}</strong><small>{{ $coupon->publisher_id ? 'Publisher books only' : 'All catalogue books' }}</small></td><td><strong>{{ $coupon->discount_type === 'percentage' ? number_format($coupon->discount_value, 0).'%' : '₹'.number_format($coupon->discount_value, 2) }}</strong><small>{{ ucfirst($coupon->discount_type) }} discount</small></td><td><span>{{ $coupon->valid_from?->format('d M Y') ?? 'Immediately' }}</span><small>to {{ $coupon->valid_to?->format('d M Y') ?? 'No expiry' }}</small></td><td><strong>{{ $coupon->times_used }}</strong><small>of {{ $coupon->usage_limit ?? 'unlimited' }}</small></td><td><span class="status-pill {{ (!$coupon->is_active || $expired) ? 'status-muted' : 'status-success' }}">{{ $expired ? 'Expired' : ($coupon->is_active ? 'Active' : 'Inactive') }}</span></td><td><div class="coupon-actions"><form method="POST" action="{{ route('admin.coupons.status', $coupon) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $coupon->is_active ? 0 : 1 }}"><button class="btn btn-outline btn-sm">{{ $coupon->is_active ? 'Deactivate' : 'Activate' }}</button></form><form method="POST" action="{{ route('admin.coupons.destroy', $coupon) }}" onsubmit="return confirm('Remove this coupon? Existing order records will remain safe.')">@csrf @method('DELETE')<button class="btn btn-danger-outline btn-sm">Remove</button></form></div></td></tr>
    @empty<tr><td colspan="7" class="taxonomy-empty">No coupons match your filters.</td></tr>@endforelse
    </tbody></table></div>
    @if($coupons->hasPages())<div class="coupon-pagination">{{ $coupons->links() }}</div>@endif
</section>
@endsection
