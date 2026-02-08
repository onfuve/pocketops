<?php

namespace App\Helpers;

/**
 * Local-first assets for offline / blackout support.
 * Prefer local files; optional CDN fallback when local file missing.
 */
class AssetHelper
{
    public static function asset(string $localPath, string $cdnUrl = ''): string
    {
        $useLocal = env('USE_LOCAL_ASSETS', true);
        if ($useLocal && file_exists(public_path($localPath))) {
            return asset($localPath);
        }
        return $cdnUrl ?: asset($localPath);
    }
}
