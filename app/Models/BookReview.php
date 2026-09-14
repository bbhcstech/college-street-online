<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookReview extends Model
{
    protected $fillable = ['book_id', 'customer_id', 'order_id', 'rating', 'review', 'images', 'admin_response', 'admin_responded_at'];

    protected $casts = ['images' => 'array', 'admin_responded_at' => 'datetime'];

    public function book() { return $this->belongsTo(Book::class); }
    public function customer() { return $this->belongsTo(User::class, 'customer_id'); }
    public function order() { return $this->belongsTo(Order::class); }
    public function reports() { return $this->hasMany(ReviewReport::class); }
}
