<?php

namespace App\Support;

class MoneyMath
{
    /**
     * Presisi desimal standar mata uang (2 digit sen).
     */
    public const SCALE = 2;

    /**
     * Penjumlahan nominal uang: $a + $b
     */
    public static function add(mixed $a, mixed $b, int $scale = self::SCALE): string
    {
        return bcadd(self::toString($a), self::toString($b), $scale);
    }

    /**
     * Pengurangan nominal uang: $a - $b
     */
    public static function sub(mixed $a, mixed $b, int $scale = self::SCALE): string
    {
        return bcsub(self::toString($a), self::toString($b), $scale);
    }

    /**
     * Perkalian nominal (mis. berat timbangan x harga satuan): $a * $b
     */
    public static function mul(mixed $a, mixed $b, int $scale = self::SCALE): string
    {
        return bcmul(self::toString($a), self::toString($b), $scale);
    }

    /**
     * Pembagian nominal: $a / $b
     */
    public static function div(mixed $a, mixed $b, int $scale = self::SCALE): string
    {
        $bStr = self::toString($b);
        if (bccomp($bStr, '0', $scale) === 0) {
            return '0.00';
        }
        return bcdiv(self::toString($a), $bStr, $scale);
    }

    /**
     * Komparasi dua nilai uang ($a <=> $b):
     * Return 0 jika sama, 1 jika $a > $b, -1 jika $a < $b.
     */
    public static function comp(mixed $a, mixed $b, int $scale = self::SCALE): int
    {
        return bccomp(self::toString($a), self::toString($b), $scale);
    }

    /**
     * Mengecek apakah $a lebih besar atau sama dengan $b ($a >= $b).
     */
    public static function gte(mixed $a, mixed $b, int $scale = self::SCALE): bool
    {
        return self::comp($a, $b, $scale) >= 0;
    }

    /**
     * Mengecek apakah $a lebih kecil dari $b ($a < $b).
     */
    public static function lt(mixed $a, mixed $b, int $scale = self::SCALE): bool
    {
        return self::comp($a, $b, $scale) < 0;
    }

    /**
     * Konversi nilai apa pun menjadi string angka bersih untuk BCMath.
     */
    public static function toString(mixed $val): string
    {
        if (is_null($val)) {
            return '0';
        }
        if (is_string($val)) {
            $val = trim($val);
            return is_numeric($val) ? $val : '0';
        }
        if (is_int($val) || is_float($val)) {
            return number_format($val, 4, '.', '');
        }
        return '0';
    }

    /**
     * Format nilai nominal menjadi float 2 desimal standar jika diperlukan oleh model/blade.
     */
    public static function toFloat(mixed $val, int $scale = self::SCALE): float
    {
        return (float) number_format((float) self::toString($val), $scale, '.', '');
    }
}
