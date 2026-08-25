<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\NewsletterSubscriber;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = in_array($request->query('period'), ['30', '90', '365', 'all'], true)
            ? $request->query('period')
            : '30';
        $from = $period === 'all' ? null : now()->subDays((int) $period - 1)->startOfDay();

        $orders = Order::query()->when($from, fn ($query) => $query->where('created_at', '>=', $from));
        $paidOrders = Order::query()
            ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
            ->where('status', '!=', 'cancelled')
            ->when($from, fn ($query) => $query->where('created_at', '>=', $from));

        $useMonths = $period === '365' || $period === 'all';
        $dateExpression = $useMonths ? "DATE_FORMAT(orders.created_at, '%Y-%m')" : 'DATE(orders.created_at)';
        $revenueTrend = (clone $paidOrders)
            ->selectRaw("{$dateExpression} as period, SUM(total_amount) as revenue, COUNT(*) as orders")
            ->groupBy('period')->orderBy('period')->get();

        $statuses = ['pending_payment', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        $statusCounts = (clone $orders)->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', $statuses)->groupBy('status')->pluck('total', 'status');

        $topBooks = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->when($from, fn ($query) => $query->where('orders.created_at', '>=', $from))
            ->where('orders.status', '!=', 'cancelled')
            ->selectRaw('books.id, books.title, SUM(order_items.quantity) as units, SUM(order_items.quantity * order_items.unit_price) as sales')
            ->groupBy('books.id', 'books.title')->orderByDesc('units')->limit(8)->get();

        return view('admin.analytics', [
            'period' => $period,
            'revenue' => (float) (clone $paidOrders)->sum('total_amount'),
            'orderVolume' => (clone $orders)->count(),
            'averageOrder' => (float) (clone $paidOrders)->avg('total_amount'),
            'revenueTrend' => $revenueTrend,
            'statusCounts' => $statusCounts,
            'statusLabels' => array_combine($statuses, ['Pending', 'Confirmed', 'Processing', 'Shipped', 'Delivered', 'Completed', 'Cancelled']),
            'topBooks' => $topBooks,
            'health' => [
                'Active books' => Book::active()->count(),
                'Approved publishers' => Publisher::where('approval_status', 'approved')->count(),
                'Customers' => User::where('role', 'customer')->count(),
                'Subscribers' => NewsletterSubscriber::count(),
                'Pending payments' => Payment::where('verified_status', 'pending')->count(),
                'Low-stock books' => DB::table('inventories')->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
            ],
        ]);
    }
}
