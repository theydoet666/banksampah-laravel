<?php

namespace App\Support;

class RandomPassword
{
    /**
     * Menghasilkan password acak yang kuat tanpa karakter ambigu (seperti 0, O, 1, l, I).
     *
     * @param int $length Panjang password (default: 10 karakter)
     * @return string
     */
    public static function generate(int $length = 10): string
    {
        // Karakter alfabet & angka tanpa karakter ambigu
        $chars = '23456789abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
        $maxIndex = strlen($chars) - 1;
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }

        return $password;
    }
}
