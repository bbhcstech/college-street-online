<?php

namespace App\Services;

use App\Models\Cart as CartModel;
use App\Models\Country;
use App\Models\Coupon;
use App\Models\Currency;
use Illuminate\Support\Collection;

class PricingService
{
    public function quote(Collection $cartItems, ?string $countryCode = null, ?Coupon $coupon = null): array
    {
        $currencyService = app(CurrencyService::class);
        $country = $countryCode ? Country::where('code', $countryCode)->first() : null;
        $country = $country ?? $currencyService->getSelectedCountry();

        $currency = Currency::where('code', $country->currency_code)->first();
        $rate = (float) ($currency?->exchange_rate ?? 1.0);

        // Sum subtotal in target country's currency
        $subtotal = $cartItems->sum(function (CartModel $cart) use ($currencyService, $country) {
            $priceData = $currencyService->resolveBookPrice($cart->book, $country);
            return $cart->quantity * ($priceData['price'] ?? (float) $cart->book->price);
        });

        $baseSubtotal = $cartItems->sum(fn (CartModel $c) => $c->quantity * (float) $c->book->price);
        $totalItems = $cartItems->sum('quantity');

        // Multi-publisher shipment shipping fee calculation
        $publisherGroups = $cartItems->groupBy(fn (CartModel $c) => $c->book->publisher_id);
        $shipping = 0.00;

        if ($country->free_shipping_threshold && $subtotal >= $country->free_shipping_threshold) {
            $shipping = 0.00;
        } else {
            foreach ($publisherGroups as $pubId => $pubItems) {
                $pubItemCount = $pubItems->sum('quantity');
                $pubShipFee = (float) $country->base_shipping_fee + ($pubItemCount > 1 ? ($pubItemCount - 1) * (float) $country->per_item_shipping_fee : 0);
                $shipping += $pubShipFee;
            }
            $shipping = round($shipping, 2);
        }

        $baseShipping = $rate > 0 ? round($shipping / $rate, 2) : $shipping;

        // Discount calculation
        $discount = 0.00;
        $baseDiscount = 0.00;

        if ($coupon && $subtotal > 0) {
            $couponSubtotal = $this->couponSubtotal($cartItems, $coupon, $country);
            if ($couponSubtotal > 0 && $coupon->isValidFor($couponSubtotal)) {
                $discount = round($coupon->computeDiscount($couponSubtotal), 2);
                $baseDiscount = $rate > 0 ? round($discount / $rate, 2) : $discount;
            }
        }

        // Tax calculation
        $taxRate = (float) ($country->tax_rate ?? 0.00);
        $isTaxInclusive = (bool) $country->is_tax_inclusive;
        $taxableSubtotal = max(0, $subtotal - $discount);

        if ($taxRate > 0 && $taxableSubtotal > 0) {
            if ($isTaxInclusive) {
                $tax = round($taxableSubtotal * ($taxRate / (100 + $taxRate)), 2);
            } else {
                $tax = round($taxableSubtotal * ($taxRate / 100), 2);
            }
        } else {
            $tax = 0.00;
        }

        $baseTax = $rate > 0 ? round($tax / $rate, 2) : $tax;

        // Final Totals
        if ($isTaxInclusive) {
            $total = max(0, round($subtotal + $shipping - $discount, 2));
            $baseTotal = max(0, round($baseSubtotal + $baseShipping - $baseDiscount, 2));
        } else {
            $total = max(0, round($subtotal + $shipping + $tax - $discount, 2));
            $baseTotal = max(0, round($baseSubtotal + $baseShipping + $baseTax - $baseDiscount, 2));
        }

        return [
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'tax' => $tax,
            'taxRate' => $taxRate,
            'isTaxInclusive' => $isTaxInclusive,
            'platformFee' => 0.00,
            'discount' => $discount,
            'total' => $total,
            'baseSubtotal' => $baseSubtotal,
            'baseShipping' => $baseShipping,
            'baseTax' => $baseTax,
            'basePlatformFee' => 0.00,
            'baseDiscount' => $baseDiscount,
            'baseTotal' => $baseTotal,
            'rate' => $rate,
            'country' => $country->code,
            'currency' => $country->currency_code,
            'symbol' => $country->symbol,
            'publishersCount' => $publisherGroups->count(),
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
                return $cart->quantity * ($priceData['price'] ?? (float) $cart->book->price);
            });
    }
}
