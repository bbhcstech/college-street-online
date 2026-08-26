<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
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
        $filename = 'publisher-sales-' . $data['period'] . '-' . now()->format('Y-m-d');

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
        $period = in_array($request->query('period'), ['week', 'month', 'year'], true) ? $request->query('period') : 'month';
        [$start, $end, $points] = $this->periodDetails($period);

        $sales = DB::table('order_items')->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')->where('books.publisher_id', $publisherId)
            ->whereBetween('orders.created_at', [$start, $end])->whereIn('orders.status', self::SALE_STATUSES);

        $bucketExpression = match ($period) {
            'year' => "DATE_FORMAT(orders.created_at, '%Y-%m')",
            'month' => 'CEIL(DAY(orders.created_at) / 7)',
            default => 'DATE(orders.created_at)',
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

        return compact('period', 'start', 'end', 'series', 'topBooks', 'statusMix', 'revenue', 'orders') + [
            'units' => (int) (clone $sales)->sum('order_items.quantity'),
            'averageOrder' => $orders ? $revenue / $orders : 0,
        ];
    }

    private function periodDetails(string $period): array
    {
        $end = now()->endOfDay();
        if ($period === 'week') {
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
