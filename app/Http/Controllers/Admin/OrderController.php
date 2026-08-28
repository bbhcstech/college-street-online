<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\InventoryTransaction;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $perPage = in_array((int) $request->query('per_page'), [10, 25, 50, 100], true) ? (int) $request->query('per_page') : 10;
        $orders = $this->filteredQuery($request)->latest()->paginate($perPage)->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'totalOrders' => Order::count(),
            'pendingOrders' => Order::where('status', 'pending_payment')->count(),
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
        return Order::query()->with(['customer', 'payment'])
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
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('created_at', '>=', $request->query('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('created_at', '<=', $request->query('date_to')));
    }

    public function show(Order $order)
    {
        $order->load(['items.book', 'customer', 'payment', 'statusHistory.actor', 'coupon']);
        return view('admin.orders.show', compact('order'));
    }

    /** FR-8 fix: writes to order_status_history via Order::transitionTo(), not just the column. */
    public function updateStatus(Request $request, Order $order)
    {
        $data = $request->validate(['status' => 'required|in:pending_payment,confirmed,processing,packed,shipped,delivered,completed,cancelled,return_requested,returned']);

        DB::transaction(function () use ($order, $data) {
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
        });

        return back()->with('success', 'Order status updated.');
    }

    /** FR-7: manual UTR verification — this is the confirmation gate until a payment gateway is integrated (Section 10.2). */
    public function verifyPayment(Request $request, Payment $payment)
    {
        $data = $request->validate(['decision' => 'required|in:verified,rejected']);
        $payment->update(['verified_status' => $data['decision'], 'verified_by' => auth()->id(), 'verified_at' => now()]);
        if ($data['decision'] === 'verified') {
            $commissionRate = (float) (SiteSetting::where('key', 'publisher_commission_rate')->value('value') ?? 0);
            foreach ($payment->order->items as $item) {
                $gross = $item->quantity * ($item->base_unit_price ?? $item->unit_price);
                $item->update([
                    'publisher_commission_rate' => $commissionRate,
                    'publisher_commission_amount' => round($gross * $commissionRate / 100, 2),
                ]);
            }
            $payment->order->transitionTo('confirmed', auth()->id());
        }
        return back()->with('success', 'Payment ' . $data['decision'] . '.');
    }

    public function paymentProof(Payment $payment)
    {
        abort_unless($payment->proof_url && Storage::disk('public')->exists($payment->proof_url), 404);

        return response()->file(Storage::disk('public')->path($payment->proof_url));
    }
}
