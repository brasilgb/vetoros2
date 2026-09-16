<?php

namespace App\Services;

use InvalidArgumentException;

final class BudgetCalculator
{
    /** @return array{quantity: string, unit_price: string, discount_amount: string, total: string} */
    public static function item(string|int $quantity, string|int $unitPrice, string|int $discount = '0.00'): array
    {
        $quantityMilli = self::scaledInteger($quantity, 3);
        $unitCents = self::scaledInteger($unitPrice, 2);
        $discountCents = self::scaledInteger($discount, 2);
        if ($quantityMilli < 0 || $unitCents < 0 || $discountCents < 0) {
            throw new InvalidArgumentException('Budget monetary values and quantity cannot be negative.');
        }

        $grossCents = intdiv($quantityMilli * $unitCents + 500, 1000);
        if ($discountCents > $grossCents) {
            throw new InvalidArgumentException('Item discount cannot exceed the item gross amount.');
        }

        return [
            'quantity' => self::formatScaled($quantityMilli, 3),
            'unit_price' => self::formatScaled($unitCents, 2),
            'discount_amount' => self::formatScaled($discountCents, 2),
            'total' => self::formatScaled($grossCents - $discountCents, 2),
        ];
    }

    /** @param array<int, array{total: string|int}> $items
     * @return array{subtotal: string, discount_amount: string, total: string}
     */
    public static function budget(array $items, string|int $discount = '0.00'): array
    {
        $subtotal = array_sum(array_map(fn (array $item): int => self::scaledInteger($item['total'], 2), $items));
        $discountCents = self::scaledInteger($discount, 2);
        if ($discountCents < 0 || $discountCents > $subtotal) {
            throw new InvalidArgumentException('Budget discount cannot exceed the subtotal.');
        }

        return ['subtotal' => self::formatScaled($subtotal, 2), 'discount_amount' => self::formatScaled($discountCents, 2), 'total' => self::formatScaled($subtotal - $discountCents, 2)];
    }

    private static function scaledInteger(string|int $value, int $scale): int
    {
        $value = trim((string) $value);
        if (! preg_match('/^\d+(?:\.\d{1,'.$scale.'})?$/', $value)) {
            throw new InvalidArgumentException('Budget values must be non-negative decimal numbers.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');

        return ((int) $whole * (10 ** $scale)) + ((int) str_pad($fraction, $scale, '0'));
    }

    private static function formatScaled(int $value, int $scale): string
    {
        $base = 10 ** $scale;

        return intdiv($value, $base).'.'.str_pad((string) ($value % $base), $scale, '0', STR_PAD_LEFT);
    }
}
