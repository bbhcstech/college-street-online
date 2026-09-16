<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Publisher;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $startOfMonth = now()->startOfMonth();
        $lastSevenDays = collect(range(6, 0))->map(fn ($days) => now()->subDays($days)->startOfDay());
        $dailyOrders = Order::where('created_at', '>=', $lastSevenDays->first())
            ->selectRaw('DATE(created_at) as day, COUNT(*) as total')
            ->groupBy('day')->pluck('total', 'day');
        $dailyRevenue = Order::where('created_at', '>=', $lastSevenDays->first())
            ->where('status', '!=', 'cancelled')
            ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
            ->selectRaw('DATE(created_at) as day, SUM(base_total_amount) as total')
            ->groupBy('day')->pluck('total', 'day');

        $salesByCountryData = Order::where('status', '!=', 'cancelled')
            ->leftJoin('countries', 'orders.country_code', '=', 'countries.code')
            ->selectRaw('COALESCE(countries.name, orders.country_code, "India") as country_name, SUM(orders.base_total_amount) as total_sales')
            ->groupBy('country_name')
            ->orderByDesc('total_sales')
            ->pluck('total_sales', 'country_name');

        if ($salesByCountryData->isEmpty()) {
            $salesByCountryData = collect(['India' => 0, 'United States' => 0, 'United Kingdom' => 0, 'UAE' => 0]);
        }

        $topSellingBooksData = Book::select('books.id', 'books.title')
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as total_sold')
            ->join('order_items', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.status', '!=', 'cancelled')
            ->groupBy('books.id', 'books.title')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $lowStockBooksList = Book::with(['publisher', 'inventory'])
            ->whereHas('inventory', fn ($q) => $q->whereColumn('quantity', '<=', 'low_stock_threshold'))
            ->limit(5)
            ->get();

        $recentCustomersList = User::where('role', 'customer')
            ->withCount('orders')
            ->latest()
            ->limit(5)
            ->get();

        return view('admin.dashboard', [
            'totalSales' => Order::where('status', '!=', 'cancelled')
                ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
                ->sum('base_total_amount'),
            'todaySales' => Order::where('created_at', '>=', now()->startOfDay())
                ->where('status', '!=', 'cancelled')
                ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
                ->sum('base_total_amount'),
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending_payment')->count(),
            'publisherCount' => Publisher::where('approval_status', 'approved')->count(),
            'bookCount' => Book::active()->count(),
            'orderCount' => Order::where('created_at', '>=', $startOfMonth)->count(),
            'pendingPayments' => Payment::where('verified_status', 'pending')->count(),
            'monthlyRevenue' => Order::where('created_at', '>=', $startOfMonth)
                ->where('status', '!=', 'cancelled')
                ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
                ->sum('base_total_amount'),
            'customerCount' => User::where('role', 'customer')->count(),
            'pendingPublishers' => Publisher::where('approval_status', 'pending')->count(),
            'lowStockCount' => DB::table('inventories')->whereColumn('quantity', '<=', 'low_stock_threshold')->count(),
            'recentPayments' => Payment::with('order.customer')->where('verified_status', 'pending')->latest()->limit(6)->get(),
            'recentOrders' => Order::with('customer')->latest()->limit(6)->get(),
            'statusMix' => Order::selectRaw('status, count(*) as c')->groupBy('status')->pluck('c', 'status'),
            'salesByCountry' => $salesByCountryData,
            'topSellingBooks' => $topSellingBooksData,
            'lowStockBooks' => $lowStockBooksList,
            'recentCustomers' => $recentCustomersList,
            'weeklyTrend' => $lastSevenDays->map(fn ($date) => [
                'label' => $date->format('D, d M'),
                'orders' => (int) ($dailyOrders[$date->toDateString()] ?? 0),
                'revenue' => (float) ($dailyRevenue[$date->toDateString()] ?? 0),
            ]),
        ]);
    }
}
