<?php

namespace App\Services;

use App\Models\Cart as CartModel;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use Illuminate\Support\Collection;

class PricingService
{
    const PLATFORM_FEE = 0.00;

    public function quote(Collection $cartItems, string $countryCode = 'IN', ?Coupon $coupon = null): array
    {
        $country = Country::where('code', $countryCode)->first() ?? Country::where('code', 'IN')->first();
        $currency = Currency::where('code', $country->currency_code)->first();
        $rate = (float) ($currency?->exchange_rate ?? 1.0);

        $currencyService = app(CurrencyService::class);

        // Sum subtotal in target country's currency
        $subtotal = $cartItems->sum(function (CartModel $cart) use ($currencyService, $country) {
            $priceData = $currencyService->resolveBookPrice($cart->book, $country);
            return $cart->quantity * $priceData['price'];
        });

        $baseSubtotal = $cartItems->sum(fn (CartModel $c) => $c->quantity * (float) $c->book->price);
        $totalItems = $cartItems->sum('quantity');

        // Calculate shipping in target currency
        $shipping = $currencyService->calculateShippingFee($totalItems, $subtotal, $country);
        $baseShipping = $rate > 0 ? round($shipping / $rate, 2) : $shipping;

        $platformFee = 0.00;
        $basePlatformFee = 0.00;

        $discount = 0.00;
        $baseDiscount = 0.00;

        if ($coupon && $subtotal > 0) {
            $couponSubtotal = $this->couponSubtotal($cartItems, $coupon, $country);
            if ($couponSubtotal > 0 && $coupon->isValidFor($couponSubtotal)) {
                $discount = round($coupon->computeDiscount($couponSubtotal), 2);
                $baseDiscount = $rate > 0 ? round($discount / $rate, 2) : $discount;
            }
        }

        $total = max(0, round($subtotal + $shipping + $platformFee - $discount, 2));
        $baseTotal = max(0, round($baseSubtotal + $baseShipping + $basePlatformFee - $baseDiscount, 2));

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'platformFee' => $platformFee,
            'discount' => $discount,
            'total' => $total,
            'baseTotal' => $baseTotal,
            'rate' => $rate,
            'country' => $country->code,
            'currency' => $country->currency_code,
            'symbol' => $country->symbol,
        ];
    }

    public function couponSubtotal(Collection $cartItems, Coupon $coupon, ?Country $country = null): float
    {
        $currencyService = app(CurrencyService::class);

        return $cartItems
            ->when($coupon->publisher_id, fn (Collection $items) => $items->filter(
                fn (CartModel $cart) => (int) $cart->book->publisher_id === (int) $coupon->publisher_id
            ))
            ->sum(function (CartModel $cart) use ($currencyService, $country) {
                $priceData = $currencyService->resolveBookPrice($cart->book, $country);
                return $cart->quantity * $priceData['price'];
            });
    }
}
