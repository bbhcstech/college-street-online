@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Overview';
    $logoutRoute = route('admin.logout');
    $maxRevenue = max(1, (float) $revenueTrend->max('revenue'));
    $maxStatus = max(1, (int) $statusCounts->max());
@endphp
@section('title', 'Analytics & Reports')
@section('nav')@include('admin.partials.nav', ['active' => 'analytics'])@endsection
@section('content')
<div class="page-header" style="margin-bottom:22px;">
    <p style="margin:0;color:var(--a-text-muted);">Sales performance and platform activity.</p>
    <form method="GET">
        <select name="period" class="a-select" onchange="this.form.submit()" style="min-width:180px;">
            <option value="30" @selected($period === '30')>Last 30 days</option>
            <option value="90" @selected($period === '90')>Last 90 days</option>
            <option value="365" @selected($period === '365')>Last 12 months</option>
            <option value="all" @selected($period === 'all')>All time</option>
        </select>
    </form>
</div>

<div class="a-grid a-grid-3" style="margin-bottom:22px;">
    <div class="stat-box"><div class="label">Verified revenue</div><div class="num" style="margin-top:10px;">₹{{ number_format($revenue, 0) }}</div></div>
    <div class="stat-box"><div class="label">Total orders</div><div class="num" style="margin-top:10px;">{{ number_format($orderVolume) }}</div></div>
    <div class="stat-box"><div class="label">Average paid order</div><div class="num" style="margin-top:10px;">₹{{ number_format($averageOrder, 0) }}</div></div>
</div>

<div class="a-grid a-grid-2">
    <div class="a-card">
        <div class="a-card-head"><div class="a-card-icon">₹</div><h3 style="margin:0;">Revenue over time</h3></div>
        @forelse($revenueTrend as $point)
            <div style="display:grid;grid-template-columns:82px 1fr 100px;align-items:center;gap:12px;margin:13px 0;font-size:.8rem;">
                <span style="color:var(--a-text-muted);">{{ $point->period }}</span>
                <div style="height:11px;background:var(--a-surface-alt);border-radius:99px;overflow:hidden;"><div style="height:100%;width:{{ max(2, ($point->revenue / $maxRevenue) * 100) }}%;background:linear-gradient(90deg,var(--a-primary),var(--a-gold));border-radius:99px;"></div></div>
                <strong style="text-align:right;">₹{{ number_format($point->revenue, 0) }}</strong>
            </div>
        @empty
            <p style="color:var(--a-text-muted);">No verified revenue in this period.</p>
        @endforelse
    </div>

    <div class="a-card">
        <div class="a-card-head"><div class="a-card-icon">◔</div><h3 style="margin:0;">Order status distribution</h3></div>
        @foreach($statusLabels as $status => $label)
            @php($count = (int) ($statusCounts[$status] ?? 0))
            <div style="display:grid;grid-template-columns:105px 1fr 35px;align-items:center;gap:10px;margin:11px 0;font-size:.8rem;">
                <span>{{ $label }}</span>
                <div style="height:9px;background:var(--a-surface-alt);border-radius:99px;overflow:hidden;"><div style="height:100%;width:{{ $count ? max(3, ($count / $maxStatus) * 100) : 0 }}%;background:{{ $status === 'cancelled' ? 'var(--a-danger)' : 'var(--a-primary)' }};border-radius:99px;"></div></div>
                <strong style="text-align:right;">{{ $count }}</strong>
            </div>
        @endforeach
    </div>
</div>

<div class="a-card">
    <div class="a-card-head"><div class="a-card-icon">★</div><h3 style="margin:0;">Top-selling books</h3></div>
    <div style="overflow-x:auto;"><table class="a-table"><thead><tr><th>#</th><th>Book</th><th>Units sold</th><th>Gross sales</th></tr></thead><tbody>
    @forelse($topBooks as $book)
        <tr><td>{{ $loop->iteration }}</td><td style="font-weight:600;">{{ $book->title }}</td><td>{{ number_format($book->units) }}</td><td>₹{{ number_format($book->sales, 0) }}</td></tr>
    @empty
        <tr><td colspan="4" style="text-align:center;color:var(--a-text-muted);padding:30px;">No book sales in this period.</td></tr>
    @endforelse
    </tbody></table></div>
</div>

<div class="a-card">
    <div class="a-card-head"><div class="a-card-icon">✓</div><h3 style="margin:0;">Platform health</h3></div>
    <div class="a-grid a-grid-3">
        @foreach($health as $label => $value)
            <div style="padding:17px;border:1px solid var(--a-border);border-radius:10px;background:var(--a-surface-alt);"><div style="color:var(--a-text-muted);font-size:.78rem;">{{ $label }}</div><strong style="display:block;margin-top:5px;font-size:1.35rem;">{{ number_format($value) }}</strong></div>
        @endforeach
    </div>
</div>
@endsection
