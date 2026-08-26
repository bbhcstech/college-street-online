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
@section('nav')
    <div class="nav-group">
        <div class="nav-group-title">Overview</div><a href="{{ route('publisher.dashboard') }}"
            class="nav-link active"><span class="nav-icon">▣</span><span>Dashboard</span></a>
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
    <div class="nav-group">
        <div class="nav-group-title">Reports</div><a href="{{ route('publisher.analytics.index') }}" class="nav-link"><span
                class="nav-icon">&#128200;</span><span>Analytics & Reports</span></a>
    </div>
@endsection
@section('content')
    <div class="publisher-dashboard-welcome">
        <div><span>{{ now()->format('l, d F Y') }}</span>
            <h2>Welcome back, {{ auth()->user()->name }}</h2>
            <p>Your catalogue, sales, fulfillment and inventory overview.</p>
        </div><a href="{{ route('publisher.books.create') }}" class="btn btn-primary">+ Add new book</a>
    </div>

    <div class="publisher-dashboard-stats">
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">₹</div><span class="trend-chip trend-up">This month</span>
            </div>
            <div class="num">₹{{ number_format($monthlyRevenue, 0) }}</div>
            <div class="label">Gross book sales</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon purple">▤</div><span class="trend-chip trend-up">{{ $monthlyOrders }}</span>
            </div>
            <div class="num">{{ number_format($monthlyUnits) }}</div>
            <div class="label">Units sold this month</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon gold">▣</div><span class="trend-chip trend-up">{{ $activeBookCount }} active</span>
            </div>
            <div class="num">{{ $bookCount }}</div>
            <div class="label">Books listed</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon red">!</div><span
                    class="trend-chip {{ $pendingFulfillment ? 'trend-down' : 'trend-up' }}">Action</span>
            </div>
            <div class="num">{{ $pendingFulfillment }}</div>
            <div class="label">Items awaiting processing</div>
        </div>
    </div>

    <div class="publisher-dashboard-health">
        <div><strong>{{ $monthlyOrders }}</strong><span>Orders this month</span></div>
        <div><strong>{{ $activeBookCount }}</strong><span>Active titles</span></div>
        <div class="{{ $lowStockCount ? 'attention' : '' }}"><strong>{{ $lowStockCount }}</strong><span>Low-stock
                titles</span>
        </div>
    </div>

    <div class="dashboard-chart-grid">
        <section class="a-card dashboard-chart-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Sales performance</h3>
                    <p>Publisher sales during the last seven days</p>
                </div>
                <div class="chart-legend"><span><i class="legend-orders"></i>Units</span><span><i
                            class="legend-revenue"></i>Revenue</span></div>
            </div>
            <div class="weekly-chart">@foreach($weeklyTrend as $point)
                <div class="weekly-column" title="{{ $point['units'] }} units · ₹{{ number_format($point['revenue'], 0) }}">
                    <div class="bar-area"><i class="revenue-bar"
                            style="height:{{ $point['revenue'] > 0 ? max(4, $point['revenue'] / $maxRevenue * 100) : 0 }}%"></i><i
                            class="order-bar"
                            style="height:{{ $point['units'] > 0 ? max(4, $point['units'] / $maxUnits * 100) : 0 }}%"></i>
                    </div>
                    <strong>{{ $point['units'] }}</strong><span>{{ $point['label'] }}</span>
            </div>@endforeach
            </div>
        </section>
        <section class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Order distribution</h3>
                    <p>Orders containing your books</p>
                </div>
            </div>
            <div class="status-chart-wrap">
                <div class="status-donut"
                    style="background:conic-gradient({{ implode(',', $gradient) ?: '#DCE2ED 0 100%' }});">
                    <div><strong>{{ $statusMix->sum() }}</strong><span>Orders</span></div>
                </div>
                <div class="status-legend">@forelse($statusMix as $status => $count)
                    <div><i
                            style="background:{{ $statusColors[$status] ?? '#8A96A8' }}"></i><span>{{ ucfirst(str_replace('_', ' ', $status)) }}</span><strong>{{ $count }}</strong>
                </div>@empty<p>No orders yet.</p>@endforelse
                </div>
            </div>
        </section>
    </div>

    <div class="publisher-dashboard-bottom">
        <section class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Recent orders</h3>
                    <p>Latest orders containing your books</p>
                </div><a href="{{ route('publisher.orders.index') }}">View all →</a>
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
                    <tbody>@forelse($recentOrderItems as $item)
                        <tr>
                            <td><strong>#CSO{{ $item->order_id }}</strong></td>
                            <td>{{ $item->book?->title ?? 'Book unavailable' }}</td>
                            <td>{{ $item->order?->customer?->name ?? '—' }}</td>
                            <td>{{ $item->quantity }}</td>
                            <td><span
                                    class="badge {{ $item->fulfillment_status === 'shipped' ? 'badge-success' : 'badge-info' }}">{{ ucfirst($item->fulfillment_status) }}</span>
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
                </div><a href="{{ route('publisher.inventory.index') }}">Manage →</a>
            </div>
            <div class="publisher-alert-list">@forelse($lowStockBooks as $book)
                <div><span class="publisher-alert-icon">!</span>
                    <div><strong>{{ $book->title }}</strong><small>Threshold:
                            {{ $book->inventory?->low_stock_threshold ?? 5 }}</small></div>
                    <b>{{ $book->inventory?->quantity ?? 0 }} left</b>
            </div>@empty<div class="publisher-all-good"><span>✓</span>
                    <p>All titles have healthy stock.</p>
                </div>@endforelse
            </div>
        </section>
    </div>
@endsection