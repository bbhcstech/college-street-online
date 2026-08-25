<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'meta'];
    protected $casts = ['meta' => 'array'];

    public static function valueFor(string $key): ?string
    {
        return static::where('key', $key)->value('value');
    }
}
