<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class RestaurantSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("restaurant_setting_{$key}", function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            if (!$setting) {
                return $default;
            }

            return match ($setting->type) {
                'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
                'integer' => (int) $setting->value,
                'float' => (float) $setting->value,
                'json' => json_decode($setting->value, true) ?? $default,
                default => $setting->value,
            };
        });
    }

    public static function set(string $key, mixed $value, string $group = 'general', ?string $type = null): self
    {
        if ($type === null) {
            $type = is_bool($value) ? 'boolean' : (is_numeric($value) ? (is_int($value) ? 'integer' : 'float') : (is_array($value) ? 'json' : 'string'));
        }

        $storedValue = is_array($value) ? json_encode($value) : (is_bool($value) ? ($value ? '1' : '0') : (string) $value);

        $setting = static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $storedValue,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("restaurant_setting_{$key}");
        Cache::forget('restaurant_all_settings');

        return $setting;
    }

    public static function getAll(): array
    {
        return Cache::rememberForever('restaurant_all_settings', function () {
            $records = static::all();
            $result = [];
            foreach ($records as $r) {
                $result[$r->key] = match ($r->type) {
                    'boolean' => filter_var($r->value, FILTER_VALIDATE_BOOLEAN),
                    'integer' => (int) $r->value,
                    'float' => (float) $r->value,
                    'json' => json_decode($r->value, true),
                    default => $r->value,
                };
            }
            return $result;
        });
    }
}
