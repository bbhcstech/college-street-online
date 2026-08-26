@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Overview';
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
@endphp
@section('title', 'Analytics & Reports')
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}" class="nav-link"><span
                class="nav-icon">▣</span><span>Dashboard</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Catalogue</div><a href="{{ route('publisher.books.index') }}" class="nav-link"><span
                class="nav-icon">📖</span><span>My Books</span></a><a href="{{ route('publisher.inventory.index') }}"
            class="nav-link"><span class="nav-icon">📦</span><span>Inventory</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Marketing</div><a href="{{ route('publisher.coupons.index') }}" class="nav-link"><span
                class="nav-icon">🏷</span><span>Coupons & Offers</span></a>
    </div>
    <div class="nav-group">
        <div class="nav-group-title">Sales</div><a href="{{ route('publisher.orders.index') }}" class="nav-link"><span
                class="nav-icon">🚚</span><span>Orders</span></a>
    </div>
    <div class="nav-group"><div class="nav-group-title">Reports</div><a href="{{ route('publisher.analytics.index') }}" class="nav-link active"><span class="nav-icon">&#128200;</span><span>Analytics & Reports</span></a></div>
@endsection
@section('content')
    <style>
        html.dark .dashboard-card { background: var(--a-surface); color: var(--a-text); border-color: var(--a-border); }
        html.dark .analytics-toolbar { background: var(--a-surface); border-color: var(--a-border); }
        html.dark .analytics-toolbar p,
        html.dark .card-heading p,
        html.dark .chart-legend,
        html.dark .analytics-bar-group small { color: var(--a-text-muted); }
        html.dark .analytics-actions select { background: var(--a-surface-alt); color: var(--a-text); border-color: var(--a-border); }
        html.dark .status-donut::before { background: var(--a-surface); }
        html.dark .analytics-chart { border-color: var(--a-border); }
        html.dark .publisher-analytics-table th,
        html.dark .publisher-analytics-table td { color: var(--a-text); border-color: var(--a-border); }
        html.dark .rank-pill { background: rgba(237, 161, 58, .18); color: #f1ad4d; }
    </style>
    <div class="analytics-toolbar">
        <div>
            <h2>Sales analytics</h2>
            <p>Only orders and books from your publisher account are included.</p>
        </div>
        <div class="analytics-actions">
            <form method="GET"><select name="period" onchange="this.form.submit()">
                    <option value="week" @selected($period === 'week')>Weekly</option>
                    <option value="month" @selected($period === 'month')>Monthly</option>
                    <option value="year" @selected($period === 'year')>Yearly</option>
                </select></form>
            <a class="btn btn-secondary"
                href="{{ route('publisher.analytics.export', ['type' => 'csv', 'period' => $period]) }}">CSV</a>
            <a class="btn btn-secondary"
                href="{{ route('publisher.analytics.export', ['type' => 'excel', 'period' => $period]) }}">Excel</a>
            <a class="btn btn-secondary" target="_blank"
                href="{{ route('publisher.analytics.export', ['type' => 'print', 'period' => $period]) }}">Print / PDF</a>
        </div>
    </div>
    <div class="analytics-summary-grid">
        <div class="stat-box">
            <div class="label">Gross sales</div>
            <div class="num">₹{{ number_format($revenue, 0) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Orders</div>
            <div class="num">{{ number_format($orders) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Units sold</div>
            <div class="num">{{ number_format($units) }}</div>
        </div>
        <div class="stat-box">
            <div class="label">Average order value</div>
            <div class="num">₹{{ number_format($averageOrder, 0) }}</div>
        </div>
    </div>
    <div class="analytics-two-column">
        <section class="dashboard-card">
            <div class="card-heading">
                <div>
                    <h3>Sales performance</h3>
                    <p>Units and revenue for the selected period</p>
                </div>
                <div class="chart-legend"><span class="navy-dot"></span> Units <span class="gold-dot"></span> Revenue</div>
            </div>
            <div class="analytics-chart-scroll">
                <div class="analytics-chart" style="--points:{{ $series->count() }}">
                    @foreach($series as $point)
                        <div class="analytics-bar-group">
                            <div class="analytics-bars"><i class="revenue"
                                    style="height:{{ $point['revenue'] ? max(4, $point['revenue'] / $maxRevenue * 100) : 0 }}%"></i><i
                                    class="units"
                                    style="height:{{ $point['units'] ? max(4, $point['units'] / $maxUnits * 100) : 0 }}%"></i></div>
                            <strong>{{ $point['units'] }}</strong><small>{{ $point['label'] }}</small>
                    </div>@endforeach
                </div>
            </div>
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
                    <span><b>{{ $statusMix->sum() }}</b>Orders</span></div>
                <div class="analytics-status-list">@forelse($statusMix as $status => $count)
                    <div><i
                            style="background:{{ $colors[$status] ?? '#8a96a8' }}"></i><span>{{ str($status)->replace('_', ' ')->title() }}</span><b>{{ $count }}</b>
                </div>@empty<p>No orders in this period.</p>@endforelse
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
                <tbody>@forelse($topBooks as $book)
                    <tr>
                        <td><span class="rank-pill">{{ $loop->iteration }}</span></td>
                        <td><strong>{{ $book->title }}</strong></td>
                        <td>{{ $book->isbn ?: '—' }}</td>
                        <td>{{ $book->orders }}</td>
                        <td>{{ $book->units }}</td>
                        <td>₹{{ number_format($book->revenue, 0) }}</td>
                </tr>@empty<tr>
                        <td colspan="6" class="empty-state">No completed sales in this period.</td>
                    </tr>@endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
