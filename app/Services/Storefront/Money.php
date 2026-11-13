<?php

namespace App\Services\Storefront;

use Illuminate\Validation\ValidationException;

final class Money
{
    public static function minor(mixed $value): int
    {
        if (! is_numeric($value) || ! preg_match('/^\d+(?:\.\d{1,2})?$/D', (string) $value)) {
            throw ValidationException::withMessages(['price' => 'A valid catalog price is unavailable.']);
        }
        [$whole, $fraction] = array_pad(explode('.', (string) $value), 2, '');
        if (strlen($whole) > 8) {
            throw ValidationException::withMessages(['price' => 'Catalog amount exceeds supported limits.']);
        }

        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    public static function decimal(int $minor): string
    {
        return intdiv($minor, 100).'.'.str_pad((string) ($minor % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function ratio(int $amount, int $numerator, int $denominator): int
    {
        // Laravel already installs Brick Math. Avoid float conversion and integer overflow in proportional allocations.
        return \Brick\Math\BigInteger::of($amount)->multipliedBy($numerator)->plus(intdiv($denominator, 2))->quotient($denominator)->toInt();
    }
}
