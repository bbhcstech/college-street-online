@extends('layouts.dashboard')
@php($homeRoute = route('admin.dashboard'))
@php($brandLabel = 'Admin Console')
@php($crumb = 'Marketplace')
@php($logoutRoute = route('admin.logout'))
@section('title', 'Coupons & Offers')
@section('nav')@include('admin.partials.nav', ['active' => 'coupons'])@endsection
@section('content')
<div class="a-grid" style="grid-template-columns:1fr 1.4fr;align-items:start;">
<div class="a-card">
    <h3 style="margin-top:0;">Create Coupon</h3>
    <form method="POST" action="{{ route('admin.coupons.store') }}">
        @csrf
        <div class="a-form-group"><label>Code</label><input type="text" name="code" class="a-input" required style="text-transform:uppercase;"></div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Type</label><select name="discount_type" class="a-select"><option value="percentage">Percentage</option><option value="fixed">Fixed</option></select></div>
            <div class="a-form-group"><label>Value</label><input type="number" step="0.01" name="discount_value" class="a-input" required></div>
        </div>
        <div class="a-form-group"><label>Min Order Value (&#8377;, optional)</label><input type="number" step="0.01" name="min_order_value" class="a-input"></div>
        <div class="a-grid a-grid-2">
            <div class="a-form-group"><label>Valid From</label><input type="date" name="valid_from" class="a-input"></div>
            <div class="a-form-group"><label>Valid To</label><input type="date" name="valid_to" class="a-input"></div>
        </div>
        <div class="a-form-group"><label>Usage Limit (optional)</label><input type="number" name="usage_limit" class="a-input"></div>
        <button type="submit" class="btn btn-primary">Create Coupon</button>
    </form>
</div>
<div class="a-card">
    <h3 style="margin-top:0;">Active Coupons</h3>
    <table class="a-table"><thead><tr><th>Code</th><th>Discount</th><th>Used</th><th></th></tr></thead><tbody>
    @forelse($coupons as $c)
        <tr>
            <td><strong>{{ $c->code }}</strong></td>
            <td>{{ $c->discount_type === 'percentage' ? $c->discount_value.'%' : '₹'.$c->discount_value }}</td>
            <td>{{ $c->times_used }}{{ $c->usage_limit ? ' / '.$c->usage_limit : '' }}</td>
            <td><form method="POST" action="{{ route('admin.coupons.destroy', $c) }}">@csrf @method('DELETE')<button class="btn btn-danger btn-sm">Remove</button></form></td>
        </tr>
    @empty
        <tr><td colspan="4">No coupons yet.</td></tr>
    @endforelse
    </tbody></table>
</div>
</div>
@endsection
