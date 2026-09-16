<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id',
        'payment_method',
        'utr_number',
        'proof_url',
        'verified_status',
        'rejection_reason',
        'admin_notes',
        'verified_by',
        'verified_at',
    ];
    protected $casts = ['verified_at' => 'datetime'];

    public function order() { return $this->belongsTo(Order::class); }
}
