<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Services\InventoryService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(PricingService $pricing)
    {
        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');
        $quote = $pricing->quote($items, 'IN');
        return view('pages.checkout', compact('items', 'quote'));
    }

    /**
     * FR-6/FR-7: totals are always recomputed server-side here via
     * PricingService — never trusted from client-submitted values — and
     * the order starts pending_payment until an admin manually verifies
     * the UTR (FR-7: no gateway integration yet, see SRS Section 10.2).
     */
    public function store(Request $request, PricingService $pricing, InventoryService $inventoryService)
    {
        $data = $request->validate([
            'shipping_address' => 'required|string',
            'shipping_phone' => 'nullable|string|max:30',
            'country' => 'nullable|string|size:2',
            'coupon_code' => 'nullable|string',
            'utr_number' => 'required|string|max:50',
            'proof' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');

        $coupon = $data['coupon_code'] ? Coupon::where('code', $data['coupon_code'])->first() : null;
        $quote = $pricing->quote($items, $data['country'] ?? 'IN', $coupon);

        $order = DB::transaction(function () use ($data, $items, $quote, $coupon, $inventoryService) {
            $order = Order::create([
                'customer_id' => auth()->id(),
                'status' => 'pending_payment',
                'country' => $data['country'] ?? 'IN',
                'currency' => 'INR',
                'shipping_address' => $data['shipping_address'],
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'subtotal' => $quote['subtotal'],
                'shipping_fee' => $quote['shipping'],
                'platform_fee' => $quote['platformFee'],
                'coupon_id' => $coupon?->id,
                'discount_amount' => $quote['discount'],
                'total_amount' => $quote['total'],
            ]);
            $order->statusHistory()->create(['to_status' => 'pending_payment', 'changed_by' => auth()->id()]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id, 'book_id' => $item->book_id,
                    'quantity' => $item->quantity, 'unit_price' => $item->book->price,
                ]);
                $inventoryService->recordSale($item->book, $item->quantity, $order->id);
            }

            if ($coupon) $coupon->increment('times_used');

            $proofPath = null;
            if (request()->hasFile('proof')) {
                $proofPath = request()->file('proof')->store('payment-proofs', 'public');
            }
            Payment::create([
                'order_id' => $order->id, 'utr_number' => $data['utr_number'],
                'proof_url' => $proofPath, 'verified_status' => 'pending',
            ]);

            Cart::where('customer_id', auth()->id())->delete();
            return $order;
        });

        return redirect()->route('account.orders')->with('success', "Order #{$order->id} placed! We'll confirm once your payment is verified.");
    }
}
