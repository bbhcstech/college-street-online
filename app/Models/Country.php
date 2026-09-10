<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Country extends Model
{
    protected $fillable = [
        'code',
        'name',
        'currency_code',
        'markup_percentage',
        'base_shipping_fee',
        'per_kg_shipping_fee',
        'per_item_shipping_fee',
        'free_shipping_threshold',
        'is_active',
    ];

    protected $casts = [
        'markup_percentage' => 'float',
        'base_shipping_fee' => 'float',
        'per_kg_shipping_fee' => 'float',
        'free_shipping_threshold' => 'float',
        'is_active' => 'boolean',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    public function getSymbolAttribute()
    {
        $currency = Currency::where('code', $this->currency_code)->first();
        return $currency?->symbol ?? ($this->attributes['symbol'] ?? '₹');
    }

    public function getPerItemShippingFeeAttribute()
    {
        return (float) ($this->attributes['per_item_shipping_fee'] ?? $this->attributes['per_kg_shipping_fee'] ?? 0.00);
    }

    public function setPerItemShippingFeeAttribute($value)
    {
        $this->attributes['per_kg_shipping_fee'] = $value;
    }

    public static function getActiveCountries()
    {
        return static::where('is_active', true)->orderBy('name')->get();
    }
}
