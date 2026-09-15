<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\PublisherLedgerEntry;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AnalyticsController extends Controller
{
    private const SALE_STATUSES = ['confirmed', 'processing', 'packed', 'shipped', 'delivered', 'completed'];

    public function index(Request $request)
    {
        return view('publisher.analytics', $this->reportData($request));
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $data = $this->reportData($request, false);
        $report = in_array($request->query('report'), ['sales', 'inventory', 'orders', 'books'], true) ? $request->query('report') : 'sales';
        $data['report'] = $report;
        $filename = 'publisher-' . $report . '-' . $data['period'] . '-' . now()->format('Y-m-d');

        if ($type === 'csv') {
            return new StreamedResponse(function () use ($data) {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Book', 'ISBN', 'Orders', 'Units sold', 'Gross sales (INR)']);
                foreach ($data['topBooks'] as $book) {
                    fputcsv($handle, [$book->title, $book->isbn, $book->orders, $book->units, number_format($book->revenue, 2, '.', '')]);
                }
                fclose($handle);
            }, 200, ['Content-Type' => 'text/csv', 'Content-Disposition' => 'attachment; filename="' . $filename . '.csv"']);
        }

        return response()->view('publisher.analytics-report', $data + ['exportType' => $type])
            ->header('Content-Disposition', $type === 'excel' ? 'attachment; filename="' . $filename . '.xls"' : 'inline')
            ->header('Content-Type', $type === 'excel' ? 'application/vnd.ms-excel' : 'text/html; charset=UTF-8');
    }

    private function reportData(Request $request, bool $limitBooks = true): array
    {
        $publisherId = auth()->user()->publisher->id;
        $period = in_array($request->query('period'), ['day', 'week', 'month', 'year', 'custom'], true) ? $request->query('period') : 'month';
        [$start, $end, $points] = $this->periodDetails($period, $request);

        $deliveryDates = DB::table('order_status_histories')->whereIn('to_status', ['delivered', 'completed'])
            ->selectRaw('order_id, MIN(created_at) as delivered_at')->groupBy('order_id');
        $sales = DB::table('order_items')->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->joinSub($deliveryDates, 'delivery_dates', fn ($join) => $join->on('delivery_dates.order_id', '=', 'orders.id'))
            ->where('books.publisher_id', $publisherId)->whereBetween('delivery_dates.delivered_at', [$start, $end])
            ->whereIn('orders.status', ['delivered', 'completed']);

        $bucketExpression = match ($period) {
            'year' => "DATE_FORMAT(delivery_dates.delivered_at, '%Y-%m')",
            'month' => 'CEIL(DAY(delivery_dates.delivered_at) / 7)',
            default => 'DATE(delivery_dates.delivered_at)',
        };
        $grouped = (clone $sales)->selectRaw("$bucketExpression as bucket, SUM(order_items.quantity) as units, SUM(order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)) as revenue")
            ->groupBy('bucket')->get()->keyBy('bucket');

        $series = $points->map(function ($point) use ($grouped) {
            $row = $grouped->get($point['key']);
            return $point + ['units' => (int) ($row->units ?? 0), 'revenue' => (float) ($row->revenue ?? 0)];
        });

        $topQuery = (clone $sales)->selectRaw('books.id, books.title, books.isbn, COUNT(DISTINCT orders.id) as orders, SUM(order_items.quantity) as units, SUM(order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)) as revenue')
            ->groupBy('books.id', 'books.title', 'books.isbn')->orderByDesc('units');
        $topBooks = $limitBooks ? $topQuery->limit(10)->get() : $topQuery->get();

        $statusMix = DB::table('orders')->join('order_items', 'order_items.order_id', '=', 'orders.id')
            ->join('books', 'books.id', '=', 'order_items.book_id')->where('books.publisher_id', $publisherId)
            ->whereBetween('orders.created_at', [$start, $end])->selectRaw('orders.status, COUNT(DISTINCT orders.id) as total')
            ->groupBy('orders.status')->pluck('total', 'status');

        $revenue = (float) (clone $sales)->sum(DB::raw('order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)'));
        $orders = (int) (clone $sales)->distinct()->count('orders.id');
        $rangeDays = max(1, $start->diffInDays($end) + 1);
        $previousEnd = $start->copy()->subSecond();
        $previousStart = $previousEnd->copy()->subDays($rangeDays - 1)->startOfDay();
        $previousRevenue = (float) DB::table('order_items')->join('books', 'books.id', '=', 'order_items.book_id')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->joinSub($deliveryDates, 'previous_delivery_dates', fn ($join) => $join->on('previous_delivery_dates.order_id', '=', 'orders.id'))
            ->where('books.publisher_id', $publisherId)->whereBetween('previous_delivery_dates.delivered_at', [$previousStart, $previousEnd])->whereIn('orders.status', ['delivered', 'completed'])
            ->sum(DB::raw('order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price)'));
        $salesGrowth = $previousRevenue > 0 ? (($revenue - $previousRevenue) / $previousRevenue) * 100 : ($revenue > 0 ? 100 : 0);
        $deliveredOrderIds = (clone $sales)->distinct()->pluck('orders.id');
        $netRevenue = (float) PublisherLedgerEntry::where('publisher_id', $publisherId)->where('type', 'earning')->whereIn('order_id', $deliveredOrderIds)->sum('amount');

        $bookPerformance = Book::where('publisher_id', $publisherId)->leftJoin('order_items', 'books.id', '=', 'order_items.book_id')
            ->leftJoin('orders', function ($join) { $join->on('orders.id', '=', 'order_items.order_id')->whereIn('orders.status', ['delivered', 'completed']); })
            ->leftJoinSub($deliveryDates, 'book_delivery_dates', fn ($join) => $join->on('book_delivery_dates.order_id', '=', 'orders.id')->whereBetween('book_delivery_dates.delivered_at', [$start, $end]))
            ->selectRaw('books.id, books.title, books.isbn, books.view_count, COALESCE(SUM(CASE WHEN book_delivery_dates.order_id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as units')
            ->groupBy('books.id', 'books.title', 'books.isbn', 'books.view_count')->get();
        $leastSellingBooks = $bookPerformance->sortBy('units')->take(5)->values();
        $mostViewedBooks = $bookPerformance->sortByDesc('view_count')->take(5)->values();

        $inventory = DB::table('books')->leftJoin('inventories', 'inventories.book_id', '=', 'books.id')->where('books.publisher_id', $publisherId)
            ->selectRaw('COUNT(books.id) as total_books, COALESCE(SUM(inventories.quantity),0) as current_stock, SUM(CASE WHEN inventories.quantity > 0 AND inventories.quantity <= inventories.low_stock_threshold THEN 1 ELSE 0 END) as low_stock, SUM(CASE WHEN inventories.quantity IS NULL OR inventories.quantity <= 0 THEN 1 ELSE 0 END) as out_of_stock')->first();
        $stockMovement = (int) DB::table('inventory_transactions')->join('books', 'books.id', '=', 'inventory_transactions.book_id')->where('books.publisher_id', $publisherId)->whereBetween('inventory_transactions.created_at', [$start, $end])->sum(DB::raw('ABS(inventory_transactions.change_qty)'));
        $inventoryBooks = Book::where('publisher_id', $publisherId)->with('inventory')->orderBy('title')->get();

        $allOrders = DB::table('orders')->join('order_items', 'order_items.order_id', '=', 'orders.id')->join('books', 'books.id', '=', 'order_items.book_id')->where('books.publisher_id', $publisherId)->whereBetween('orders.created_at', [$start, $end]);
        $orderAnalytics = ['total' => (clone $allOrders)->distinct()->count('orders.id'), 'pending' => (clone $allOrders)->whereIn('orders.status', ['pending_payment', 'confirmed', 'processing', 'packed'])->distinct()->count('orders.id'), 'completed' => (clone $allOrders)->whereIn('orders.status', ['delivered', 'completed'])->distinct()->count('orders.id'), 'cancelled' => (clone $allOrders)->where('orders.status', 'cancelled')->distinct()->count('orders.id')];
        $customerOrders = (clone $allOrders)->select('orders.customer_id')->selectRaw('COUNT(DISTINCT orders.id) as order_count')->groupBy('orders.customer_id')->get();
        $customerAnalytics = ['total' => $customerOrders->count(), 'new' => $customerOrders->where('order_count', 1)->count(), 'repeat' => $customerOrders->where('order_count', '>', 1)->count()];

        return compact('period', 'start', 'end', 'series', 'topBooks', 'statusMix', 'revenue', 'orders', 'netRevenue', 'salesGrowth', 'leastSellingBooks', 'mostViewedBooks', 'inventory', 'inventoryBooks', 'stockMovement', 'orderAnalytics', 'customerAnalytics') + [
            'units' => (int) (clone $sales)->sum('order_items.quantity'),
            'averageOrder' => $orders ? $revenue / $orders : 0,
        ];
    }

    private function periodDetails(string $period, Request $request): array
    {
        $end = now()->endOfDay();
        if ($period === 'custom' && $request->filled(['date_from', 'date_to'])) {
            $start = Carbon::parse($request->date_from)->startOfDay();
            $end = Carbon::parse($request->date_to)->endOfDay();
            if ($start->gt($end)) [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
            $days = min(31, $start->diffInDays($end));
            $points = collect(range(0, $days))->map(fn ($i) => ['key' => $start->copy()->addDays($i)->toDateString(), 'label' => $start->copy()->addDays($i)->format('d M')]);
        } elseif ($period === 'day') {
            $start = now()->startOfDay();
            $points = collect([['key' => $start->toDateString(), 'label' => 'Today']]);
        } elseif ($period === 'week') {
            $start = now()->subDays(6)->startOfDay();
            $points = collect(range(0, 6))->map(fn($i) => ['key' => $start->copy()->addDays($i)->toDateString(), 'label' => $start->copy()->addDays($i)->format('D')]);
        } elseif ($period === 'year') {
            $start = now()->startOfYear();
            $points = collect(range(1, 12))->map(fn($month) => ['key' => $start->format('Y') . '-' . str_pad($month, 2, '0', STR_PAD_LEFT), 'label' => Carbon::create(null, $month)->format('M')]);
        } else {
            $start = now()->startOfMonth();
            $points = collect(range(1, (int) ceil(now()->day / 7)))->map(fn($week) => ['key' => $week, 'label' => 'Week '.$week]);
        }
        return [$start, $end, $points];
    }
}
