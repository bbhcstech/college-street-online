<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryTransaction extends Model
{
    const UPDATED_AT = null;
    protected $fillable = ['book_id', 'transaction_type', 'change_qty', 'order_id'];

    public function book() { return $this->belongsTo(Book::class); }
    public function order() { return $this->belongsTo(Order::class); }
}
