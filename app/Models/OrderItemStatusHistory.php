<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItemStatusHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['order_item_id', 'from_status', 'to_status', 'changed_by'];

    public function orderItem() { return $this->belongsTo(OrderItem::class); }
    public function actor() { return $this->belongsTo(User::class, 'changed_by'); }
}
