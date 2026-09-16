<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'customer_id', 'status', 'tracking_number', 'country', 'currency', 'shipping_address', 'shipping_phone',
        'subtotal', 'shipping_fee', 'tax_amount', 'tax_rate', 'is_tax_inclusive', 'platform_fee', 'coupon_id', 'discount_amount', 'total_amount',
        'exchange_rate', 'base_subtotal', 'base_shipping_fee', 'base_tax_amount', 'base_discount_amount', 'base_total_amount',
    ];

    const STATUSES = [
        'pending_payment', 'confirmed', 'processing', 'packed', 'shipped',
        'delivered', 'completed', 'cancelled', 'return_requested', 'returned',
    ];

    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function coupon() { return $this->belongsTo(Coupon::class); }
    public function payment() { return $this->hasOne(Payment::class); }
    public function statusHistory() { return $this->hasMany(OrderStatusHistory::class)->latest('created_at'); }
    public function getCurrencySymbolAttribute(): string
    {
        return ['INR'=>'₹', 'USD'=>'$', 'GBP'=>'£', 'AED'=>'AED ', 'EUR'=>'€', 'CAD'=>'C$', 'AUD'=>'A$'][$this->currency] ?? $this->currency.' ';
    }

    /** FR-8 fix: every transition writes an audit row, not just the column. */
    public function transitionTo(string $newStatus, ?int $actorId = null): void
    {
        $old = $this->status;
        $this->update(['status' => $newStatus]);
        $this->statusHistory()->create([
            'from_status' => $old,
            'to_status' => $newStatus,
            'changed_by' => $actorId,
        ]);
    }
}
