<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\SiteSetting;
use App\Services\CurrencyService;
use App\Services\InventoryService;
use App\Services\PricingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function index(Request $request, PricingService $pricing, CurrencyService $currencyService)
    {
        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');

        $selectedCountry = $currencyService->getSelectedCountry();
        $couponCode = $request->session()->get('checkout_coupon');
        $appliedCoupon = $couponCode ? Coupon::where('code', $couponCode)->first() : null;

        $quote = $pricing->quote($items, $selectedCountry->code, $appliedCoupon);

        $paymentQrUrl = SiteSetting::valueFor('payment_qr');
        $upiId = SiteSetting::valueFor('payment_upi_id', '');
        $domesticBank = [
            'account_name' => SiteSetting::valueFor('bank_account_name', ''),
            'account_number' => SiteSetting::valueFor('bank_account_number', ''),
            'bank_name' => SiteSetting::valueFor('bank_name', ''),
            'ifsc' => SiteSetting::valueFor('bank_ifsc', ''),
            'branch' => SiteSetting::valueFor('bank_branch', ''),
        ];
        $intlBank = [
            'account_name' => SiteSetting::valueFor('intl_bank_account_name', ''),
            'account_number' => SiteSetting::valueFor('intl_bank_account_number', ''),
            'bank_name' => SiteSetting::valueFor('intl_bank_name', ''),
            'swift_bic' => SiteSetting::valueFor('intl_bank_swift', ''),
            'iban' => SiteSetting::valueFor('intl_bank_iban', ''),
            'routing_number' => SiteSetting::valueFor('intl_bank_routing', ''),
            'bank_address' => SiteSetting::valueFor('intl_bank_address', ''),
        ];
        $enabledMethods = [
            'upi_qr' => (bool) SiteSetting::valueFor('method_upi_qr', true),
            'bank_transfer' => (bool) SiteSetting::valueFor('method_bank_transfer', true),
            'international_wire' => (bool) SiteSetting::valueFor('method_intl_wire', true),
        ];

        $countries = Country::where('is_active', true)->orderBy('name')->get();

        return view('pages.checkout', compact(
            'items', 'quote', 'appliedCoupon', 'paymentQrUrl', 'upiId',
            'domesticBank', 'intlBank', 'enabledMethods', 'countries', 'selectedCountry'
        ));
    }

    public function applyCoupon(Request $request, PricingService $pricing)
    {
        $data = $request->validate([
            'coupon_code' => 'required|string|max:40',
            'country' => 'nullable|string|exists:countries,code',
        ]);
        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');

        $code = strtoupper(trim($data['coupon_code']));
        $coupon = Coupon::where('code', $code)->first();
        $subtotal = $coupon ? $pricing->couponSubtotal($items, $coupon) : 0;

        if (! $coupon || $subtotal <= 0 || ! $coupon->isValidFor($subtotal)) {
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
                'quote' => $pricing->quote($items, $data['country'] ?? session('customer_country', 'IN'), $coupon),
            ]);
        }

        return back()->with('success', 'Coupon applied successfully.');
    }

    public function store(Request $request, PricingService $pricing, InventoryService $inventoryService)
    {
        $activeCountryCodes = Country::where('is_active', true)->pluck('code')->toArray();

        $data = $request->validate([
            'shipping_address' => 'required|string',
            'shipping_phone' => 'nullable|string|max:30',
            'country' => 'required|in:' . implode(',', $activeCountryCodes),
            'coupon_code' => 'nullable|string',
            'payment_method' => 'required|in:upi_qr,bank_transfer,international_wire',
            'utr_number' => 'required|string|max:50',
            'proof' => 'required|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',
        ]);

        $items = Cart::with('book')->where('customer_id', auth()->id())->get();
        abort_if($items->isEmpty(), 404, 'Your cart is empty.');

        $couponCode = $data['coupon_code'] ?? null;
        $coupon = $couponCode ? Coupon::where('code', strtoupper(trim($couponCode)))->first() : null;
        if ($coupon) {
            $couponSubtotal = $pricing->couponSubtotal($items, $coupon);
            if ($couponSubtotal <= 0 || ! $coupon->isValidFor($couponSubtotal)) $coupon = null;
        }
        $quote = $pricing->quote($items, $data['country'], $coupon);

        $order = DB::transaction(function () use ($data, $items, $quote, $coupon, $inventoryService) {
            $order = Order::create([
                'customer_id' => auth()->id(),
                'status' => 'pending_payment',
                'country' => $data['country'],
                'currency' => $quote['currency'],
                'exchange_rate' => $quote['rate'],
                'shipping_address' => $data['shipping_address'],
                'shipping_phone' => $data['shipping_phone'] ?? null,
                'subtotal' => $quote['subtotal'],
                'shipping_fee' => $quote['shipping'],
                'platform_fee' => $quote['platformFee'],
                'coupon_id' => $coupon?->id,
                'discount_amount' => $quote['discount'],
                'total_amount' => $quote['total'],
                'base_total_amount' => $quote['baseTotal'],
            ]);
            $order->statusHistory()->create(['to_status' => 'pending_payment', 'changed_by' => auth()->id()]);

            foreach ($items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'book_id' => $item->book_id,
                    'quantity' => $item->quantity,
                    'unit_price' => round((float) $item->book->price * $quote['rate'], 2),
                    'base_unit_price' => $item->book->price,
                ]);
                $inventoryService->recordSale($item->book, $item->quantity, $order->id);
            }

            if ($coupon) $coupon->increment('times_used');

            $proofPath = null;
            if (request()->hasFile('proof')) {
                $proofPath = request()->file('proof')->store('payment-proofs', 'public');
            }

            Payment::create([
                'order_id' => $order->id,
                'payment_method' => $data['payment_method'],
                'utr_number' => $data['utr_number'],
                'proof_url' => $proofPath,
                'verified_status' => 'pending',
            ]);

            Cart::where('customer_id', auth()->id())->delete();
            return $order;
        });

        $request->session()->forget('checkout_coupon');

        return redirect()->route('account.orders')->with('success', "Order #{$order->id} placed successfully! We'll process your order after payment verification.");
    }
}
