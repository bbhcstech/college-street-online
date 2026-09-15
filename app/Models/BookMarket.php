<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookMarket extends Model
{
    protected $fillable = [
        'book_id',
        'country_code',
        'is_available',
        'price',
        'mrp',
        'stock',
        'max_order_qty',
        'dispatch_days',
    ];

    protected $casts = [
        'is_available' => 'boolean',
        'price' => 'float',
        'mrp' => 'float',
        'stock' => 'integer',
        'max_order_qty' => 'integer',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}

