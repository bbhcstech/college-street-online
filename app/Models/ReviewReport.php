<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReviewReport extends Model
{
    protected $fillable = ['book_review_id', 'reporter_id', 'reason'];

    public function review() { return $this->belongsTo(BookReview::class, 'book_review_id'); }
    public function reporter() { return $this->belongsTo(User::class, 'reporter_id'); }
}
