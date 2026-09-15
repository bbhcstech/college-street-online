<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InventoryTransaction;
use App\Models\Order;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Services\InventoryService;
use App\Services\PublisherSettlementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $orders = $this->filteredQuery($request)->latest()->paginate($perPage)->withQueryString();

        $statusCounts = Order::selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $countries = Order::whereNotNull('country')->where('country', '!=', '')->distinct()->pluck('country')->sort()->values();

        return view('admin.orders.index', [
            'orders' => $orders,
            'countries' => $countries,
            'totalOrders' => Order::count(),
            'pendingOrders' => (int) ($statusCounts['pending_payment'] ?? 0),
            'processingOrders' => (int) ($statusCounts['processing'] ?? 0),
            'shippedOrders' => (int) ($statusCounts['shipped'] ?? 0),
            'deliveredOrders' => (int) ($statusCounts['delivered'] ?? 0) + (int) ($statusCounts['completed'] ?? 0),
            'cancelledOrders' => (int) ($statusCounts['cancelled'] ?? 0),
            'returnedOrders' => (int) ($statusCounts['return_requested'] ?? 0) + (int) ($statusCounts['returned'] ?? 0),
            'totalRevenue' => Order::whereHas('payment', fn ($query) => $query->where('verified_status', 'verified'))->sum('base_total_amount'),
        ]);
    }

    public function export(Request $request, string $type)
    {
        abort_unless(in_array($type, ['csv', 'excel', 'print', 'pdf'], true), 404);
        $orders = $this->filteredQuery($request)
            ->when($request->filled('ids'), fn ($query) => $query->whereIn('orders.id', collect(explode(',', $request->query('ids')))->filter(fn ($id) => ctype_digit($id))))
            ->latest()->get();

        if ($type === 'csv') {
            return response()->streamDownload(function () use ($orders) {
                $output = fopen('php://output', 'w');
                fputcsv($output, ['Order', 'Date', 'Customer', 'Email', 'Total', 'Currency', 'Payment', 'Status']);
                foreach ($orders as $order) {
                    fputcsv($output, ['CSO'.$order->id, $order->created_at->format('Y-m-d H:i'), $order->customer?->name, $order->customer?->email, $order->total_amount, $order->currency, $order->payment?->verified_status ?? 'No payment', $order->status]);
                }
                fclose($output);
            }, 'orders-'.now()->format('Y-m-d').'.csv');
        }

        if ($type === 'excel') {
            return response()->view('admin.orders.report', compact('orders') + ['mode' => 'excel'])
                ->header('Content-Type', 'application/vnd.ms-excel')
                ->header('Content-Disposition', 'attachment; filename="orders-'.now()->format('Y-m-d').'.xls"');
        }

        return view('admin.orders.report', compact('orders') + ['mode' => $type]);
    }

    private function filteredQuery(Request $request)
    {
        return Order::query()->with(['customer', 'payment'])->withCount('items')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->query('q'));
                $orderId = preg_replace('/\D/', '', $term);
                $query->where(function ($search) use ($term, $orderId) {
                    if ($orderId !== '') $search->orWhere('id', (int) $orderId);
                    $search->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%"));
                });
            })
            ->when($request->filled('status'), fn ($query) => $query->where('status', $request->query('status')))
            ->when($request->filled('payment'), function ($query) use ($request) {
                $request->payment === 'none'
                    ? $query->whereDoesntHave('payment')
                    : $query->whereHas('payment', fn ($payment) => $payment->where('verified_status', $request->payment));
            })
            ->when($request->filled('country'), fn ($query) => $query->where('country', $request->query('country')))
            ->when($request->filled('min_price'), fn ($query) => $query->where('base_total_amount', '>=', (float) $request->query('min_price')))
            ->when($request->filled('max_price'), fn ($query) => $query->where('base_total_amount', '<=', (float) $request->query('max_price')))
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->query('date_to')));
    }

    public function show(Order $order)
    {
        $order->load(['items.book', 'customer', 'payment', 'statusHistory.actor', 'coupon']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate([
            'status' => 'required|in:pending_payment,confirmed,processing,packed,shipped,delivered,completed,cancelled,return_requested,returned',
            'tracking_number' => 'nullable|string|max:100',
        ]);

        DB::transaction(function () use ($order, $data) {
            if (!empty($data['tracking_number'])) {
                $order->tracking_number = $data['tracking_number'];
            }

            if ($data['status'] === 'cancelled') {
                $order->load('items.book');
                $inventory = app(InventoryService::class);

                foreach ($order->items as $item) {
                    $alreadyRestored = InventoryTransaction::where('order_id', $order->id)
                        ->where('book_id', $item->book_id)
                        ->where('transaction_type', 'cancel')
                        ->exists();

                    if (! $alreadyRestored && $item->book) {
                        $inventory->recordCancel($item->book, $item->quantity, $order->id);
                    }
                }
            }

            $order->transitionTo($data['status'], auth()->id());
            if (in_array($data['status'], ['delivered', 'completed'], true)) {
                $order->load('items');
                foreach ($order->items as $item) {
                    if ($item->fulfillment_status === $data['status']) continue;
                    $oldFulfillment = $item->fulfillment_status;
                    $item->update(['fulfillment_status' => $data['status']]);
                    $item->statusHistory()->create([
                        'from_status' => $oldFulfillment,
                        'to_status' => $data['status'],
                        'changed_by' => auth()->id(),
                    ]);
                }
            }
            if (in_array($data['status'], ['delivered', 'completed'], true)) app(PublisherSettlementService::class)->scheduleRelease($order);
            if (in_array($data['status'], ['cancelled', 'returned'], true)) app(PublisherSettlementService::class)->recordReversal($order, ucfirst($data['status']).' order #CSO'.$order->id);
        });

        return back()->with('success', 'Order status updated successfully.');
    }

    public function verifyPayment(Request $request, Payment $payment)
    {
        $data = $request->validate([
            'decision' => 'required|in:verified,rejected',
            'rejection_reason' => 'nullable|string|max:500',
            'admin_notes' => 'nullable|string|max:500',
        ]);

        $payment->update([
            'verified_status' => $data['decision'],
            'rejection_reason' => $data['rejection_reason'] ?? null,
            'admin_notes' => $data['admin_notes'] ?? null,
            'verified_by' => auth()->id(),
            'verified_at' => now(),
        ]);

        if ($data['decision'] === 'verified') {
            $commissionRate = (float) (SiteSetting::where('key', 'publisher_commission_rate')->value('value') ?? 0);
            app(PublisherSettlementService::class)->recordVerifiedOrder($payment->order, $commissionRate);
            $payment->order->transitionTo('confirmed', auth()->id());
        } elseif ($payment->order) {
            app(PublisherSettlementService::class)->recordReversal($payment->order, 'Payment rejected for order #CSO'.$payment->order_id);
        }

        return back()->with('success', 'Payment status updated to ' . $data['decision'] . '.');
    }

    public function paymentProof(Payment $payment)
    {
        abort_unless($payment->proof_url && Storage::disk('public')->exists($payment->proof_url), 404);

        return response()->file(Storage::disk('public')->path($payment->proof_url));
    }
}
