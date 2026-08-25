<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkOrderRequest extends Model
{
    protected $fillable = [
        'institution_name', 'contact_name', 'email', 'phone', 'requirements',
        'notes', 'status', 'quoted_amount', 'admin_notes', 'customer_id',
    ];

    protected $casts = ['quoted_amount' => 'decimal:2'];

    public const STATUSES = ['new', 'contacted', 'quoted', 'accepted', 'rejected', 'completed'];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }
}
