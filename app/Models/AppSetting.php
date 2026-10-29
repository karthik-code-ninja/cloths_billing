<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
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
        $setting = static::where('key', $key)->first();
        if (! $setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean', 'bool' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer', 'int' => (int) $setting->value,
            'decimal', 'float' => (float) $setting->value,
            'json', 'array' => json_decode($setting->value, true),
            default => is_bool($default) ? filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) : $setting->value,
        };
    }

    public static function set(string $key, mixed $value, ?string $type = null, string $group = 'general'): self
    {
        if ($type === null) {
            $type = match (true) {
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'decimal',
                is_array($value) => 'json',
                default => 'string',
            };
        }

        $val = match (true) {
            is_bool($value) => $value ? '1' : '0',
            is_array($value) => json_encode($value),
            default => (string) $value,
        };

        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $val, 'type' => $type, 'group' => $group]
        );
    }
}
