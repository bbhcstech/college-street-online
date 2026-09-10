<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCountryPrice extends Model
{
    protected $fillable = [
        'book_id',
        'country_code',
        'price',
        'mrp',
    ];

    protected $casts = [
        'price' => 'float',
        'mrp' => 'float',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}

