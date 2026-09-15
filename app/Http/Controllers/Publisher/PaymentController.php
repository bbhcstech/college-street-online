<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PublisherLedgerEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $publisherId = auth()->user()->publisher->id;
        $paymentStatus = in_array($request->query('payment'), ['pending', 'verified', 'rejected'], true) ? $request->query('payment') : null;
        $period = in_array($request->query('period'), ['month', 'year', 'all'], true) ? $request->query('period') : 'month';

        $orders = Order::query()->with(['customer', 'payment', 'items' => fn ($query) => $query
            ->whereHas('book', fn ($book) => $book->where('publisher_id', $publisherId))->with('book')])
            ->whereHas('items.book', fn ($book) => $book->where('publisher_id', $publisherId))
            ->when($paymentStatus, fn ($query) => $query->whereHas('payment', fn ($payment) => $payment->where('verified_status', $paymentStatus)))
            ->when($period === 'month', fn ($query) => $query->where('created_at', '>=', now()->startOfMonth()))
            ->when($period === 'year', fn ($query) => $query->where('created_at', '>=', now()->startOfYear()))
            ->latest()->paginate(15)->withQueryString();

        $totals = DB::table('order_items')->join('books', 'books.id', '=', 'order_items.book_id')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')->leftJoin('payments', 'payments.order_id', '=', 'orders.id')
            ->where('books.publisher_id', $publisherId)
            ->when($period === 'month', fn ($query) => $query->where('orders.created_at', '>=', now()->startOfMonth()))
            ->when($period === 'year', fn ($query) => $query->where('orders.created_at', '>=', now()->startOfYear()))
            ->selectRaw("SUM(CASE WHEN payments.verified_status = 'verified' THEN order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price) ELSE 0 END) as verified")
            ->selectRaw("SUM(CASE WHEN payments.verified_status = 'pending' THEN order_items.quantity * COALESCE(order_items.base_unit_price, order_items.unit_price) ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN payments.verified_status = 'verified' THEN order_items.publisher_commission_amount ELSE 0 END) as deductions")
            ->selectRaw("COUNT(DISTINCT CASE WHEN payments.verified_status = 'verified' THEN orders.id END) as paid_orders")
            ->first();

        $availableBalance = (float) PublisherLedgerEntry::where('publisher_id', $publisherId)->where('available_at', '<=', now())->sum('amount');
        $pendingBalance = (float) PublisherLedgerEntry::where('publisher_id', $publisherId)->where('type', 'earning')->where(fn ($query) => $query->whereNull('available_at')->orWhere('available_at', '>', now()))->sum('amount');
        $reservedBalance = (float) auth()->user()->publisher->payoutRequests()->whereIn('status', ['requested', 'approved'])->sum('amount');
        $payouts = auth()->user()->publisher->payoutRequests()->latest()->limit(10)->get();
        $ledgerEntries = PublisherLedgerEntry::where('publisher_id', $publisherId)->latest()->limit(20)->get();

        return view('publisher.payments', compact('orders', 'period', 'paymentStatus', 'totals', 'availableBalance', 'pendingBalance', 'reservedBalance', 'payouts', 'ledgerEntries'));
    }

    public function invoice(Order $order)
    {
        $publisher = auth()->user()->publisher;
        $order->load(['customer', 'payment', 'items.book']);
        $items = $order->items->filter(fn ($item) => $item->book?->publisher_id === $publisher->id);
        abort_if($items->isEmpty(), 403);

        $gross = $items->sum(fn ($item) => $item->quantity * ($item->base_unit_price ?? $item->unit_price));
        $deductions = (float) $items->sum('publisher_commission_amount');
        $net = $gross - $deductions;

        return view('publisher.payment-invoice', compact('publisher', 'order', 'items', 'gross', 'deductions', 'net'));
    }

    public function statement()
    {
        $publisherId = auth()->user()->publisher->id;
        $entries = PublisherLedgerEntry::where('publisher_id', $publisherId)->oldest()->get();

        return response()->streamDownload(function () use ($entries) {
            $output = fopen('php://output', 'w');
            fputcsv($output, ['Date', 'Type', 'Description', 'Order', 'Gross', 'Publisher Discount', 'Commission', 'Net Change', 'Available On']);
            foreach ($entries as $entry) fputcsv($output, [$entry->created_at->format('Y-m-d H:i'), $entry->type, $entry->description, $entry->order_id ? 'CSO'.$entry->order_id : '', $entry->gross_amount, $entry->discount_amount, $entry->commission_amount, $entry->amount, $entry->available_at?->format('Y-m-d H:i')]);
            fclose($output);
        }, 'publisher-settlement-'.now()->format('Y-m-d').'.csv');
    }
}
