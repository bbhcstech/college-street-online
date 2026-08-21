<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'discount_type', 'discount_value', 'min_order_value',
        'valid_from', 'valid_to', 'usage_limit', 'times_used', 'is_active',
    ];
    protected $casts = ['valid_from' => 'datetime', 'valid_to' => 'datetime', 'is_active' => 'boolean'];

    /**
     * FR-9 fix: revalidated server-side at the moment of order placement
     * (not only when first applied to the cart) and the computed discount
     * is always clamped to the order subtotal, regardless of what the
     * coupon rule alone would produce.
     */
    public function isValidFor(float $subtotal): bool
    {
        if (! $this->is_active) return false;
        if ($this->valid_from && now()->lt($this->valid_from)) return false;
        if ($this->valid_to && now()->gt($this->valid_to)) return false;
        if ($this->min_order_value && $subtotal < $this->min_order_value) return false;
        if ($this->usage_limit && $this->times_used >= $this->usage_limit) return false;
        return true;
    }

    public function computeDiscount(float $subtotal): float
    {
        $discount = $this->discount_type === 'percentage'
            ? $subtotal * ((float) $this->discount_value / 100)
            : (float) $this->discount_value;

        return min($discount, $subtotal); // never exceed order subtotal
    }
}
