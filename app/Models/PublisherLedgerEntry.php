<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherLedgerEntry extends Model
{
    protected $fillable = ['publisher_id', 'order_id', 'order_item_id', 'payout_request_id', 'type', 'reference_key', 'gross_amount', 'discount_amount', 'commission_amount', 'amount', 'description', 'available_at'];
    protected $casts = ['available_at' => 'datetime', 'gross_amount' => 'decimal:2', 'discount_amount' => 'decimal:2', 'commission_amount' => 'decimal:2', 'amount' => 'decimal:2'];
    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function order() { return $this->belongsTo(Order::class); }
    public function payoutRequest() { return $this->belongsTo(PublisherPayoutRequest::class); }
}
