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
    <div class="a-grid a-grid-4 dashboard-stats">
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon green">₹</div><span class="trend-chip trend-up">This month</span>
            </div>
            <div class="num">₹{{ number_format($monthlyRevenue, 0) }}</div>
            <div class="label">Verified revenue</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon purple">▤</div><span class="trend-chip trend-up">{{ $orderCount }}</span>
            </div>
            <div class="num">{{ number_format($orderCount) }}</div>
            <div class="label">Orders this month</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon gold">▣</div><span class="trend-chip trend-up">Live</span>
            </div>
            <div class="num">{{ number_format($bookCount) }}</div>
            <div class="label">Active books</div>
        </div>
        <div class="stat-box">
            <div class="stat-top">
                <div class="stat-icon red">!</div><span
                    class="trend-chip {{ $pendingPayments ? 'trend-down' : 'trend-up' }}">Action</span>
            </div>
            <div class="num">{{ number_format($pendingPayments) }}</div>
            <div class="label">Payments to verify</div>
        </div>
    </div>
    <div class="dashboard-health-strip">
        <div><strong>{{ $publisherCount }}</strong><span>Approved publishers</span></div>
        <div><strong>{{ $customerCount }}</strong><span>Customers</span></div>
        <div><strong>{{ $pendingPublishers }}</strong><span>Publisher approvals</span></div>
        <div><strong>{{ $lowStockCount }}</strong><span>Low-stock titles</span></div>
    </div>
    <div class="dashboard-chart-grid">
        <div class="a-card dashboard-chart-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Orders & revenue</h3>
                    <p>Last seven days</p>
                </div>
                <div class="chart-legend"><span><i class="legend-orders"></i>Orders</span><span><i
                            class="legend-revenue"></i>Revenue</span></div>
            </div>
            <div class="weekly-chart">@foreach($weeklyTrend as $point)
                <div class="weekly-column"
                    title="{{ $point['orders'] }} orders · ₹{{ number_format($point['revenue'], 0) }}">
                    <div class="bar-area"><i class="revenue-bar"
                            style="height:{{ max(3, $point['revenue'] / $maxRevenue * 100) }}%"></i><i class="order-bar"
                            style="height:{{ max(3, $point['orders'] / $maxOrders * 100) }}%"></i></div>
                    <strong>{{ $point['orders'] }}</strong><span>{{ $point['label'] }}</span>
            </div>@endforeach
            </div>
        </div>
        <div class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Order status</h3>
                    <p>All-time distribution</p>
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
        </div>
    </div>
    <div class="a-grid dashboard-bottom-grid">
        <div class="a-card">
            <div class="a-card-head dashboard-card-title">
                <div>
                    <h3>Recent orders</h3>
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
@endsection