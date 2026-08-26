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

        return view('admin.dashboard', [
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
            'weeklyTrend' => $lastSevenDays->map(fn ($date) => [
                'label' => $date->format('D'),
                'orders' => (int) ($dailyOrders[$date->toDateString()] ?? 0),
                'revenue' => (float) ($dailyRevenue[$date->toDateString()] ?? 0),
            ]),
        ]);
    }
}
