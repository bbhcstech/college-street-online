@extends('layouts.dashboard')
@php
    $homeRoute = route('admin.dashboard');
    $brandLabel = 'Admin Console';
    $crumb = 'Overview';
    $logoutRoute = route('admin.logout');
    $maxOrders = max(1, $weeklyTrend->max('orders'));
    $maxRevenue = max(1, $weeklyTrend->max('revenue'));
    $statusTotal = max(1, $statusMix->sum());
    $statusColors = ['pending_payment' => '#EDA13A', 'confirmed' => '#2B5D85', 'processing' => '#7352B4', 'packed' => '#C56824', 'shipped' => '#2684BE', 'delivered' => '#1F9D6C', 'completed' => '#37B77A', 'cancelled' => '#D64545', 'return_requested' => '#E07C2D', 'returned' => '#718096'];
    $cursor = 0;
    $gradient = [];
    foreach ($statusMix as $status => $count) {
        $next = $cursor + ($count / $statusTotal * 100);
        $color = $statusColors[$status] ?? '#8A96A8';
        $gradient[] = "$color {$cursor}% {$next}%";
        $cursor = $next;
    }
@endphp
@section('title', 'Dashboard')
@section('nav')@include('admin.partials.nav', ['active' => 'dashboard'])@endsection
@section('content')
    <div class="dashboard-welcome">
        <div><span>{{ now()->format('l, d F Y') }}</span>
            <h2>Welcome back, {{ auth()->user()->name }}</h2>
            <p>Here is what is happening across College Street Online.</p>
        </div><a href="{{ route('admin.analytics.index') }}" class="btn btn-primary">View full analytics →</a>
    </div>
    <div class="a-grid a-grid-7 dashboard-stats">
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <span class="trend-chip trend-up">All time</span>
            </div>
            <div class="num">₹{{ number_format($totalSales, 0) }}</div>
            <div class="label">Total sales</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon blue">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                </div>
                <span class="trend-chip trend-up">Today</span>
            </div>
            <div class="num">₹{{ number_format($todaySales, 0) }}</div>
            <div class="label">Today's sale</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon purple">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                </div>
                <span class="trend-chip trend-up">All time</span>
            </div>
            <div class="num">{{ number_format($totalOrders) }}</div>
            <div class="label">Total orders</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon amber">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <span class="trend-chip {{ $pendingOrders ? 'trend-down' : 'trend-up' }}">Pending</span>
            </div>
            <div class="num">{{ number_format($pendingOrders) }}</div>
            <div class="label">Pending orders</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon gold">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <span class="trend-chip {{ $pendingPayments ? 'trend-down' : 'trend-up' }}">Action</span>
            </div>
            <div class="num">{{ number_format($pendingPayments) }}</div>
            <div class="label">Pending payments</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>
                </div>
                <span class="trend-chip trend-up">Live</span>
            </div>
            <div class="num">{{ number_format($bookCount) }}</div>
            <div class="label">Active books</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon red">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <span class="trend-chip {{ $lowStockCount ? 'trend-down' : 'trend-neutral' }}">Alert</span>
            </div>
            <div class="num">{{ number_format($lowStockCount) }}</div>
            <div class="label">Low stock titles</div>
        </div>
    </div>

    <!-- Sales Analytics Section -->
    <div class="sales-analytics-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Sales Analytics</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Real-time performance metrics</span>
        </div>
        <div class="a-grid a-grid-2" style="grid-template-columns: 3fr 2fr; gap: 20px;">
            <!-- Sales Trend — Line Graph -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Sales Trend</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Daily sales revenue trajectory</p>
                    </div>
                </div>
                <div style="position: relative; height: 250px;">
                    <canvas id="salesTrendLineChart"></canvas>
                </div>
            </div>

            <!-- Sales by Country — Bar Graph -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Sales by Country</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Revenue breakdown by country</p>
                    </div>
                </div>
                <div style="position: relative; height: 250px;">
                    <canvas id="salesByCountryBarChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Sales Trend Line Chart
        const trendCtx = document.getElementById('salesTrendLineChart').getContext('2d');
        const trendGradient = trendCtx.createLinearGradient(0, 0, 0, 250);
        trendGradient.addColorStop(0, 'rgba(31, 157, 108, 0.25)');
        trendGradient.addColorStop(1, 'rgba(31, 157, 108, 0.0)');

        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($weeklyTrend->pluck('label')) !!},
                datasets: [{
                    label: 'Sales (₹)',
                    data: {!! json_encode($weeklyTrend->pluck('revenue')) !!},
                    borderColor: '#1F9D6C',
                    backgroundColor: trendGradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#1F9D6C',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Sales: ₹' + ctx.raw.toLocaleString('en-IN'); }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { size: 11 },
                            callback: function(v) { return '₹' + v; }
                        }
                    }
                }
            }
        });

        // Sales by Country Bar Chart
        const countryCtx = document.getElementById('salesByCountryBarChart').getContext('2d');
        new Chart(countryCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($salesByCountry->keys()) !!},
                datasets: [{
                    label: 'Revenue (₹)',
                    data: {!! json_encode($salesByCountry->values()) !!},
                    backgroundColor: [
                        '#2684BE', '#7352B4', '#1F9D6C', '#E07C2D', '#D9822B', '#D64545'
                    ],
                    borderRadius: 6,
                    maxBarThickness: 36
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Revenue: ₹' + ctx.raw.toLocaleString('en-IN'); }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { size: 11 },
                            callback: function(v) { return '₹' + v; }
                        }
                    }
                }
            }
        });
    });
    </script>
    <!-- Order Analytics Section -->
    <div class="order-analytics-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Order Analytics</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Volume & status distribution breakdown</span>
        </div>
        <div class="a-grid a-grid-2" style="grid-template-columns: 3fr 2fr; gap: 20px;">
            <!-- Orders Trend — Line Graph -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Orders Trend</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Daily order volume trajectory</p>
                    </div>
                </div>
                <div style="position: relative; height: 250px;">
                    <canvas id="ordersTrendLineChart"></canvas>
                </div>
            </div>

            <!-- Order Status — Pie/Donut Graph -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Order Status</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">All-time status distribution</p>
                    </div>
                </div>
                <div style="position: relative; height: 250px;">
                    <canvas id="orderStatusDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    @php
        $formattedStatusLabels = $statusMix->keys()->map(fn($s) => ucfirst(str_replace('_', ' ', $s)));
        $statusCounts = $statusMix->values();
        if ($statusMix->isEmpty()) {
            $formattedStatusLabels = collect(['No Orders']);
            $statusCounts = collect([0]);
        }
    @endphp

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Orders Trend Line Chart
        const ordersCtx = document.getElementById('ordersTrendLineChart').getContext('2d');
        const ordersGradient = ordersCtx.createLinearGradient(0, 0, 0, 250);
        ordersGradient.addColorStop(0, 'rgba(115, 82, 180, 0.25)');
        ordersGradient.addColorStop(1, 'rgba(115, 82, 180, 0.0)');

        new Chart(ordersCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($weeklyTrend->pluck('label')) !!},
                datasets: [{
                    label: 'Orders',
                    data: {!! json_encode($weeklyTrend->pluck('orders')) !!},
                    borderColor: '#7352B4',
                    backgroundColor: ordersGradient,
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointBackgroundColor: '#7352B4',
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Orders: ' + ctx.raw; }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 11 } } },
                    y: {
                        beginAtZero: true,
                        precision: 0,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: {
                            font: { size: 11 },
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // Order Status Donut Chart
        const statusCtx = document.getElementById('orderStatusDonutChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: {!! json_encode($formattedStatusLabels) !!},
                datasets: [{
                    data: {!! json_encode($statusCounts) !!},
                    backgroundColor: [
                        '#EDA13A', '#2B5D85', '#7352B4', '#C56824', '#2684BE', '#1F9D6C', '#37B77A', '#D64545', '#E07C2D'
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '68%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {
                            boxWidth: 12,
                            padding: 10,
                            font: { size: 11 }
                        }
                    }
                }
            }
        // Top Selling Books Bar Chart
        const topBooksCtx = document.getElementById('topSellingBooksBarChart').getContext('2d');
        new Chart(topBooksCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($topSellingBooks->pluck('title')->map(fn($t) => Str::limit($t, 20))) !!},
                datasets: [{
                    label: 'Copies Sold',
                    data: {!! json_encode($topSellingBooks->pluck('total_sold')) !!},
                    backgroundColor: '#1F9D6C',
                    borderRadius: 6,
                    maxBarThickness: 28
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) { return ' Sold: ' + ctx.raw + ' copies'; }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        precision: 0,
                        grid: { color: 'rgba(0,0,0,0.05)' },
                        ticks: { font: { size: 11 }, stepSize: 1 }
                    },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    });
    </script>

    <!-- Inventory Section -->
    <div class="inventory-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Inventory</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Stock monitoring & top selling titles</span>
        </div>
        <div class="a-grid a-grid-2" style="grid-template-columns: 3fr 2fr; gap: 20px;">
            <!-- Top Selling Books — Bar Graph -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Top Selling Books</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Highest volume titles sold</p>
                    </div>
                </div>
                <div style="position: relative; height: 250px;">
                    <canvas id="topSellingBooksBarChart"></canvas>
                </div>
            </div>

            <!-- Low Stock Alerts -->
            <div class="a-card" style="padding: 20px;">
                <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Low Stock Alerts</h4>
                        <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Titles requiring re-stocking</p>
                    </div>
                    <a href="{{ route('admin.inventory.index', ['stock' => 'low']) }}" style="font-size: 0.78rem; color: var(--a-primary); font-weight: 600; text-decoration: none;">Manage Inventory →</a>
                </div>
                <div class="dashboard-table-scroll">
                    <table class="a-table" style="width: 100%; font-size: 0.82rem;">
                        <thead>
                            <tr>
                                <th>Book Title</th>
                                <th>Stock</th>
                                <th>Threshold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lowStockBooks as $book)
                                <tr>
                                    <td><a href="{{ route('admin.books.edit', $book) }}" style="font-weight: 600; color: var(--a-text);">{{ Str::limit($book->title, 24) }}</a></td>
                                    <td><span class="badge badge-danger" style="font-weight: 700;">{{ $book->inventory->quantity ?? 0 }} left</span></td>
                                    <td>{{ $book->inventory->low_stock_threshold ?? 5 }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" style="text-align: center; color: var(--a-text-muted); padding: 24px 12px;">No low stock alerts. All stock levels are healthy!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Operations Section -->
    <div class="order-operations-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Order Operations</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Recent orders & payment verifications</span>
        </div>
        <div class="a-grid a-grid-2 dashboard-bottom-grid" style="grid-template-columns: 3fr 2fr; gap: 20px;">
            <div class="a-card">
                <div class="a-card-head dashboard-card-title">
                    <div>
                        <h3>Recent Orders</h3>
                        <p>Latest customer activity</p>
                    </div><a href="{{ route('admin.orders.index') }}">View all →</a>
                </div>
            <div class="dashboard-table-scroll">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($recentOrders as $order)
                        <tr>
                            <td><a href="{{ route('admin.orders.show', $order) }}"><strong>#CSO{{ $order->id }}</strong></a>
                            </td>
                            <td>{{ $order->customer->name ?? '—' }}</td>
                            <td>{{ $order->currency_symbol }}{{ number_format($order->total_amount, 2) }}</td>
                            <td><span
                                    class="badge {{ $order->status === 'cancelled' ? 'badge-danger' : ($order->status === 'completed' ? 'badge-success' : 'badge-info') }}">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</span>
                            </td>
                    </tr>@empty<tr>
                            <td colspan="4">No orders yet.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Payment verification</h3>
                    <p>Pending manual payments</p>
                </div>
            </div>
            <div class="dashboard-table-scroll">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>UTR</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>@forelse($recentPayments as $payment)
                        <tr>
                            <td><strong>#CSO{{ $payment->order_id }}</strong><small>{{ $payment->order->currency_symbol }}{{ number_format($payment->order->total_amount, 2) }}</small>
                            </td>
                            <td>{{ $payment->utr_number }}</td>
                            <td><a href="{{ route('admin.orders.show', $payment->order) }}"
                                    class="btn btn-outline btn-sm">Review</a></td>
                    </tr>@empty<tr>
                            <td colspan="3">No pending verifications.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

    <!-- Customers Section -->
    <div class="customers-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Customers</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Registered customer accounts</span>
        </div>
        <div class="a-card" style="padding: 20px;">
            <div class="a-card-head" style="margin-bottom: 14px; border: none; padding: 0; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: var(--a-text);">Recent Customers</h4>
                    <p style="font-size: 0.78rem; color: var(--a-text-muted); margin: 2px 0 0 0;">Latest user registrations</p>
                </div>
            </div>
            <div class="dashboard-table-scroll">
                <table class="a-table" style="width: 100%; font-size: 0.85rem;">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Total Orders</th>
                            <th>Joined Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentCustomers as $customer)
                            <tr>
                                <td><strong style="color: var(--a-text);">{{ $customer->name }}</strong></td>
                                <td>{{ $customer->email }}</td>
                                <td><span class="badge badge-info">{{ $customer->orders_count }} orders</span></td>
                                <td>{{ $customer->created_at->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align: center; color: var(--a-text-muted); padding: 20px;">No registered customers yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Quick Actions Section -->
    <div class="quick-actions-section" style="margin-bottom: 28px;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
            <h3 style="font-family: var(--font-heading); font-size: 1.15rem; font-weight: 700; color: var(--a-text); margin: 0;">Quick Actions</h3>
            <span style="font-size: 0.78rem; color: var(--a-text-muted);">Shortcuts for common admin tasks</span>
        </div>
        <div class="a-grid" style="grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 14px;">
            <a href="{{ route('admin.books.create') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(31, 157, 108, 0.12); color: #1F9D6C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Add Book</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">New catalog title</span>
                </div>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(115, 82, 180, 0.12); color: #7352B4; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Manage Orders</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">View & process orders</span>
                </div>
            </a>

            <a href="{{ route('admin.orders.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(237, 161, 58, 0.14); color: #D9822B; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Verify Payment</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Review manual UTRs</span>
                </div>
            </a>

            <a href="{{ route('admin.books.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(214, 69, 69, 0.12); color: #D64545; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Update Stock</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Manage inventory levels</span>
                </div>
            </a>

            <a href="{{ route('admin.countries.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(38, 132, 190, 0.12); color: #2684BE; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Country Pricing</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Regional price markups</span>
                </div>
            </a>

            <a href="{{ route('admin.currencies.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(115, 82, 180, 0.12); color: #7352B4; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Currency Rates</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Exchange rates & symbols</span>
                </div>
            </a>

            <a href="{{ route('admin.countries.index') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(224, 124, 45, 0.12); color: #E07C2D; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Shipping</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Shipping rates & rules</span>
                </div>
            </a>

            <a href="{{ route('admin.payment-settings.edit') }}" class="a-card quick-action-card" style="padding: 16px; text-decoration: none; display: flex; align-items: center; gap: 12px; transition: all 0.2s ease;">
                <div style="width: 38px; height: 38px; border-radius: 10px; background: rgba(31, 157, 108, 0.12); color: #1F9D6C; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>
                </div>
                <div>
                    <strong style="display: block; font-size: 0.88rem; color: var(--a-text); font-weight: 600;">Payment Settings</strong>
                    <span style="font-size: 0.73rem; color: var(--a-text-muted);">Gateway & bank details</span>
                </div>
            </a>
        </div>
    </div>
@endsection