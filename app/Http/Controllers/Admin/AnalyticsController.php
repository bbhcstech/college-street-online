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
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.analytics', $this->reportData($request));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'pdf'], true), 404);
        $data = $this->reportData($request);
        $filename = 'admin-analytics-' . $data['period'] . '-' . now()->format('Y-m-d');

        if ($type === 'csv') {
            return new StreamedResponse(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Book', 'Units sold', 'Sales (INR)']);
                foreach ($data['topBooks'] as $book) {
                    fputcsv($handle, [$book->title, $book->units, number_format($book->sales, 2, '.', '')]);
                }
                fclose($handle);
            }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"']);
        }

        return response()->view('admin.analytics-report', $data + ['exportType' => $type])
            ->header('Content-Disposition', $type === 'excel' ? 'attachment; filename="' . $filename . '.xls"' : 'inline')
            ->header('Content-Type', $type === 'excel' ? 'application/vnd.ms-excel' : 'text/html; charset=UTF-8');
    }

    private function reportData(Request $request): array
    {
        $period = in_array($request->query('period'), ['7', '30', '90', '365', 'all', 'custom'], true)
            ? $request->query('period')
            : '30';

        $dateFrom = null;
        $dateTo = null;

        if ($period === 'custom') {
            $dateFrom = $request->filled('date_from') ? \Carbon\Carbon::parse($request->query('date_from'))->startOfDay() : null;
            $dateTo = $request->filled('date_to') ? \Carbon\Carbon::parse($request->query('date_to'))->endOfDay() : null;
        } elseif ($period !== 'all') {
            $dateFrom = now()->subDays((int) $period - 1)->startOfDay();
        }

        $applyDateFilter = function ($query, $column = 'created_at') use ($dateFrom, $dateTo) {
            return $query->when($dateFrom, fn ($q) => $q->where($column, '>=', $dateFrom))
                         ->when($dateTo, fn ($q) => $q->where($column, '<=', $dateTo));
        };

        $orders = $applyDateFilter(Order::query());
        $paidOrders = $applyDateFilter(
            Order::query()
                ->whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))
                ->where('status', '!=', 'cancelled')
        );

        $useMonths = $period === '365' || $period === 'all';
        $dateExpression = $useMonths ? "DATE_FORMAT(orders.created_at, '%Y-%m')" : 'DATE(orders.created_at)';
        $revenueTrend = (clone $paidOrders)
            ->selectRaw("{$dateExpression} as period, SUM(base_total_amount) as revenue, COUNT(*) as orders")
            ->groupBy('period')->orderBy('period')->get();

        $statuses = ['pending_payment', 'confirmed', 'processing', 'shipped', 'delivered', 'completed', 'cancelled'];
        $statusCounts = (clone $orders)->selectRaw('status, COUNT(*) as total')
            ->whereIn('status', $statuses)->groupBy('status')->pluck('total', 'status');

        $topBooksQuery = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('books', 'books.id', '=', 'order_items.book_id')
            ->where('orders.status', '!=', 'cancelled');

        $topBooks = $applyDateFilter($topBooksQuery, 'orders.created_at')
            ->selectRaw('books.id, books.title, SUM(order_items.quantity) as units, SUM(order_items.quantity * order_items.base_unit_price) as sales')
            ->groupBy('books.id', 'books.title')->orderByDesc('units')->limit(8)->get();

        $salesByCountry = (clone $paidOrders)
            ->selectRaw("COALESCE(NULLIF(country, ''), 'India') as country_name, SUM(base_total_amount) as sales, COUNT(*) as orders")
            ->groupBy('country_name')->orderByDesc('sales')->limit(6)->get();

        return [
            'period' => $period,
            'dateFrom' => $request->query('date_from'),
            'dateTo' => $request->query('date_to'),
            'revenue' => (float) (clone $paidOrders)->sum('base_total_amount'),
            'orderVolume' => (clone $orders)->count(),
            'averageOrder' => (float) (clone $paidOrders)->avg('base_total_amount'),
            'totalCustomers' => $applyDateFilter(User::where('role', 'customer'))->count(),
            'revenueTrend' => $revenueTrend,
            'salesByCountry' => $salesByCountry,
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
        ];
    }
}
