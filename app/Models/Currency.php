<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate_to_inr',
        'decimal_places',
        'is_active',
    ];

    protected $casts = [
        'exchange_rate_to_inr' => 'float',
        'is_active' => 'boolean',
    ];

    public function getExchangeRateAttribute()
    {
        return (float) ($this->attributes['exchange_rate_to_inr'] ?? 1.0);
    }

    public function setExchangeRateAttribute($value)
    {
        $this->attributes['exchange_rate_to_inr'] = $value;
    }

    public static function getActiveCurrencies()
    {
        return static::where('is_active', true)->orderBy('code')->get();
    }
}
