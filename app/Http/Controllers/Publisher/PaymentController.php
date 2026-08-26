<?php

namespace App\Http\Controllers\Publisher;

use App\Http\Controllers\Controller;
use App\Models\Order;
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

        return view('publisher.payments', compact('orders', 'period', 'paymentStatus', 'totals'));
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
}
