@extends('layouts.dashboard')
@php
    $homeRoute = route('publisher.dashboard');
    $brandLabel = 'Publisher Panel';
    $crumb = 'Analytics & Reports';
    $logoutRoute = route('publisher.logout');
    
    $maxRevenue = max(1, $series->max('revenue'));
    $maxUnits = max(1, $series->max('units'));
    $statusTotal = max(1, $statusMix->sum());
    $colors = [
        'pending_payment' => '#f59e0b',
        'confirmed' => '#3b82f6',
        'processing' => '#8b5cf6',
        'packed' => '#d97706',
        'shipped' => '#06b6d4',
        'delivered' => '#10b981',
        'completed' => '#059669',
        'cancelled' => '#ef4444'
    ];
    $cursor = 0;
    $gradient = [];
    foreach ($statusMix as $status => $count) {
        $next = $cursor + ($count / $statusTotal * 100);
        $gradient[] = ($colors[$status] ?? '#94a3b8') . " {$cursor}% {$next}%";
        $cursor = $next;
    }
    
    $trendWidth = 640;
    $trendHeight = 220;
    $trendPad = 32;
    $trendPlotWidth = $trendWidth - ($trendPad * 2);
    $trendPlotHeight = $trendHeight - 55;
    
    $revenueLine = $series->map(fn($point, $index) => round($trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2), 1) . ',' . round($trendPad + $trendPlotHeight - ($point['revenue'] / $maxRevenue * $trendPlotHeight), 1))->implode(' ');
    $unitsLine = $series->map(fn($point, $index) => round($trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2), 1) . ',' . round($trendPad + $trendPlotHeight - ($point['units'] / $maxUnits * $trendPlotHeight), 1))->implode(' ');
    $revenueArea = $trendPad . ',' . ($trendPad + $trendPlotHeight) . ' ' . $revenueLine . ' ' . ($trendWidth - $trendPad) . ',' . ($trendPad + $trendPlotHeight);
@endphp
@section('title', 'Analytics & Reports')
@section('nav') @include('publisher.partials.nav', ['active' => 'analytics']) @endsection

@section('content')
<style>
    .pub-analytics-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
        border: 1px solid #f1f5f9;
        margin-bottom: 24px;
    }
    .pub-stat-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(210px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .pub-stat-card {
        background: #ffffff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        border: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .pub-donut {
        width: 140px;
        height: 140px;
        border-radius: 50%;
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
    }
    .pub-donut::before {
        content: '';
        position: absolute;
        width: 94px;
        height: 94px;
        border-radius: 50%;
        background: #ffffff;
    }
    .pub-donut-inner {
        position: relative;
        z-index: 2;
        text-align: center;
    }
</style>

<!-- Header -->
<div style="margin-bottom: 20px;">
    <span class="a-eyebrow" style="text-transform: uppercase; font-size: 0.72rem; font-weight: 800; letter-spacing: 0.8px; color: #64748b;">Catalogue Insights</span>
    <h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 4px 0;">Analytics &amp; Reports</h2>
    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Track real-time sales, revenue trends, inventory movement, and book performance.</p>
</div>

<!-- Single Row Filter & Action Bar Card -->
<div class="pub-analytics-card" style="padding: 14px 20px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: nowrap; overflow-x: auto;">
    <form method="GET" style="display: flex; align-items: center; gap: 10px; flex-shrink: 0;">
        <select name="period" class="a-input" style="height: 40px; width: 140px !important; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-weight: 600; font-size: 0.88rem; flex-shrink: 0;">
            <option value="day" @selected($period === 'day')>📅 Daily</option>
            <option value="week" @selected($period === 'week')>📆 Weekly</option>
            <option value="month" @selected($period === 'month')>📊 Monthly</option>
            <option value="year" @selected($period === 'year')>📈 Yearly</option>
            <option value="custom" @selected($period === 'custom')>⚙️ Custom</option>
        </select>
        
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="a-input" style="height: 40px; width: 145px !important; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 0.85rem; flex-shrink: 0;" aria-label="From date">
        
        <span style="color: #94a3b8; font-weight: 600; font-size: 0.85rem;">to</span>
        
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="a-input" style="height: 40px; width: 145px !important; border-radius: 8px; border: 1px solid #cbd5e1; padding: 0 10px; font-size: 0.85rem; flex-shrink: 0;" aria-label="To date">
        
        <button class="btn btn-primary" style="height: 40px; padding: 0 18px; border-radius: 8px; font-weight: 700; white-space: nowrap; flex-shrink: 0;">Apply Filter</button>
    </form>

    <div style="display: flex; align-items: center; gap: 8px; flex-shrink: 0;">
        <a class="btn btn-outline" href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'excel'])) }}" style="height: 40px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
            📊 Export Excel
        </a>
        <a class="btn btn-outline" target="_blank" href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'pdf'])) }}" style="height: 40px; padding: 0 14px; border-radius: 8px; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
            📄 Export PDF
        </a>
    </div>
</div>

<!-- 4 KPI Summary Box Grid -->
<div class="pub-stat-grid">
    <div class="pub-stat-card" style="border-left: 4px solid #3b82f6;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Total Sales</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ number_format($units) }} <span style="font-size: 0.85rem; font-weight: 600; color: #64748b;">units</span></div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.1); color: #3b82f6; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">📦</div>
    </div>

    <div class="pub-stat-card" style="border-left: 4px solid #10b981;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Net Earnings</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #047857; margin-top: 4px;">₹{{ number_format($netRevenue, 0) }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.1); color: #10b981; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">💰</div>
    </div>

    <div class="pub-stat-card" style="border-left: 4px solid #8b5cf6;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Gross Sales</div>
            <div style="font-size: 1.6rem; font-weight: 800; color: #0f172a; margin-top: 4px;">₹{{ number_format($revenue, 0) }}</div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.1); color: #8b5cf6; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">📊</div>
    </div>

    <div class="pub-stat-card" style="border-left: 4px solid #f59e0b;">
        <div>
            <div style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; letter-spacing: 0.5px;">Sales Growth</div>
            <div style="margin-top: 4px; display: flex; align-items: center; gap: 6px;">
                <span style="font-size: 1.4rem; font-weight: 800; color: {{ $salesGrowth < 0 ? '#ef4444' : '#10b981' }};">
                    {{ $salesGrowth >= 0 ? '+' : '' }}{{ number_format($salesGrowth, 1) }}%
                </span>
                <span style="background: {{ $salesGrowth < 0 ? 'rgba(239, 68, 68, 0.12)' : 'rgba(16, 185, 129, 0.12)' }}; color: {{ $salesGrowth < 0 ? '#dc2626' : '#047857' }}; font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 12px;">
                    {{ $salesGrowth < 0 ? '📉 Decreasing' : '📈 Growing' }}
                </span>
            </div>
        </div>
        <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); color: #d97706; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">⚡</div>
    </div>
</div>

<!-- Chart & Order Distribution Grid -->
<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Sales & Revenue Trend Chart -->
    <div class="pub-analytics-card" style="margin-bottom: 0;">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9;">
            <div>
                <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Sales &amp; Revenue Trend</h3>
                <p style="color: #64748b; font-size: 0.85rem; margin: 2px 0 0 0;">Units and total revenue trajectory across {{ strtolower($period) }} period.</p>
            </div>
            <div style="display: flex; align-items: center; gap: 16px; font-size: 0.8rem; font-weight: 700;">
                <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #3b82f6;"></span> Units</span>
                <span style="display: inline-flex; align-items: center; gap: 6px;"><span style="width: 10px; height: 10px; border-radius: 50%; background: #f59e0b;"></span> Revenue (₹)</span>
            </div>
        </div>

        <div style="overflow-x: auto;">
            <svg viewBox="0 0 {{ $trendWidth }} {{ $trendHeight }}" style="width: 100%; height: auto; min-width: 500px;" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="pubRevenueGradient" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#f59e0b" stop-opacity="0.3" />
                        <stop offset="100%" stop-color="#f59e0b" stop-opacity="0.0" />
                    </linearGradient>
                </defs>

                @foreach([0, 0.5, 1] as $grid)
                    <line x1="{{ $trendPad }}" y1="{{ $trendPad + ($trendPlotHeight * $grid) }}" x2="{{ $trendWidth - $trendPad }}" y2="{{ $trendPad + ($trendPlotHeight * $grid) }}" stroke="#e2e8f0" stroke-width="1" stroke-dasharray="4" />
                @endforeach

                @if($series->count() > 1)
                    <polygon points="{{ $revenueArea }}" fill="url(#pubRevenueGradient)" />
                    <polyline points="{{ $revenueLine }}" fill="none" stroke="#f59e0b" stroke-width="3" stroke-linecap="round" />
                    <polyline points="{{ $unitsLine }}" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-dasharray="4" stroke-linecap="round" />
                @endif

                @foreach($series as $index => $point)
                    @php($x = $trendPad + ($series->count() > 1 ? $index / ($series->count() - 1) * $trendPlotWidth : $trendPlotWidth / 2))
                    @php($revenueX = $series->count() === 1 ? $x - 24 : $x)
                    @php($unitsX = $series->count() === 1 ? $x + 24 : $x)
                    @php($revenueY = $trendPad + $trendPlotHeight - ($point['revenue'] / $maxRevenue * $trendPlotHeight))
                    @php($unitsY = $trendPad + $trendPlotHeight - ($point['units'] / $maxUnits * $trendPlotHeight))

                    <circle cx="{{ $revenueX }}" cy="{{ $revenueY }}" r="5" fill="#f59e0b" stroke="#ffffff" stroke-width="2">
                        <title>{{ $point['label'] }}: ₹{{ number_format($point['revenue'], 2) }}</title>
                    </circle>
                    <circle cx="{{ $unitsX }}" cy="{{ $unitsY }}" r="4" fill="#3b82f6" stroke="#ffffff" stroke-width="1.5">
                        <title>{{ $point['label'] }}: {{ $point['units'] }} units</title>
                    </circle>

                    <text x="{{ $revenueX }}" y="{{ max(14, $revenueY - 10) }}" text-anchor="middle" font-size="11" font-weight="700" fill="#b45309">₹{{ number_format($point['revenue'], 0) }}</text>
                    <text x="{{ $x }}" y="{{ $trendHeight - 6 }}" text-anchor="middle" font-size="11" font-weight="600" fill="#64748b">{{ $point['label'] }}</text>
                @endforeach
            </svg>
        </div>
    </div>

    <!-- Order Distribution Donut -->
    <div class="pub-analytics-card" style="margin-bottom: 0; display: flex; flex-direction: column; justify-content: space-between;">
        <div>
            <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Order Distribution</h3>
            <p style="color: #64748b; font-size: 0.85rem; margin: 2px 0 16px 0;">Breakdown of orders for your catalogue.</p>
        </div>

        <div class="pub-donut" style="background: conic-gradient({{ implode(',', $gradient) ?: '#e2e8f0 0 100%' }}); margin-bottom: 16px;">
            <div class="pub-donut-inner">
                <div style="font-size: 1.5rem; font-weight: 800; color: #0f172a; line-height: 1.1;">{{ $statusMix->sum() }}</div>
                <div style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase;">Orders</div>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 8px;">
            @forelse($statusMix as $status => $count)
                <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;">
                    <span style="display: flex; align-items: center; gap: 6px; font-weight: 600; color: #334155;">
                        <span style="width: 10px; height: 10px; border-radius: 50%; background: {{ $colors[$status] ?? '#94a3b8' }};"></span>
                        {{ str($status)->replace('_', ' ')->title() }}
                    </span>
                    <strong style="color: #0f172a; font-weight: 700;">{{ $count }}</strong>
                </div>
            @empty
                <p style="color: #94a3b8; font-size: 0.85rem; text-align: center; margin: 0;">No orders in this period.</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Top-selling books Leaderboard Card -->
<div class="pub-analytics-card">
    <div style="margin-bottom: 20px;">
        <h3 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0;">Top-Selling Books</h3>
        <p style="color: #64748b; font-size: 0.85rem; margin: 2px 0 0 0;">Leaderboard ranked by confirmed book sales volume.</p>
    </div>

    <div class="table-responsive">
        <table class="a-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: left;">Rank</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: left;">Book Title</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: left;">ISBN</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: center;">Orders</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: center;">Units Sold</th>
                    <th style="padding: 12px 16px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: right;">Gross Revenue</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topBooks as $book)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px 16px;">
                            <span style="font-size: 0.75rem; font-weight: 800; padding: 3px 10px; border-radius: 12px; {{ $loop->iteration === 1 ? 'background: rgba(245, 158, 11, 0.15); color: #b45309; border: 1px solid rgba(245, 158, 11, 0.3);' : ($loop->iteration === 2 ? 'background: rgba(148, 163, 184, 0.18); color: #475569; border: 1px solid rgba(148, 163, 184, 0.3);' : ($loop->iteration === 3 ? 'background: rgba(217, 119, 6, 0.15); color: #92400e; border: 1px solid rgba(217, 119, 6, 0.3);' : 'background: #f1f5f9; color: #64748b;')) }}">
                                #{{ $loop->iteration }}
                            </span>
                        </td>
                        <td style="padding: 12px 16px; font-weight: 700; color: #0f172a;">{{ $book->title }}</td>
                        <td style="padding: 12px 16px; color: #64748b; font-size: 0.88rem;">{{ $book->isbn ?: '—' }}</td>
                        <td style="padding: 12px 16px; text-align: center; font-weight: 600;">{{ $book->orders }}</td>
                        <td style="padding: 12px 16px; text-align: center; font-weight: 700; color: #2563eb;">{{ $book->units }}</td>
                        <td style="padding: 12px 16px; text-align: right; font-weight: 800; color: #047857;">₹{{ number_format($book->revenue, 0) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding: 24px; text-align: center; color: #94a3b8; font-weight: 500;">
                            No sales recorded for the selected period.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Secondary Insights Grid (Most Viewed vs Least Selling) -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Most Viewed Books -->
    <div class="pub-analytics-card" style="margin-bottom: 0;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">👁️ Most Viewed Books</h3>
        <table class="a-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: left;">Book</th>
                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: right;">Views</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mostViewedBooks as $book)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px; font-weight: 600; color: #334155;">{{ $book->title }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #2563eb;">{{ number_format($book->view_count) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="padding: 16px; text-align: center; color: #94a3b8;">No book views recorded.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Least Selling Books -->
    <div class="pub-analytics-card" style="margin-bottom: 0;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">⚠️ Least-Selling Titles</h3>
        <table class="a-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: left;">Book</th>
                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; color: #64748b; text-align: right;">Units Sold</th>
                </tr>
            </thead>
            <tbody>
                @forelse($leastSellingBooks as $book)
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 10px 12px; font-weight: 600; color: #334155;">{{ $book->title }}</td>
                        <td style="padding: 10px 12px; text-align: right; font-weight: 800; color: #64748b;">{{ number_format($book->units) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2" style="padding: 16px; text-align: center; color: #94a3b8;">No books found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Inventory & Customer Performance Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-bottom: 24px;">
    <!-- Inventory Health -->
    <div class="pub-analytics-card" style="margin-bottom: 0;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">📦 Inventory Status</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
            <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block;">Current Stock</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #0f172a;">{{ number_format($inventory->current_stock) }}</strong>
            </div>
            <div style="background: #fefce8; padding: 12px; border-radius: 8px; border: 1px solid #fef08a;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #854d0e; text-transform: uppercase; display: block;">Low Stock</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #ca8a04;">{{ number_format($inventory->low_stock) }}</strong>
            </div>
            <div style="background: #fef2f2; padding: 12px; border-radius: 8px; border: 1px solid #fecaca;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #991b1b; text-transform: uppercase; display: block;">Out of Stock</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #dc2626;">{{ number_format($inventory->out_of_stock) }}</strong>
            </div>
            <div style="background: #f0fdf4; padding: 12px; border-radius: 8px; border: 1px solid #bbf7d0;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #166534; text-transform: uppercase; display: block;">Stock Movement</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #16a34a;">{{ number_format($stockMovement) }}</strong>
            </div>
        </div>
    </div>

    <!-- Customer Insights -->
    <div class="pub-analytics-card" style="margin-bottom: 0;">
        <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">👥 Customer Analytics</h3>
        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px;">
            <div style="background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #f1f5f9;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; text-transform: uppercase; display: block;">Total Customers</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #0f172a;">{{ $customerAnalytics['total'] }}</strong>
            </div>
            <div style="background: #f0fdf4; padding: 12px; border-radius: 8px; border: 1px solid #bbf7d0;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #166534; text-transform: uppercase; display: block;">New Buyers</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #16a34a;">{{ $customerAnalytics['new'] }}</strong>
            </div>
            <div style="background: #eff6ff; padding: 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                <span style="font-size: 0.72rem; font-weight: 700; color: #1e40af; text-transform: uppercase; display: block;">Repeat Buyers</span>
                <strong style="font-size: 1.2rem; font-weight: 800; color: #2563eb;">{{ $customerAnalytics['repeat'] }}</strong>
            </div>
        </div>
    </div>
</div>

<!-- Quick Download Reports Card -->
<div class="pub-analytics-card">
    <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0;">📑 Detailed Publisher Reports</h3>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 12px;">
        @foreach(['sales' => 'Sales Report', 'inventory' => 'Inventory Report', 'orders' => 'Order Report', 'books' => 'Book Performance Report'] as $report => $label)
            <div style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between;">
                <span style="font-weight: 700; font-size: 0.88rem; color: #334155;">{{ $label }}</span>
                <div style="display: flex; gap: 6px;">
                    <a class="btn btn-outline btn-sm" href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'excel', 'report' => $report])) }}" style="padding: 3px 8px; font-size: 0.75rem; border-radius: 4px;">Excel</a>
                    <a class="btn btn-outline btn-sm" target="_blank" href="{{ route('publisher.analytics.export', array_merge(request()->query(), ['type' => 'pdf', 'report' => $report])) }}" style="padding: 3px 8px; font-size: 0.75rem; border-radius: 4px;">PDF</a>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection