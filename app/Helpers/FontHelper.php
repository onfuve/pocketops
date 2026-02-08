<?php

namespace App\Helpers;

class FontHelper
{
    public const FONTS = [
        'vazirmatn' => ['label' => 'وزیرمتن', 'css' => "'Vazirmatn', sans-serif"],
        'tahoma' => ['label' => 'تاهوما', 'css' => "'Tahoma', 'Arial', sans-serif"],
        'inherit' => ['label' => 'پیش‌فرض سیستم', 'css' => "var(--ds-font)"],
    ];

    public static function cssFor(?string $font): string
    {
        if (!$font || !isset(self::FONTS[$font])) {
            return self::FONTS['vazirmatn']['css'];
        }
        return self::FONTS[$font]['css'];
    }
}
