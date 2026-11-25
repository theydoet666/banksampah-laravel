<?php

namespace App\Support;

class SpreadsheetSanitizer
{
    /**
     * Mencegah CSV / Spreadsheet Formula Injection (CWE-1236).
     * Jika sebuah nilai teks diawali dengan karakter formula sensitif (=, +, -, @, tab, newline),
     * awali dengan tanda petik tunggal (') agar dievaluasi sebagai teks murni di Excel/Calc.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function sanitize(mixed $value): mixed
    {
        if (! is_string($value)) {
            return $value;
        }

        $trimmed = ltrim($value);
        if ($trimmed === '') {
            return $value;
        }

        $dangerousPrefixes = ['=', '+', '-', '@', "\t", "\r", "\n"];
        $firstChar = $trimmed[0];

        if (in_array($firstChar, $dangerousPrefixes, true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitasi seluruh array baris spreadsheet
     *
     * @param array $row
     * @return array
     */
    public static function sanitizeRow(array $row): array
    {
        return array_map([self::class, 'sanitize'], $row);
    }
}
