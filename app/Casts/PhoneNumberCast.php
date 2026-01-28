<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class PhoneNumberCast implements CastsAttributes
{
    public function get(Model $model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }
        return match (strlen($value)) {
            7 => substr($value, 0, 3).'-'.substr($value, 3),
            10 => '('.substr($value, 0, 3).') '.substr($value, 3, 3).'-'.substr($value, 6),
            default => strlen($value) > 10
                ? '+'.substr($value, 0, -10).' ('.substr($value, -10, 3).') '.substr($value, -7, 3).'-'.substr($value,
                    -4)
                : $value,
        };
    }

    public function set(Model $model, string $key, $value, array $attributes)
    {
        if ($value === null) {
            return null;
        }

        return preg_replace('/\D/', '', $value);
    }
}
