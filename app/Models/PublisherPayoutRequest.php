<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PublisherPayoutRequest extends Model
{
    protected $fillable = ['publisher_id', 'amount', 'status', 'payment_details', 'reference', 'admin_note', 'reviewed_by', 'reviewed_at', 'paid_at'];
    protected $casts = ['amount' => 'decimal:2', 'reviewed_at' => 'datetime', 'paid_at' => 'datetime'];
    public function publisher() { return $this->belongsTo(Publisher::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
}
