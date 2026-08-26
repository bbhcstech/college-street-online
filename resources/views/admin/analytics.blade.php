@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Overview';
    $logoutRoute = route('admin.logout');
    $maxRevenue = max(1, (float) $revenueTrend->max('revenue'));
    $maxUnits = max(1, (int) $topBooks->max('units'));
    $statusTotal = max(1, (int) $statusCounts->sum());
    $periodLabel = ['30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last 12 months', 'all' => 'All time'][$period];
    $statusColors = ['pending_payment' => '#EDA13A', 'confirmed' => '#2B5D85', 'processing' => '#7352B4', 'shipped' => '#2684BE', 'delivered' => '#22A06B', 'completed' => '#1F9D6C', 'cancelled' => '#D64545'];
    $cursor = 0;
    $gradient = [];
    foreach ($statusLabels as $status => $label) {
        $count = (int) ($statusCounts[$status] ?? 0);
        if (!$count)
            continue;
        $next = $cursor + ($count / $statusTotal * 100);
        $gradient[] = $statusColors[$status] . " {$cursor}% {$next}%";
        $cursor = $next;
    }
    $healthIcons = ['Active books' => '▣', 'Approved publishers' => '◆', 'Customers' => '●', 'Subscribers' => '✉', 'Pending payments' => '₹', 'Low-stock books' => '!'];
@endphp
@section('title', 'Analytics & Reports')
@section('nav')@include('admin.partials.nav', ['active' => 'analytics'])@endsection
@section('content')
<div class="analytics-hero">
    <div><span class="analytics-eyebrow">Performance centre</span>
        <h2>Marketplace intelligence</h2>
        <p>Track verified revenue, order behaviour, book performance, and operational health.</p>
    </div>
    <form method="GET" class="analytics-period-form"><label for="analytics-period">Reporting period</label><select
            id="analytics-period" name="period" class="a-select" onchange="this.form.submit()">
            <option value="30" @selected($period === '30')>Last 30 days</option>
            <option value="90" @selected($period === '90')>Last 90 days</option>
            <option value="365" @selected($period === '365')>Last 12 months</option>
            <option value="all" @selected($period === 'all')>All time</option>
        </select></form>
</div>

<div class="a-grid a-grid-3 analytics-kpis">
    <div class="stat-box analytics-kpi">
        <div class="stat-top">
            <div class="stat-icon green">₹</div><span class="analytics-period-chip">{{ $periodLabel }}</span>
        </div>
        <div class="label">Verified revenue</div>
        <div class="num">₹{{ number_format($revenue, 0) }}</div><small>Paid, non-cancelled orders</small>
    </div>
    <div class="stat-box analytics-kpi">
        <div class="stat-top">
            <div class="stat-icon purple">▤</div><span class="analytics-period-chip">Volume</span>
        </div>
        <div class="label">Total orders</div>
        <div class="num">{{ number_format($orderVolume) }}</div><small>All statuses in selected period</small>
    </div>
    <div class="stat-box analytics-kpi">
        <div class="stat-top">
            <div class="stat-icon gold">↗</div><span class="analytics-period-chip">AOV</span>
        </div>
        <div class="label">Average paid order</div>
        <div class="num">₹{{ number_format($averageOrder, 0) }}</div><small>Average verified order value</small>
    </div>
</div>

<div class="analytics-main-grid">
    <div class="a-card analytics-revenue-card">
        <div class="a-card-head dashboard-card-title">
            <div>
                <h3>Revenue over time</h3>
                <p>Verified revenue in INR base value</p>
            </div><span class="badge badge-success">{{ $revenueTrend->count() }} periods</span>
        </div>
        <div class="analytics-revenue-list">@forelse($revenueTrend as $point)
            <div class="analytics-revenue-row">
                <div><strong>{{ $point->period }}</strong><small>{{ number_format($point->orders) }} paid
                        {{ Str::plural('order', $point->orders) }}</small></div>
                <div class="analytics-track"><i style="width:{{ max(2, $point->revenue / $maxRevenue * 100) }}%"></i></div>
                <strong>₹{{ number_format($point->revenue, 0) }}</strong>
        </div>@empty<div class="analytics-empty">No verified revenue in this period.</div>@endforelse
        </div>
    </div>
    <div class="a-card">
        <div class="a-card-head dashboard-card-title">
            <div>
                <h3>Order status distribution</h3>
                <p>{{ number_format($statusCounts->sum()) }} orders in this period</p>
            </div>
        </div>
        <div class="analytics-status-wrap">
            <div class="analytics-donut"
                style="background:conic-gradient({{ implode(',', $gradient) ?: '#DCE2ED 0 100%' }});">
                <div><strong>{{ $statusCounts->sum() }}</strong><span>Total orders</span></div>
            </div>
            <div class="analytics-status-list">
                @foreach($statusLabels as $status => $label)@php($count = (int) ($statusCounts[$status] ?? 0))
                <div><i
                        style="background:{{ $statusColors[$status] }}"></i><span>{{ $label }}</span><strong>{{ $count }}</strong><small>{{ number_format($count / $statusTotal * 100, 0) }}%</small>
                </div>@endforeach
            </div>
        </div>
    </div>
</div>

<div class="a-card analytics-books-card">
    <div class="a-card-head dashboard-card-title">
        <div>
            <h3>Top-selling books</h3>
            <p>Ranked by units sold during {{ strtolower($periodLabel) }}</p>
        </div><a href="{{ route('admin.books.index') }}">Manage books →</a>
    </div>
    <div class="analytics-table-wrap">
        <table class="a-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Book</th>
                    <th>Sales performance</th>
                    <th>Units sold</th>
                    <th>Gross sales (INR)</th>
                </tr>
            </thead>
            <tbody>@forelse($topBooks as $book)
                <tr>
                    <td><span class="analytics-rank {{ $loop->iteration <= 3 ? 'top' : '' }}">{{ $loop->iteration }}</span>
                    </td>
                    <td><strong>{{ $book->title }}</strong></td>
                    <td>
                        <div class="analytics-book-progress"><i
                                style="width:{{ max(3, $book->units / $maxUnits * 100) }}%"></i></div>
                    </td>
                    <td><strong>{{ number_format($book->units) }}</strong></td>
                    <td>₹{{ number_format($book->sales, 0) }}</td>
            </tr>@empty<tr>
                    <td colspan="5">
                        <div class="analytics-empty">No book sales in this period.</div>
                    </td>
                </tr>@endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="a-card">
    <div class="a-card-head dashboard-card-title">
        <div>
            <h3>Platform health</h3>
            <p>Current operational summary</p>
        </div><span
            class="badge {{ ($health['Pending payments'] + $health['Low-stock books']) > 0 ? 'badge-gold' : 'badge-success' }}">Live
            status</span>
    </div>
    <div class="analytics-health-grid">@foreach($health as $label => $value)
        <div
            class="analytics-health-item {{ in_array($label, ['Pending payments', 'Low-stock books']) && $value > 0 ? 'needs-attention' : '' }}">
            <div class="analytics-health-icon">{{ $healthIcons[$label] }}</div>
            <div><strong>{{ number_format($value) }}</strong><span>{{ $label }}</span></div>
    </div>@endforeach
    </div>
</div>
@endsection