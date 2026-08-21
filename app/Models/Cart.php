<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $fillable = ['customer_id', 'book_id', 'quantity'];

    public function book() { return $this->belongsTo(Book::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }

    public function lineTotal(): float { return (float) $this->quantity * (float) $this->book->price; }
}
