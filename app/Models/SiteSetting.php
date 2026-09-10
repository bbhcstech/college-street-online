<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class SiteSetting extends Model
{
    protected $fillable = ['key', 'value', 'meta'];
    protected $casts = ['meta' => 'array'];

    public static function valueFor(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        if (! $setting || $setting->value === null) {
            return $default;
        }

        $value = $setting->value;

        // Only convert to storage URL if meta explicitly specifies disk or key ends with _qr / image
        if (is_array($setting->meta) && isset($setting->meta['disk'])) {
            if (str_starts_with($value, 'http')) {
                return $value;
            }
            return Storage::disk($setting->meta['disk'])->url($value);
        }

        return $value;
    }
}
