<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('customer')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->latest()->paginate(20)->withQueryString();
        return view('admin.orders.index', compact('orders'));
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
        $order->transitionTo($data['status'], auth()->id());
        return back()->with('success', 'Order status updated.');
    }

    /** FR-7: manual UTR verification — this is the confirmation gate until a payment gateway is integrated (Section 10.2). */
    public function verifyPayment(Request $request, Payment $payment)
    {
        $data = $request->validate(['decision' => 'required|in:verified,rejected']);
        $payment->update(['verified_status' => $data['decision'], 'verified_by' => auth()->id(), 'verified_at' => now()]);
        if ($data['decision'] === 'verified') {
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
