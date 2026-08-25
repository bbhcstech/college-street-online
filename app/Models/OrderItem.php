<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = ['order_id', 'book_id', 'quantity', 'unit_price', 'base_unit_price', 'fulfillment_status'];

    public function order() { return $this->belongsTo(Order::class); }
    public function book() { return $this->belongsTo(Book::class)->withTrashed(); }
    public function statusHistory() { return $this->hasMany(OrderItemStatusHistory::class)->latest('created_at'); }
}
