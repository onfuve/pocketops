<?php

namespace App\Helpers;

use Hekmatinasser\Verta\Verta;

class FormatHelper
{
    /** Persian (۰-۹) to English (0-9) for storage/calculation */
    public static function persianToEnglish(string $value): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $english, $value);
    }

    /** English (0-9) to Persian (۰-۹) for display */
    public static function englishToPersian(string $value): string
    {
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        return str_replace($english, $persian, $value);
    }

    /** Format number with comma every 3 digits, optional Persian digits */
    public static function numberFormat(int|float $num, bool $persianDigits = true): string
    {
        $formatted = number_format((float) $num, 0, '', ',');
        return $persianDigits ? self::englishToPersian($formatted) : $formatted;
    }

    /** Format amount in Rial with comma and optional Persian digits */
    public static function rial(int|float $amount, bool $persianDigits = true): string
    {
        return self::numberFormat($amount, $persianDigits) . ' ریال';
    }

    /** Format Carbon/date to Shamsi string (e.g. ۱۴۰۳/۱۱/۱۳) */
    public static function shamsi($date, string $format = 'Y/m/d'): string
    {
        if ($date === null) {
            return '';
        }
        try {
            $datetime = $date instanceof \DateTimeInterface ? $date : new \DateTimeImmutable($date);
            $v = Verta::instance($datetime);
            $formatted = $v->format($format);
            return self::englishToPersian($formatted);
        } catch (\Throwable $e) {
            $d = $date instanceof \DateTimeInterface ? $date->format('Y/m/d') : (string) $date;
            return self::englishToPersian($d);
        }
    }

    /** Shamsi date-time for invoice number (e.g. 140311131205) */
    public static function shamsiNumber(): string
    {
        return Verta::now()->format('YmdHis');
    }

    /** Parse Shamsi date string (Y/m/d or Y-m-d, Persian or English digits) to Gregorian Y-m-d */
    public static function shamsiToGregorian(?string $shamsi): ?string
    {
        if ($shamsi === null || trim($shamsi) === '') {
            return null;
        }
        try {
            $normalized = self::persianToEnglish(trim(str_replace(['-', ' '], ['/', '/'], $shamsi)));
            if (!preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $normalized)) {
                return null;
            }
            $v = Verta::parse($normalized);
            return $v->datetime()->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
