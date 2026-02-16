<?php

namespace App\Helpers;

use Hekmatinasser\Verta\Verta;

class FormatHelper
{
    private const TEHRAN = 'Asia/Tehran';

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

    /** Format number with comma thousands separator, no decimals (###,###,###) */
    public static function numberFormat(int|float $num, bool $persianDigits = true): string
    {
        $n = (int) round((float) $num);
        $formatted = number_format($n, 0, '', ',');
        return $persianDigits ? self::englishToPersian($formatted) : $formatted;
    }

    /** Format amount in Rial with comma and optional Persian digits */
    public static function rial(int|float $amount, bool $persianDigits = true): string
    {
        return self::numberFormat($amount, $persianDigits) . ' ریال';
    }

    /** Format amount in Toman (amount/10) with optional Persian digits */
    public static function toman(int|float $amount, bool $persianDigits = true): string
    {
        return self::numberFormat(round($amount / 10), $persianDigits) . ' تومان';
    }

    /** Format price for price list: rial, toman, or none (number only) */
    public static function priceForList(int|float $amount, string $format = 'rial', bool $persianDigits = true): string
    {
        $num = match ($format) {
            'toman' => self::numberFormat(round($amount / 10), $persianDigits),
            'rial' => self::numberFormat((int) round($amount), $persianDigits),
            'none' => self::numberFormat((int) round($amount), $persianDigits),
            default => self::numberFormat((int) round($amount), $persianDigits),
        };
        return match ($format) {
            'toman' => $num . ' تومان',
            'rial' => $num . ' ریال',
            'none' => $num,
            default => $num . ' ریال',
        };
    }

    /** Format Carbon/date to Shamsi string (e.g. ۱۴۰۳/۱۱/۱۳). Uses Asia/Tehran for date-only to avoid day shift. */
    public static function shamsi($date, string $format = 'Y/m/d'): string
    {
        if ($date === null) {
            return '';
        }
        try {
            if ($date instanceof \DateTimeInterface) {
                $dateStr = $date->format('Y-m-d H:i:s');
                $tz = $date->getTimezone()->getName();
            } else {
                $str = trim((string) $date);
                if (preg_match('/^\d{4}-\d{2}-\d{2}( \d|$)/', $str)) {
                    $dt = new \DateTimeImmutable($str . (str_contains($str, ' ') ? '' : ' 12:00:00'), new \DateTimeZone(self::TEHRAN));
                    $dateStr = $dt->format('Y-m-d H:i:s');
                    $tz = self::TEHRAN;
                } else {
                    $dateStr = $str;
                    $tz = self::TEHRAN;
                }
            }
            $v = Verta::instance($dateStr, $tz);
            $formatted = $v->format($format);
            return self::englishToPersian($formatted);
        } catch (\Throwable $e) {
            return '';
        }
    }

    /** Shamsi date-time for invoice number (e.g. 140311131205) */
    public static function shamsiNumber(): string
    {
        return Verta::now()->format('YmdHis');
    }

    /** Parse Shamsi date string (Y/m/d or Y-m-d, Persian or English digits) to Gregorian Y-m-d. Uses noon Tehran to avoid day boundary shift. */
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
            $v = Verta::parse($normalized . ' 12:00:00', self::TEHRAN);
            return $v->datetime()->setTimezone(new \DateTimeZone(self::TEHRAN))->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    /** Gregorian Y-m-d to Shamsi Y-m-d for consistent sorting (e.g. "1403/11/15"). */
    public static function gregorianToShamsiSortKey(string $gregorianYmd): string
    {
        $dateStr = $gregorianYmd . ' 12:00:00';
        $v = Verta::instance($dateStr, self::TEHRAN);
        return sprintf('%04d/%02d/%02d', $v->year, $v->month, $v->day);
    }
}
