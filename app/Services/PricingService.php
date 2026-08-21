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
        $subtotal = $cartItems->sum(fn (CartModel $c) => $c->quantity * (float) $c->book->price);

        $shipping = $this->shippingFor($subtotal, $country, $cartItems);
        $platformFee = self::PLATFORM_FEE;

        $discount = 0.0;
        if ($coupon && $coupon->isValidFor($subtotal)) {
            $discount = $coupon->computeDiscount($subtotal);
        }

        $total = max(0, $subtotal + $shipping + $platformFee - $discount);

        return compact('subtotal', 'shipping', 'platformFee', 'discount', 'total');
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
