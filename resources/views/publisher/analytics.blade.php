@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Analytics & Reports';
    $logoutRoute = route('publisher.logout');
    $maxRevenue = max(1, $series->max('revenue'));
    $maxUnits = max(1, $series->max('units'));
    $statusTotal = max(1, $statusMix->sum());
    $colors = ['pending_payment' => '#eda13a', 'confirmed' => '#2b5d85', 'processing' => '#7352b4', 'packed' => '#c56824', 'shipped' => '#2684be', 'delivered' => '#1f9d6c', 'completed' => '#37b77a', 'cancelled' => '#d64545'];
    $cursor = 0;
    $gradient = [];
    foreach ($statusMix as $status => $count) {
        $next = $cursor + ($count / $statusTotal * 100);
        $gradient[] = ($colors[$status] ?? '#8a96a8') . " {$cursor}% {$next}%";
        $cursor = $next;
    }
    $trendWidth = 620;
    $trendHeight = 210;
    $trendPad = 28;
    $trendPlotWidth = $trendWidth - ($trendPad * 2);
    $trendPlotHeight = $trendHeight - 50;
    $revenueLine = $series->map(fn($point, $index) => round($trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2), 1) . ',' . round($trendPad + $trendPlotHeight - ($point['revenue'] / $maxRevenue * $trendPlotHeight), 1))->implode(' ');
    $unitsLine = $series->map(fn($point, $index) => round($trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2), 1) . ',' . round($trendPad + $trendPlotHeight - ($point['units'] / $maxUnits * $trendPlotHeight), 1))->implode(' ');
    $revenueArea = $trendPad . ',' . ($trendPad + $trendPlotHeight) . ' ' . $revenueLine . ' ' . ($trendWidth - $trendPad) . ',' . ($trendPad + $trendPlotHeight);
@endphp
@section('title', 'Analytics & Reports')
@section('nav')@include('publisher.partials.nav', ['active' => 'analytics'])@endsection
@section('content')
<style>
    html.dark .dashboard-card {
        background: var(--a-surface);
        color: var(--a-text);
        border-color: var(--a-border);
    }

    html.dark .analytics-toolbar {
        background: var(--a-surface);
        border-color: var(--a-border);
    }

    html.dark .analytics-toolbar p,
    html.dark .card-heading p,
    html.dark .chart-legend,
    html.dark .analytics-bar-group small {
        color: var(--a-text-muted);
    }

    html.dark .analytics-actions select {
        background: var(--a-surface-alt);
        color: var(--a-text);
        border-color: var(--a-border);
    }

    html.dark .status-donut::before {
        background: var(--a-surface);
    }

    html.dark .analytics-chart {
        border-color: var(--a-border);
    }

    html.dark .publisher-analytics-table th,
    html.dark .publisher-analytics-table td {
        color: var(--a-text);
        border-color: var(--a-border);
    }

    html.dark .rank-pill {
        background: rgba(237, 161, 58, .18);
        color: #f1ad4d;
    }
</style>
<div class="analytics-toolbar">
    <div>
        <h2>Analytics &amp; Reports</h2>
        <p>Sales, books, inventory, orders, and customers for your catalogue.</p>
    </div>
    <div class="analytics-actions">
        <form method="GET" class="publisher-analytics-filter">
            <select name="period">
                <option value="day" @selected($period === 'day')>Daily</option>
                <option value="week" @selected($period === 'week')>Weekly</option>
                <option value="month" @selected($period === 'month')>Monthly</option>
                <option value="year" @selected($period === 'year')>Yearly</option>
                <option value="custom" @selected($period === 'custom')>Custom dates</option>
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input"
                aria-label="From date">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="a-input" aria-label="To date">
            <button class="btn btn-primary btn-sm">Apply</button>
        </form>
        <a class="btn btn-secondary"
            href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'excel'])) }}">Excel</a>
        <a class="btn btn-secondary" target="_blank"
            href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'pdf'])) }}">PDF</a>
    </div>
</div>
<div class="analytics-summary-grid">
    <div class="stat-box">
        <div class="label">Total sales</div>
        <div class="num">{{ number_format($units) }}</div>
    </div>
    <div class="stat-box">
        <div class="label">Total revenue</div>
        <div class="num">₹{{ number_format($netRevenue, 0) }}</div>
    </div>
    <div class="stat-box">
        <div class="label">Gross sales</div>
        <div class="num">₹{{ number_format($revenue, 0) }}</div>
    </div>
    <div class="stat-box">
        <div class="label">Sales growth</div>
        <div class="num" style="color:{{ $salesGrowth < 0 ? '#c43d3d' : '#078657' }}">
            {{ $salesGrowth >= 0 ? '+' : '' }}{{ number_format($salesGrowth, 1) }}%</div>
    </div>
</div>
<div class="analytics-two-column">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Sales &amp; Revenue Trend</h3>
                <p>Units and revenue for the selected period</p>
            </div>
            <div class="chart-legend">
                <span class="navy-dot"></span> Units <span class="gold-dot"></span> Revenue
            </div>
        </div>
        <div class="analytics-chart-scroll"><svg class="publisher-sales-line"
                viewBox="0 0 {{ $trendWidth }} {{ $trendHeight }}" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="publisherRevenueFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#eda13a" stop-opacity=".28" />
                        <stop offset="100%" stop-color="#eda13a" stop-opacity="0" />
                    </linearGradient>
                </defs>
                @foreach([0, .5, 1] as $grid)
                    <line x1="{{ $trendPad }}" y1="{{ $trendPad + ($trendPlotHeight * $grid) }}"
                        x2="{{ $trendWidth - $trendPad }}" y2="{{ $trendPad + ($trendPlotHeight * $grid) }}"
                class="grid-line" />@endforeach
                @if($series->count() > 1)
                    <polygon points="{{ $revenueArea }}" class="revenue-area" />
                    <polyline points="{{ $revenueLine }}" class="revenue-line" />
                <polyline points="{{ $unitsLine }}" class="units-line" />@endif
                @foreach($series as $index => $point)
                @php($x = $trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2))
                @php($revenueX = $series->count() === 1 ? $x - 28 : $x)
                @php($unitsX = $series->count() === 1 ? $x + 28 : $x)
                @php($revenueY = $trendPad + $trendPlotHeight - ($point['revenue'] / $maxRevenue * $trendPlotHeight))
                @php($unitsY = $trendPad + $trendPlotHeight - ($point['units'] / $maxUnits * $trendPlotHeight))
                @if($series->count() === 1)
                    <line x1="{{ $x }}" y1="{{ $trendPad + 18 }}" x2="{{ $x }}" y2="{{ $trendPad + $trendPlotHeight }}"
                class="single-guide" />@endif
                <circle cx="{{ $revenueX }}" cy="{{ $revenueY }}" r="{{ $series->count() === 1 ? 7 : 4 }}"
                    class="revenue-dot">
                    <title>{{ $point['label'] }}: ₹{{ number_format($point['revenue'], 2) }}</title>
                </circle>
                <circle cx="{{ $unitsX }}" cy="{{ $unitsY }}" r="{{ $series->count() === 1 ? 6 : 3.5 }}"
                    class="units-dot">
                    <title>{{ $point['label'] }}: {{ $point['units'] }} units</title>
                </circle>
                <text x="{{ $revenueX }}" y="{{ max(14, $revenueY - 12) }}" text-anchor="middle"
                    class="value-label">₹{{ number_format($point['revenue'], 0) }}</text>
                @if($series->count() === 1)<text x="{{ $unitsX }}" y="{{ max(14, $unitsY - 12) }}" text-anchor="middle"
                class="value-label">{{ $point['units'] }} unit{{ $point['units'] === 1 ? '' : 's' }}</text>@endif
                <text x="{{ $x }}" y="{{ $trendHeight - 5 }}" text-anchor="middle">{{ $point['label'] }}</text>
                @endforeach
            </svg></div>
    </section>
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Order distribution</h3>
                <p>Status of orders containing your books</p>
            </div>
        </div>
        <div class="analytics-donut-wrap">
            <div class="status-donut"
                style="background:conic-gradient({{ implode(',', $gradient) ?: '#e6ebf2 0 100%' }})">
                <span><b>{{ $statusMix->sum() }}</b>Orders</span>
            </div>
            <div class="analytics-status-list">
                @forelse($statusMix as $status => $count)
                    <div>
                        <i style="background:{{ $colors[$status] ?? '#8a96a8' }}"></i>
                        <span>{{ str($status)->replace('_', ' ')->title() }}</span>
                        <b>{{ $count }}</b>
                    </div>
                @empty
                    <p>No orders in this period.</p>
                @endforelse
            </div>
        </div>
    </section>
</div>
<section class="dashboard-card analytics-top-books">
    <div class="card-heading">
        <div>
            <h3>Top-selling books</h3>
            <p>Ranked using real confirmed sales</p>
        </div>
    </div>
    <div class="analytics-table-wrap">
        <table class="a-table publisher-analytics-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Book</th>
                    <th>ISBN</th>
                    <th>Orders</th>
                    <th>Units sold</th>
                    <th>Gross sales</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topBooks as $book)
                    <tr>
                        <td><span class="rank-pill">{{ $loop->iteration }}</span></td>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->isbn ?: '—' }}</td>
                        <td>{{ $book->orders }}</td>
                        <td>{{ $book->units }}</td>
                        <td>₹{{ number_format($book->revenue, 0) }}</td>
                </tr>@empty<tr>
                        <td colspan="6" class="empty-state">No completed sales in this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>

<div class="analytics-two-column">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Most viewed books</h3>
                <p>Customer catalogue interest</p>
            </div>
        </div>
        <table class="a-table">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Views</th>
                </tr>
            </thead>
            <tbody>@forelse($mostViewedBooks as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td><strong>{{ number_format($book->view_count) }}</strong></td>
            </tr>@empty<tr>
                    <td colspan="2">No book views recorded.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </section>
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Least-selling books</h3>
                <p>Titles needing attention</p>
            </div>
        </div>
        <table class="a-table">
            <thead>
                <tr>
                    <th>Book</th>
                    <th>Units sold</th>
                </tr>
            </thead>
            <tbody>@forelse($leastSellingBooks as $book)
                <tr>
                    <td>{{ $book->title }}</td>
                    <td><strong>{{ number_format($book->units) }}</strong></td>
            </tr>@empty<tr>
                    <td colspan="2">No books found.</td>
                </tr>@endforelse
            </tbody>
        </table>
    </section>
</div>

<div class="analytics-two-column">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Inventory Status</h3>
                <p>Current catalogue stock health</p>
            </div>
        </div>
        <div class="analytics-metric-grid">
            <div><span>Current stock</span><strong>{{ number_format($inventory->current_stock) }}</strong></div>
            <div><span>Low stock</span><strong>{{ number_format($inventory->low_stock) }}</strong></div>
            <div><span>Out of stock</span><strong>{{ number_format($inventory->out_of_stock) }}</strong></div>
            <div><span>Stock movement</span><strong>{{ number_format($stockMovement) }}</strong></div>
        </div>
        <div class="analytics-inventory-bars"><i
                style="--value:{{ max(3, min(100, $inventory->current_stock)) }}%"><span>Current</span></i><i
                class="warning"
                style="--value:{{ max(3, min(100, $inventory->low_stock * 10)) }}%"><span>Low</span></i><i
                class="danger"
                style="--value:{{ max(3, min(100, $inventory->out_of_stock * 10)) }}%"><span>Out</span></i></div>
    </section>
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Order Analytics</h3>
                <p>Order status for the selected period</p>
            </div>
        </div>
        <div class="analytics-metric-grid">
            <div><span>Total orders</span><strong>{{ $orderAnalytics['total'] }}</strong></div>
            <div><span>Pending</span><strong>{{ $orderAnalytics['pending'] }}</strong></div>
            <div><span>Completed</span><strong>{{ $orderAnalytics['completed'] }}</strong></div>
            <div><span>Cancelled</span><strong>{{ $orderAnalytics['cancelled'] }}</strong></div>
        </div>
    </section>
</div>

<div class="analytics-two-column">
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Customer Analytics</h3>
                <p>Customers purchasing your books</p>
            </div>
        </div>
        <div class="analytics-metric-grid analytics-metric-grid-3">
            <div><span>Total customers</span><strong>{{ $customerAnalytics['total'] }}</strong></div>
            <div><span>New customers</span><strong>{{ $customerAnalytics['new'] }}</strong></div>
            <div><span>Repeat customers</span><strong>{{ $customerAnalytics['repeat'] }}</strong></div>
        </div>
    </section>
    <section class="dashboard-card">
        <div class="card-heading">
            <div>
                <h3>Reports</h3>
                <p>Download detailed publisher reports</p>
            </div>
        </div>
        <div class="publisher-report-links">
            @foreach(['sales' => 'Sales Report', 'inventory' => 'Inventory Report', 'orders' => 'Order Report', 'books' => 'Book Performance Report'] as $report => $label)<a
                class="btn btn-outline"
                href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'excel', 'report' => $report])) }}">{{ $label }}
                · Excel</a><a class="btn btn-outline" target="_blank"
            href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'pdf', 'report' => $report])) }}">PDF</a>@endforeach
        </div>
    </section>
</div>
@endsection