<?php
namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Services\InventoryService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(Request $request, PricingService $pricing)
    {
        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');
        $couponCode = $request->session()->get('checkout_coupon');
        $appliedCoupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;
        $quote = $pricing->quote($items, 'IN', $appliedCoupon);
        $paymentQrUrl = SiteSetting::valueFor('payment_qr');
        return view('pages.checkout', compact('items', 'quote', 'appliedCoupon', 'paymentQrUrl'));
    }

    public function applyCoupon(Request $request, PricingService $pricing)
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:40',
            'country' => 'nullable|string|size:2',
        ]);
        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');

        $code = strtoupper(trim($data['coupon_code']));
        $coupon = Coupon::where('code', $code)->first();
        $subtotal = $pricing->quote($items)['subtotal'];

        if (! $coupon || ! $coupon->isValidFor($subtotal)) {
            $request->session()->forget('checkout_coupon');

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This coupon is invalid, expired, or not applicable.',
                ], 422);
            }

            return back()->withErrors(['coupon_code' => 'This coupon is invalid, expired, or not applicable.']);
        }

        $request->session()->put('checkout_coupon', $coupon->code);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Coupon applied successfully.',
                'code' => $coupon->code,
                'quote' => $pricing->quote($items, $data['country'] ?? 'IN', $coupon),
            ]);
        }

        return back()->with('success', 'Coupon applied successfully.');
    }

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

        $couponCode = $data['coupon_code'] ?? null;
        $coupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;
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

        $request->session()->forget('checkout_coupon');

        return redirect()->route('account.orders')->with('success', "Order #{$order->id} placed! We'll confirm once your payment is verified.");
    }
}
