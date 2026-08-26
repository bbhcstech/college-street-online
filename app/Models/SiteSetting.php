<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'meta'];
    protected $casts = ['meta' => 'array'];

    public static function valueFor(string $key): ?string
    {
        $value = static::where('key', $key)->value('value');
        if (! $value || str_starts_with($value, 'http')) return $value;

        return Storage::disk('public')->url($value);
    }
}
