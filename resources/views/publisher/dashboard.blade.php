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
    </style>

    <div class="sales-analytics-grid">
        <!-- 📈 Sales Overview — Line Graph -->
        <section class="a-card">
            <div class="a-card-head dashboard-card-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
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

        <!-- 🏆 Top-Selling Books -->
        <section class="a-card">
            <div class="a-card-head dashboard-card-title" style="display:flex; justify-content:space-between; align-items:center;">
                <div>
                    <h3 style="margin:0;">Top-Selling Books</h3>
                    <p style="margin:3px 0 0; color:var(--a-text-muted); font-size:0.75rem;">Best performing titles by sales</p>
                </div>
                <a href="{{ route('publisher.books.index') }}" style="color:var(--a-primary); font-size:0.78rem; font-weight:700;">All books &rarr;</a>
            </div>

            <div class="dashboard-table-scroll" style="margin-top:8px;">
                <table class="a-table" style="font-size:0.8rem;">
                    <thead>
                        <tr>
                            <th style="width:36px;">#</th>
                            <th>Book Title</th>
                            <th style="text-align:center;">Sold</th>
                            <th style="text-align:right;">Gross</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topSellingBooks as $index => $book)
                            <tr>
                                <td>
                                    <span class="top-book-rank {{ $index === 0 ? 'gold' : '' }}">{{ $index + 1 }}</span>
                                </td>
                                <td>
                                    <strong style="display:block; max-width:180px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $book->title }}</strong>
                                    <small style="color:var(--a-text-muted); font-size:0.7rem;">{{ $book->isbn ?: 'ISBN N/A' }}</small>
                                </td>
                                <td style="text-align:center;">
                                    <strong>{{ number_format($book->units_sold) }}</strong>
                                    <small style="display:block; color:var(--a-text-muted); font-size:0.68rem;">units</small>
                                </td>
                                <td style="text-align:right; font-weight:700; color:var(--a-text);">
                                    ₹{{ number_format($book->total_revenue, 0) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" style="text-align:center; color:var(--a-text-muted); padding:28px 10px;">
                                    No completed book sales recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <!-- 📖 My Books & Stock Management -->
    <section class="a-card" style="margin-bottom: 22px;">
        <div class="a-card-head dashboard-card-title" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
            <div>
                <h3 style="margin:0;">My Books &amp; Stock Management</h3>
                <p style="margin:3px 0 0; color:var(--a-text-muted); font-size:0.75rem;">Catalogue status, inventory stock levels, and quick edit</p>
            </div>
            <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <a href="{{ route('publisher.books.create') }}" class="btn btn-primary btn-sm">+ Add Book</a>
                <a href="{{ route('publisher.inventory.index') }}" class="btn btn-outline btn-sm">Stock Management &rarr;</a>
                <a href="{{ route('publisher.books.index') }}" class="btn btn-outline btn-sm">All Books &rarr;</a>
            </div>
        </div>

        <!-- Status & Stock Quick Summary Pills -->
        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(130px, 1fr)); gap:10px; margin: 14px 0 18px;">
            <div style="padding:10px 14px; border-radius:10px; background:var(--a-surface-alt); border:1px solid var(--a-border);">
                <small style="color:var(--a-text-muted); font-size:0.68rem; display:block;">Active Titles</small>
                <strong style="font-size:1.15rem; color:#1F9D6C;">{{ number_format($activeBooks) }}</strong>
            </div>
            <div style="padding:10px 14px; border-radius:10px; background:var(--a-surface-alt); border:1px solid var(--a-border);">
                <small style="color:var(--a-text-muted); font-size:0.68rem; display:block;">Inactive / Draft</small>
                <strong style="font-size:1.15rem; color:#718096;">{{ number_format($inactiveBooks) }}</strong>
            </div>
            <div style="padding:10px 14px; border-radius:10px; background:var(--a-surface-alt); border:1px solid var(--a-border);">
                <small style="color:var(--a-text-muted); font-size:0.68rem; display:block;">In Stock</small>
                <strong style="font-size:1.15rem; color:#2684BE;">{{ number_format($inStockCount) }}</strong>
            </div>
            <div style="padding:10px 14px; border-radius:10px; background:var(--a-surface-alt); border:1px solid var(--a-border);">
                <small style="color:var(--a-text-muted); font-size:0.68rem; display:block;">Low Stock</small>
                <strong style="font-size:1.15rem; color:#E07C2D;">{{ number_format($lowStockCount) }}</strong>
            </div>
            <div style="padding:10px 14px; border-radius:10px; background:var(--a-surface-alt); border:1px solid var(--a-border);">
                <small style="color:var(--a-text-muted); font-size:0.68rem; display:block;">Out of Stock</small>
                <strong style="font-size:1.15rem; color:{{ $outOfStockCount > 0 ? '#D64545' : '#718096' }};">{{ number_format($outOfStockCount) }}</strong>
            </div>
        </div>

        <div class="dashboard-table-scroll">
            <table class="a-table" style="font-size:0.8rem;">
                <thead>
                    <tr>
                        <th>Book Title</th>
                        <th>Author</th>
                        <th>Price</th>
                        <th>Stock Level</th>
                        <th>Status</th>
                        <th style="text-align:right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentBooks as $book)
                        <tr>
                            <td>
                                <strong>{{ $book->title }}</strong>
                                <small style="display:block; color:var(--a-text-muted); font-size:0.7rem;">{{ $book->isbn ?: 'ISBN N/A' }}</small>
                            </td>
                            <td>{{ $book->author?->name ?? '—' }}</td>
                            <td>₹{{ number_format($book->price, 2) }}</td>
                            <td>
                                @php($qty = $book->inventory?->quantity ?? 0)
                                @php($threshold = $book->inventory?->low_stock_threshold ?? 5)
                                @if($qty <= 0)
                                    <span class="badge badge-danger">Out of stock (0)</span>
                                @elseif($qty <= $threshold)
                                    <span class="badge badge-warning">Low stock ({{ $qty }})</span>
                                @else
                                    <span class="badge badge-success">{{ $qty }} in stock</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $book->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                    {{ ucfirst($book->status) }}
                                </span>
                            </td>
                            <td style="text-align:right;">
                                <a href="{{ route('publisher.books.edit', $book) }}" class="btn btn-outline btn-sm" style="padding:4px 10px; font-size:0.75rem;">✏️ Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; color:var(--a-text-muted); padding:24px 10px;">
                                No books added to catalogue yet. <a href="{{ route('publisher.books.create') }}">Add your first book</a>
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
                    <h3>Inventory alerts</h3>
                    <p>Titles at or below their threshold</p>
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
@endsection