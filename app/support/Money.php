<?php

namespace App\Support;

use InvalidArgumentException;

class Money
{
    public static function toCents(string $amount): int
    {
        if (! preg_match('/^\d+(?:\.\d{1,2})?$/D', $amount)) {
            throw new InvalidArgumentException('Invalid money amount.');
        }

        $parts = explode('.', $amount, 2);

        $whole = (int) $parts[0];
        $fraction = str_pad($parts[1] ?? '', 2, '0');

        return ($whole * 100) + (int) $fraction;
    }

    public static function fromCents(int $cents): string
    {
        if ($cents < 0) {
            throw new InvalidArgumentException('Amount cannot be negative.');
        }

        return intdiv($cents, 100) . '.' . str_pad(
            (string) ($cents % 100),
            2,
            '0',
            STR_PAD_LEFT
        );
    }
}