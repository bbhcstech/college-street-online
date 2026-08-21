<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = ['book_id', 'quantity', 'low_stock_threshold'];

    public function book() { return $this->belongsTo(Book::class); }

    public function isLowStock(): bool { return $this->quantity <= $this->low_stock_threshold; }
}
