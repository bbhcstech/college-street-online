@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Overview';
    $logoutRoute = route('admin.logout');
    $maxRevenue = max(1, (float) $revenueTrend->max('revenue'));
    $maxUnits = max(1, (int) $topBooks->max('units'));
    $statusTotal = max(1, (int) $statusCounts->sum());
    $periodLabel = ['7' => 'Last 7 days', '30' => 'Last 30 days', '90' => 'Last 90 days', '365' => 'Last 12 months', 'all' => 'All time', 'custom' => 'Custom range'][$period] ?? 'Custom range';
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
    <form method="GET" class="analytics-period-form" style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <label for="analytics-period">Reporting period</label>
        <select id="analytics-period" name="period" class="a-select" onchange="this.form.submit()">
            <option value="7" @selected($period === '7')>Last 7 days</option>
            <option value="30" @selected($period === '30')>Last 30 days</option>
            <option value="90" @selected($period === '90')>Last 90 days</option>
            <option value="365" @selected($period === '365')>Last 12 months</option>
            <option value="all" @selected($period === 'all')>All time</option>
            <option value="custom" @selected($period === 'custom')>Custom Range</option>
        </select>
        @if($period === 'custom')
            <input type="date" name="date_from" value="{{ $dateFrom }}" class="a-input" style="width: 140px; height: 34px; padding: 0 8px;" title="Date From">
            <span style="color: #666; font-size: 0.85rem;">to</span>
            <input type="date" name="date_to" value="{{ $dateTo }}" class="a-input" style="width: 140px; height: 34px; padding: 0 8px;" title="Date To">
            <button class="btn btn-primary btn-sm" style="height: 34px; padding: 0 12px;">Apply</button>
        @endif
    </form>
</div>

<div class="a-grid a-grid-4 analytics-kpis">
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
    <div class="stat-box analytics-kpi">
        <div class="stat-top">
            <div class="stat-icon blue">👥</div><span class="analytics-period-chip">Users</span>
        </div>
        <div class="label">Total customers</div>
        <div class="num">{{ number_format($totalCustomers) }}</div><small>Registered customer accounts</small>
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
        <div style="padding: 16px; position: relative; height: 260px;">
            <canvas id="revenueLineChart"></canvas>
        </div>
    </div>
    <div class="a-card">
        <div class="a-card-head dashboard-card-title">
            <div>
                <h3>Order status distribution</h3>
                <p>{{ number_format($statusCounts->sum()) }} orders in this period</p>
            </div>
        </div>
        <div style="padding: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; height: 260px;">
            <div style="width: 200px; height: 200px; position: relative; margin: 0 auto;">
                <canvas id="orderStatusDonutChart"></canvas>
            </div>
            <div class="analytics-status-list" style="flex: 1; min-width: 160px; max-height: 220px; overflow-y: auto;">
                @foreach($statusLabels as $status => $label)
                    @php($count = (int) ($statusCounts[$status] ?? 0))
                    <div>
                        <i style="background:{{ $statusColors[$status] }}"></i>
                        <span>{{ $label }}</span>
                        <strong>{{ $count }}</strong>
                        <small>{{ number_format($count / $statusTotal * 100, 0) }}%</small>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-top: 24px; margin-bottom: 24px;">
    <!-- Card 1: Sales by Country -->
    <div class="a-card">
        <div class="a-card-head dashboard-card-title">
            <div>
                <h3>Sales by Country</h3>
                <p>Revenue &amp; orders grouped by destination</p>
            </div>
            <span class="badge badge-primary">Global</span>
        </div>
        <div style="padding: 16px; position: relative; height: 260px;">
            <canvas id="salesByCountryChart"></canvas>
        </div>
    </div>

    <!-- Card 2: Top Selling Books (Horizontal Bar Graph) -->
    <div class="a-card">
        <div class="a-card-head dashboard-card-title">
            <div>
                <h3>Top-Selling Books</h3>
                <p>Ranked by units sold during {{ strtolower($periodLabel) }}</p>
            </div>
            <a href="{{ route('admin.books.index') }}" style="font-size: 0.82rem; color: #6366f1; text-decoration: none; font-weight: 600;">Manage books →</a>
        </div>
        <div style="padding: 16px; position: relative; height: 260px;">
            <canvas id="topBooksBarChart"></canvas>
        </div>
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
    </div>@endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('revenueLineChart');
    if (!ctx) return;

    const labels = @json($revenueTrend->pluck('period'));
    const data = @json($revenueTrend->pluck('revenue'));
    const orders = @json($revenueTrend->pluck('orders'));

    const chartCtx = ctx.getContext('2d');
    const gradient = chartCtx.createLinearGradient(0, 0, 0, 240);
    gradient.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
    gradient.addColorStop(1, 'rgba(16, 185, 129, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels.length ? labels : ['No Data'],
            datasets: [{
                label: 'Revenue (₹)',
                data: data.length ? data : [0],
                borderColor: '#10b981',
                borderWidth: 2.5,
                backgroundColor: gradient,
                fill: true,
                tension: 0.35,
                pointBackgroundColor: '#10b981',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 12, weight: 'bold' },
                    bodyFont: { size: 13 },
                    padding: 10,
                    displayColors: false,
                    callbacks: {
                        label: function(context) {
                            const val = context.parsed.y || 0;
                            const idx = context.dataIndex;
                            const ordCount = orders[idx] || 0;
                            return ['Revenue: ₹' + val.toLocaleString(), 'Orders: ' + ordCount];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 }, color: '#64748b' }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: {
                        font: { size: 11 },
                        color: '#64748b',
                        callback: function(value) {
                            return '₹' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Donut Chart for Order Status Distribution
    const donutCtx = document.getElementById('orderStatusDonutChart');
    if (donutCtx) {
        const statusLabels = @json(array_values($statusLabels));
        const statusData = @json(array_map(fn($s) => (int)($statusCounts[$s] ?? 0), array_keys($statusLabels)));
        const statusColors = @json(array_values($statusColors));
        const totalStatus = @json($statusTotal);

        new Chart(donutCtx, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData.some(v => v > 0) ? statusData : [1],
                    backgroundColor: statusData.some(v => v > 0) ? statusColors : ['#e2e8f0'],
                    borderWidth: 2,
                    borderColor: '#ffffff',
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 10,
                        displayColors: true,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed || 0;
                                const pct = totalStatus > 0 ? Math.round((val / totalStatus) * 100) : 0;
                                return ' Orders: ' + val + ' (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // Bar Chart for Sales by Country
    const countryCtx = document.getElementById('salesByCountryChart');
    if (countryCtx) {
        const countryLabels = @json($salesByCountry->pluck('country_name'));
        const countrySales = @json($salesByCountry->pluck('sales'));
        const countryOrders = @json($salesByCountry->pluck('orders'));

        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: countryLabels.length ? countryLabels : ['No Data'],
                datasets: [{
                    label: 'Revenue (₹)',
                    data: countrySales.length ? countrySales : [0],
                    backgroundColor: '#3b82f6',
                    borderRadius: 4,
                    barThickness: 24
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.y || 0;
                                const idx = context.dataIndex;
                                const ords = countryOrders[idx] || 0;
                                return ['Revenue: ₹' + val.toLocaleString(), 'Orders: ' + ords];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 11 }, color: '#64748b' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 11 },
                            color: '#64748b',
                            callback: function(value) {
                                return '₹' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Horizontal Bar Chart for Top Selling Books
    const topBooksCtx = document.getElementById('topBooksBarChart');
    if (topBooksCtx) {
        const bookTitles = @json($topBooks->pluck('title'));
        const bookUnits = @json($topBooks->pluck('units'));
        const bookSales = @json($topBooks->pluck('sales'));

        new Chart(topBooksCtx, {
            type: 'bar',
            data: {
                labels: bookTitles.length ? bookTitles : ['No Data'],
                datasets: [{
                    label: 'Units Sold',
                    data: bookUnits.length ? bookUnits : [0],
                    backgroundColor: '#8b5cf6',
                    borderRadius: 4,
                    barThickness: 16
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { size: 12, weight: 'bold' },
                        bodyFont: { size: 13 },
                        padding: 10,
                        callbacks: {
                            label: function(context) {
                                const val = context.parsed.x || 0;
                                const idx = context.dataIndex;
                                const salesVal = bookSales[idx] || 0;
                                return ['Units Sold: ' + val, 'Total Sales: ₹' + Number(salesVal).toLocaleString()];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { size: 11 }, color: '#64748b', precision: 0 }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 11 },
                            color: '#64748b',
                            callback: function(value, index) {
                                const label = this.getLabelForValue(index);
                                return label.length > 20 ? label.substr(0, 20) + '...' : label;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection