<?php
namespace App\Services;

use App\Models\Cart as CartModel;
use App\Models\Coupon;
use Illuminate\Support\Collection;

/**
 * FR-6 fix: shipping and platform-fee rules live in ONE place, used by both
 * the cart preview and the final checkout calculation, so the two can never
 * drift out of sync (the exact bug called out in the SRS).
 */
class PricingService
{
    const PLATFORM_FEE = 15.00;
    const FREE_SHIPPING_THRESHOLD_INR = 499.00;
    const DOMESTIC_SHIPPING_FEE = 40.00;
    const INTL_RATE_PER_KG = 800.00; // placeholder weight-based international rate

    public function quote(Collection $cartItems, string $country = 'IN', ?Coupon $coupon = null): array
    {
        $country = array_key_exists($country, config('currencies.countries')) ? $country : 'IN';
        $pricing = config("currencies.countries.{$country}");
        $rate = (float) $pricing['rate'];
        $baseSubtotal = $cartItems->sum(fn (CartModel $c) => $c->quantity * (float) $c->book->price);

        $baseShipping = $this->shippingFor($baseSubtotal, $country, $cartItems);
        $basePlatformFee = self::PLATFORM_FEE;

        $baseDiscount = 0.0;
        if ($coupon && $coupon->isValidFor($baseSubtotal)) {
            $baseDiscount = $coupon->computeDiscount($baseSubtotal);
        }

        $baseTotal = max(0, $baseSubtotal + $baseShipping + $basePlatformFee - $baseDiscount);
        $subtotal = round($baseSubtotal * $rate, 2);
        $shipping = round($baseShipping * $rate, 2);
        $platformFee = round($basePlatformFee * $rate, 2);
        $discount = round($baseDiscount * $rate, 2);
        $total = round($baseTotal * $rate, 2);

        return compact('subtotal', 'shipping', 'platformFee', 'discount', 'total', 'baseTotal', 'rate') + [
            'country' => $country, 'currency' => $pricing['currency'], 'symbol' => $pricing['symbol'],
        ];
    }

    protected function shippingFor(float $subtotal, string $country, Collection $cartItems): float
    {
        if ($country === 'IN') {
            return $subtotal >= self::FREE_SHIPPING_THRESHOLD_INR ? 0.0 : self::DOMESTIC_SHIPPING_FEE;
        }
        // International: weight-based placeholder (0.3kg per book average)
        $estWeightKg = $cartItems->sum('quantity') * 0.3;
        return round($estWeightKg * self::INTL_RATE_PER_KG, 2);
    }
}
