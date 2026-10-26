<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value', 'type'];

    protected static function booted(): void
    {
        static::saved(function (Setting $setting) {
            Cache::forget("setting_{$setting->key}");
        });

        static::deleted(function (Setting $setting) {
            Cache::forget("setting_{$setting->key}");
        });
    }

    public static function get($key, $default = null)
    {
        return Cache::remember("setting_{$key}", 86400, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            switch ($setting->type) {
                case 'boolean':
                    return filter_var($setting->value, FILTER_VALIDATE_BOOLEAN);
                case 'integer':
                    return (int) $setting->value;
                case 'json':
                    return json_decode($setting->value, true);
                default:
                    return $setting->value;
            }
        });
    }

    public static function set($key, $value, $type = 'string')
    {
        Cache::forget("setting_{$key}");

        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : $value,
                'type' => $type
            ]
        );
    }

    public static function forget($key): void
    {
        Cache::forget("setting_{$key}");
    }
}
