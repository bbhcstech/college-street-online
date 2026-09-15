<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $publisher = auth()->user()->publisher;
        $saleStatuses = ['confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed'];
        $monthStart = now()->startOfMonth();
        $lastSevenDays = collect(range(6, 0))->map(fn ($days) => now()->subDays($days)->startOfDay());

        $salesQuery = DB::table('order_items')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('books.publisher_id', $publisher->id)
            ->whereIn('orders.status', $saleStatuses);

        $dailySales = (clone $salesQuery)
            ->where('orders.created_at', '>=', $lastSevenDays->first())
            ->selectRaw('DATE(orders.created_at) as day, SUM(order_items.quantity) as units, SUM(order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)) as revenue')
            ->groupBy('day')->get()->keyBy('day');

        $statusMix = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->where('books.publisher_id', $publisher->id)
            ->selectRaw('orders.status, COUNT(DISTINCT orders.id) as total')
            ->groupBy('orders.status')->pluck('total', 'status');

        $totalSales = (float) ((clone $salesQuery)->sum(DB::raw('order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)')));
        $totalOrders = (clone $salesQuery)->distinct()->count('orders.id');
        $unitsSold = (int) ((clone $salesQuery)->sum('order_items.quantity'));
        $pendingOrders = DB::table('orders')
            ->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->where('books.publisher_id', $publisher->id)
            ->where('order_items.fulfillment_status', 'pending')
            ->whereIn('orders.status', $saleStatuses)
            ->distinct()
            ->count('orders.id');

        $monthlyRevenue = (float) ((clone $salesQuery)->where('orders.created_at', '>=', $monthStart)->sum(DB::raw('order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)')));
        $monthlyUnits = (int) ((clone $salesQuery)->where('orders.created_at', '>=', $monthStart)->sum('order_items.quantity'));

        $lastSixMonths = collect(range(5, 0))->map(fn ($m) => now()->subMonths($m)->startOfMonth());
        $monthlyTrendsData = (clone $salesQuery)
            ->where('orders.created_at', '>=', $lastSixMonths->first())
            ->selectRaw("DATE_FORMAT(orders.created_at, '%Y-%m') as ym, SUM(order_items.quantity) as units, SUM(order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)) as revenue")
            ->groupBy('ym')->get()->keyBy('ym');

        $monthlyTrends = $lastSixMonths->map(function ($date) use ($monthlyTrendsData) {
            $key = $date->format('Y-m');
            $point = $monthlyTrendsData->get($key);
            return [
                'label' => $date->format('M Y'),
                'short_label' => $date->format('M'),
                'units' => (int) ($point->units ?? 0),
                'revenue' => (float) ($point->revenue ?? 0),
            ];
        });

        $topSellingBooks = DB::table('order_items')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('books.publisher_id', $publisher->id)
            ->whereIn('orders.status', $saleStatuses)
            ->selectRaw('books.id, books.title, books.isbn, books.cover_image_url, SUM(order_items.quantity) as units_sold, SUM(order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)) as total_revenue, COUNT(DISTINCT orders.id) as orders_count')
            ->groupBy('books.id', 'books.title', 'books.isbn', 'books.cover_image_url')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();

        return view('publisher.dashboard', [
            'totalBooks' => $publisher->books()->count(),
            'activeBooks' => $publisher->books()->where('status', 'active')->count(),
            'inactiveBooks' => $publisher->books()->where('status', '!=', 'active')->count(),
            'outOfStockCount' => $publisher->books()->whereHas('inventory', fn ($query) => $query->where('quantity', '<=', 0))->count(),
            'inStockCount' => $publisher->books()->whereHas('inventory', fn ($query) => $query->where('quantity', '>', 0))->count(),
            'recentBooks' => $publisher->books()->with(['author', 'category', 'inventory'])->latest('updated_at')->limit(5)->get(),
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'unitsSold' => $unitsSold,
            'totalRevenue' => $totalSales,
            'monthlyRevenue' => $monthlyRevenue,
            'monthlyUnits' => $monthlyUnits,
            'monthlyTrends' => $monthlyTrends,
            'topSellingBooks' => $topSellingBooks,
            'pendingOrders' => $pendingOrders,
            'lowStockCount' => $publisher->books()->whereHas('inventory', fn ($query) => $query->whereColumn('quantity', '<=', 'low_stock_threshold'))->count(),
            'statusMix' => $statusMix,
            'weeklyTrend' => $lastSevenDays->map(function ($date) use ($dailySales) {
                $point = $dailySales->get($date->toDateString());
                return ['label' => $date->format('D'), 'units' => (int) ($point->units ?? 0), 'revenue' => (float) ($point->revenue ?? 0)];
            }),
            'recentOrderItems' => OrderItem::whereHas('book', fn ($query) => $query->where('publisher_id', $publisher->id))
                ->with(['book', 'order.customer'])->latest()->limit(6)->get(),
            'lowStockBooks' => $publisher->books()->with('inventory')
                ->whereHas('inventory', fn ($query) => $query->whereColumn('quantity', '<=', 'low_stock_threshold'))
                ->orderBy('title')->limit(6)->get(),
        ]);
    }
}
