@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Overview';
    $logoutRoute = route('publisher.logout');
    $maxUnits = max(1, $weeklyTrend->max('units'));
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
@section('nav')@include('publisher.partials.nav', ['active' => 'dashboard'])@endsection
@section('content')
    <div class="publisher-dashboard-welcome">
        <div>
            <span>{{ now()->format('l, d F Y') }}</span>
            <h2>Welcome back, {{ auth()->user()->name }}</h2>
            <p>Your catalogue, sales, fulfillment and inventory overview.</p>
        </div>
        <a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add new book</a>
    </div>

    <style>
        .publisher-kpi-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 22px;
        }
        @media (max-width: 1024px) {
            .publisher-kpi-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        @media (max-width: 560px) {
            .publisher-kpi-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>

    <div class="publisher-kpi-grid">
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon blue">📖</div>
                <span class="trend-chip trend-up">Catalogue</span>
            </div>
            <div class="num">{{ number_format($totalBooks) }}</div>
            <div class="label">Total Books</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">✓</div>
                <span class="trend-chip trend-up">Live</span>
            </div>
            <div class="num">{{ number_format($activeBooks) }}</div>
            <div class="label">Active Books</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon gold">₹</div>
                <span class="trend-chip trend-up">Sales</span>
            </div>
            <div class="num">₹{{ number_format($totalSales, 0) }}</div>
            <div class="label">Total Sales</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon purple">🛒</div>
                <span class="trend-chip trend-up">Orders</span>
            </div>
            <div class="num">{{ number_format($totalOrders) }}</div>
            <div class="label">Total Orders</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon blue">📦</div>
                <span class="trend-chip trend-up">Volume</span>
            </div>
            <div class="num">{{ number_format($unitsSold) }}</div>
            <div class="label">Units Sold</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">💳</div>
                <span class="trend-chip trend-up">Revenue</span>
            </div>
            <div class="num">₹{{ number_format($totalRevenue, 0) }}</div>
            <div class="label">Total Revenue</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon {{ $pendingOrders > 0 ? 'red' : 'green' }}">🚚</div>
                <span class="trend-chip {{ $pendingOrders > 0 ? 'trend-down' : 'trend-up' }}">{{ $pendingOrders > 0 ? 'Action' : 'Cleared' }}</span>
            </div>
            <div class="num">{{ number_format($pendingOrders) }}</div>
            <div class="label">Pending Orders</div>
        </div>

        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon {{ $lowStockCount > 0 ? 'amber' : 'green' }}">⚠️</div>
                <span class="trend-chip {{ $lowStockCount > 0 ? 'trend-down' : 'trend-up' }}">{{ $lowStockCount > 0 ? 'Restock' : 'Healthy' }}</span>
            </div>
            <div class="num">{{ number_format($lowStockCount) }}</div>
            <div class="label">Low Stock Books</div>
        </div>
    </div>

    @php
        $trendCount = count($monthlyTrends);
        $maxTrendRevenue = max(1, $monthlyTrends->max('revenue'));
        $maxTrendUnits = max(1, $monthlyTrends->max('units'));
        $svgW = 650;
        $svgH = 200;
        $padX = 40;
        $padY = 25;
        $plotW = $svgW - ($padX * 2);
        $plotH = $svgH - ($padY * 2);

        $revPoints = [];
        $unitPoints = [];
        $dots = [];

        foreach($monthlyTrends as $idx => $mt) {
            $x = $padX + ($trendCount > 1 ? ($idx / ($trendCount - 1)) * $plotW : $plotW / 2);
            $yRev = $padY + $plotH - (($mt['revenue'] / $maxTrendRevenue) * $plotH);
            $yUnit = $padY + $plotH - (($mt['units'] / $maxTrendUnits) * $plotH);

            $revPoints[] = round($x, 1) . ',' . round($yRev, 1);
            $unitPoints[] = round($x, 1) . ',' . round($yUnit, 1);
            $dots[] = [
                'x' => round($x, 1),
                'yRev' => round($yRev, 1),
                'yUnit' => round($yUnit, 1),
                'label' => $mt['label'],
                'short' => $mt['short_label'],
                'revenue' => $mt['revenue'],
                'units' => $mt['units']
            ];
        }
        $revPolyline = implode(' ', $revPoints);
        $unitPolyline = implode(' ', $unitPoints);
        $dashboardStatuses = collect([
            'Pending' => ($statusMix['pending_payment'] ?? 0) + ($statusMix['confirmed'] ?? 0),
            'Processing' => ($statusMix['processing'] ?? 0) + ($statusMix['packed'] ?? 0),
            'Shipped' => $statusMix['shipped'] ?? 0,
            'Delivered' => ($statusMix['delivered'] ?? 0) + ($statusMix['completed'] ?? 0),
        ]);
        $dashboardStatusTotal = max(1, $dashboardStatuses->sum());
        $statusCursor = 0;
        $statusPalette = ['#EDA13A', '#7352B4', '#2684BE', '#1F9D6C'];
        $statusGradient = [];
        foreach ($dashboardStatuses->values() as $index => $count) {
            $next = $statusCursor + (($count / $dashboardStatusTotal) * 100);
            $statusGradient[] = $statusPalette[$index] . " {$statusCursor}% {$next}%";
            $statusCursor = $next;
        }
    @endphp

    <style>
        .sales-analytics-grid {
            display: grid;
            grid-template-columns: 1.55fr 1fr;
            gap: 20px;
            margin-bottom: 22px;
        }
        @media (max-width: 1024px) {
            .sales-analytics-grid {
                grid-template-columns: 1fr;
            }
        }
        .sales-chart-wrapper {
            position: relative;
            width: 100%;
            height: 220px;
        }
        .sales-chart-svg {
            width: 100%;
            height: 100%;
            overflow: visible;
        }
        .chart-dot {
            transition: transform 0.2s ease, r 0.2s ease;
            cursor: pointer;
        }
        .chart-dot:hover {
            r: 6px;
        }
        .top-book-rank {
            width: 24px;
            height: 24px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.72rem;
            background: rgba(237, 161, 58, 0.15);
            color: #d9822b;
        }
        .top-book-rank.gold {
            background: var(--a-gold, #EDA13A);
            color: #0C242B;
        }
        .publisher-status-wrap { display:flex; align-items:center; justify-content:center; gap:30px; min-height:220px; }
        .publisher-status-donut { width:150px; height:150px; border-radius:50%; display:grid; place-items:center; position:relative; background:conic-gradient({{ implode(', ', $statusGradient) }}); }
        .publisher-status-donut::after { content:''; position:absolute; inset:30px; border-radius:50%; background:var(--a-surface); }
        .publisher-status-donut strong { position:relative; z-index:1; font-size:1.5rem; }
        .publisher-status-list { min-width:155px; }
        .publisher-status-list div { display:grid; grid-template-columns:9px 1fr auto; gap:8px; align-items:center; padding:7px 0; font-size:.75rem; }
        .publisher-status-list i { width:8px; height:8px; border-radius:50%; }
        .sales-overview-head { display:block !important; }
        .sales-overview-head .chart-legend { justify-content:flex-start !important; flex-wrap:wrap; gap:8px 18px !important; margin-top:10px; }
        .sales-overview-head .chart-legend span { width:auto !important; height:auto !important; white-space:nowrap; }
        @media (max-width: 560px) { .publisher-status-wrap { flex-direction:column; gap:14px; } }
    </style>

    <div class="sales-analytics-grid">
        <!-- 📈 Sales Overview — Line Graph -->
        <section class="a-card">
            <div class="a-card-head dashboard-card-title sales-overview-head" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
                <div>
                    <h3 style="margin:0;">Sales Overview</h3>
                    <p style="margin:3px 0 0; color:var(--a-text-muted); font-size:0.75rem;">Monthly Revenue &amp; Books Sold trend</p>
                </div>
                <div class="chart-legend" style="display:flex; align-items:center; gap:16px; font-size:0.76rem;">
                    <span style="display:flex; align-items:center; gap:6px;">
                        <i style="width:10px; height:10px; border-radius:50%; background:#EDA13A; display:inline-block;"></i>
                        <strong>Monthly Revenue</strong> (₹{{ number_format($monthlyRevenue, 0) }})
                    </span>
                    <span style="display:flex; align-items:center; gap:6px;">
                        <i style="width:10px; height:10px; border-radius:50%; background:#2684BE; display:inline-block;"></i>
                        <strong>Books Sold</strong> ({{ number_format($monthlyUnits) }} units)
                    </span>
                </div>
            </div>

            <div class="sales-chart-wrapper" style="margin-top:10px;">
                <svg class="sales-chart-svg" viewBox="0 0 {{ $svgW }} {{ $svgH }}" preserveAspectRatio="none">
                    <defs>
                        <linearGradient id="revenueGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#EDA13A" stop-opacity="0.25" />
                            <stop offset="100%" stop-color="#EDA13A" stop-opacity="0.0" />
                        </linearGradient>
                    </defs>

                    <!-- Horizontal Grid Lines -->
                    <line x1="{{ $padX }}" y1="{{ $padY }}" x2="{{ $svgW - $padX }}" y2="{{ $padY }}" stroke="var(--a-border, #E2E8F0)" stroke-dasharray="4 4" stroke-opacity="0.6" />
                    <line x1="{{ $padX }}" y1="{{ $padY + ($plotH / 2) }}" x2="{{ $svgW - $padX }}" y2="{{ $padY + ($plotH / 2) }}" stroke="var(--a-border, #E2E8F0)" stroke-dasharray="4 4" stroke-opacity="0.6" />
                    <line x1="{{ $padX }}" y1="{{ $padY + $plotH }}" x2="{{ $svgW - $padX }}" y2="{{ $padY + $plotH }}" stroke="var(--a-border, #E2E8F0)" stroke-opacity="0.9" />

                    @if($trendCount > 1)
                        <!-- Revenue Area Fill -->
                        <polygon points="{{ $dots[0]['x'] }},{{ $padY + $plotH }} {{ $revPolyline }} {{ $dots[$trendCount - 1]['x'] }},{{ $padY + $plotH }}" fill="url(#revenueGradient)" />
                        <!-- Revenue Line Graph -->
                        <polyline points="{{ $revPolyline }}" fill="none" stroke="#EDA13A" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
                        <!-- Units Sold Line Graph -->
                        <polyline points="{{ $unitPolyline }}" fill="none" stroke="#2684BE" stroke-width="2.5" stroke-dasharray="5 3" stroke-linecap="round" stroke-linejoin="round" />
                    @endif

                    <!-- Vertex Dots & Labels -->
                    @foreach($dots as $dot)
                        <!-- Revenue Dot -->
                        <circle cx="{{ $dot['x'] }}" cy="{{ $dot['yRev'] }}" r="4" fill="#EDA13A" stroke="#fff" stroke-width="2" class="chart-dot">
                            <title>{{ $dot['label'] }}: ₹{{ number_format($dot['revenue'], 0) }} Revenue</title>
                        </circle>
                        <!-- Units Dot -->
                        <circle cx="{{ $dot['x'] }}" cy="{{ $dot['yUnit'] }}" r="3.5" fill="#2684BE" stroke="#fff" stroke-width="1.5" class="chart-dot">
                            <title>{{ $dot['label'] }}: {{ number_format($dot['units']) }} Books Sold</title>
                        </circle>
                        <!-- X-Axis Month Label -->
                        <text x="{{ $dot['x'] }}" y="{{ $svgH - 2 }}" text-anchor="middle" font-size="10.5" fill="var(--a-text-muted, #718096)" font-family="sans-serif">{{ $dot['short'] }}</text>
                    @endforeach
                </svg>
            </div>
        </section>

        <!-- Order Status -->
        <section class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3 style="margin:0;">Order Status</h3>
                    <p style="margin:3px 0 0; color:var(--a-text-muted); font-size:0.75rem;">Current fulfillment distribution</p>
                </div>
            </div>
            <div class="publisher-status-wrap">
                <div class="publisher-status-donut"><strong>{{ $dashboardStatuses->sum() }}</strong></div>
                <div class="publisher-status-list">
                    @foreach($dashboardStatuses as $label => $count)
                        <div><i style="background:{{ $statusPalette[$loop->index] }}"></i><span>{{ $label }}</span><strong>{{ $count }}</strong></div>
                    @endforeach
                </div>
            </div>
        </section>
    </div>

    <!-- Top-Selling Books -->
    <section class="a-card publisher-top-books-card">
        <div class="a-card-head dashboard-card-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin:0;">Top-Selling Books</h3>
                <p style="margin:3px 0 0; color:var(--a-text-muted); font-size:0.75rem;">Best-performing titles by units sold and revenue</p>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('publisher.books.index') }}" class="btn btn-outline btn-sm">All Books &rarr;</a>
            </div>
        </div>

        <div class="dashboard-table-scroll">
            <table class="a-table publisher-top-books-table">
                <thead>
                    <tr>
                        <th>#</th><th>Book details</th><th>ISBN</th><th>Orders</th><th>Units sold</th><th>Stock</th><th style="text-align:right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($topSellingBooks as $index => $book)
                        <tr>
                            <td><span class="top-book-rank {{ $index === 0 ? 'gold' : '' }}">{{ $index + 1 }}</span></td>
                            <td>
                                <div class="publisher-top-book">
                                    @if($book->cover_image_url)
                                        <img src="{{ str_starts_with($book->cover_image_url, 'http') ? $book->cover_image_url : \Illuminate\Support\Facades\Storage::disk('public')->url($book->cover_image_url) }}" alt="{{ $book->title }} cover">
                                    @else
                                        <span class="publisher-top-book-placeholder">📘</span>
                                    @endif
                                    <span><strong>{{ $book->title }}</strong><small>{{ $book->author_name ?: 'Author not specified' }}</small></span>
                                </div>
                            </td>
                            <td>{{ $book->isbn ?: '—' }}</td>
                            <td>{{ number_format($book->orders_count) }}</td>
                            <td><strong>{{ number_format($book->units_sold) }}</strong></td>
                            <td><span class="publisher-stock-chip {{ $book->current_stock <= 0 ? 'is-out' : '' }}">{{ number_format($book->current_stock) }} available</span></td>
                            <td style="text-align:right;font-weight:700;">₹{{ number_format($book->total_revenue, 0) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" style="text-align:center; color:var(--a-text-muted); padding:24px 10px;">
                                No completed book sales recorded yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="publisher-dashboard-bottom">
        <section class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Recent orders</h3>
                    <p>Latest orders containing your books</p>
                </div>
                <a href="{{ route('publisher.orders.index') }}">View all →</a>
            </div>
            <div class="dashboard-table-scroll">
                <table class="a-table">
                    <thead>
                        <tr>
                            <th>Order</th>
                            <th>Book</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Fulfillment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrderItems as $item)
                        <tr>
                            <td><strong>#CSO{{ $item->order_id }}</strong></td>
                            <td>{{ $item->book?->title ?? 'Book unavailable' }}</td>
                            <td>{{ $item->order?->customer?->name ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td><span class="badge {{ $item->fulfillment_status === 'shipped' ? 'badge-success' : 'badge-info' }}">
                                {{ ucfirst($item->fulfillment_status) }}
                            </span>
                            </td>
                            </tr>@empty<tr>
                            <td colspan="5">No orders yet.</td>
                        </tr>@endforelse
                    </tbody>
                </table>
            </div>
        </section>
        <section class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Low Stock Alerts</h3>
                    <p>Books that need restocking</p>
                </div>
                <a href="{{ route('publisher.inventory.index') }}">Manage →</a>
            </div>
            <div class="publisher-alert-list">
                @forelse($lowStockBooks as $book)
                    <div>
                        <span class="publisher-alert-icon">!</span>
                        <div>
                            <strong>{{ $book->title }}</strong>
                            <small>Threshold: {{ $book->inventory?->low_stock_threshold ?? 5 }}</small>
                        </div>
                        <b>{{ $book->inventory?->quantity ?? 0 }} left</b>
                    </div>
                    @empty
                    <div class="publisher-all-good">
                        <span>✓</span>
                        <p>All titles have healthy stock.</p>
                    </div>
                @endforelse
            </div>
        </section>
    </div>

    <section class="a-card publisher-activity-card">
        <div class="a-card-head dashboard-card-title">
            <div><h3>Recent Activity</h3><p>Live catalogue, stock, order, and payout updates</p></div>
            <a href="{{ route('publisher.inventory.index') }}">View inventory →</a>
        </div>
        <div class="dashboard-table-scroll">
            <table class="a-table">
                <thead><tr><th>Activity</th><th>Details</th><th>Performed by</th><th>Time</th></tr></thead>
                <tbody>@forelse($recentActivities as $activity)
                    <tr>
                        <td><span class="publisher-activity-kind publisher-activity-kind--{{ str_replace('_', '-', $activity->type) }}">{{ str($activity->type)->replace('_', ' ')->title() }}</span></td>
                        <td><strong>{{ $activity->subject }}</strong><small>{{ $activity->description }}</small></td>
                        <td>{{ $activity->actor?->name ?? 'System' }}</td>
                        <td class="publisher-activity-time" title="{{ $activity->created_at->format('d M Y, h:i A') }}">{{ $activity->created_at->diffForHumans() }}</td>
                    </tr>
                @empty<tr><td colspan="4">No recent activity.</td></tr>@endforelse</tbody>
            </table>
        </div>
    </section>
@endsection
