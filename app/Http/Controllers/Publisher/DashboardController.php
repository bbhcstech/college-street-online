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

        return view('publisher.dashboard', [
            'bookCount' => $publisher->books()->count(),
            'activeBookCount' => $publisher->books()->where('status', 'active')->count(),
            'lowStockCount' => $publisher->books()->whereHas('inventory', fn ($query) => $query->whereColumn('quantity', '<=', 'low_stock_threshold'))->count(),
            'monthlyRevenue' => (clone $salesQuery)->where('orders.created_at', '>=', $monthStart)->sum(DB::raw('order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)')),
            'monthlyUnits' => (clone $salesQuery)->where('orders.created_at', '>=', $monthStart)->sum('order_items.quantity'),
            'monthlyOrders' => (clone $salesQuery)->where('orders.created_at', '>=', $monthStart)->distinct()->count('orders.id'),
            'pendingFulfillment' => OrderItem::whereHas('book', fn ($query) => $query->where('publisher_id', $publisher->id))
                ->where('fulfillment_status', 'pending')->whereHas('order', fn ($query) => $query->whereIn('status', $saleStatuses))->count(),
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
