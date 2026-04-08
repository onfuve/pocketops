<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class InstanceSyncService
{
    /**
     * @return array<string, mixed>
     */
    public function fetchRemotePayload(string $baseUrl, string $bearerToken, int $timeoutSeconds = 600): array
    {
        $baseUrl = rtrim($baseUrl, '/');
        $url = $baseUrl.'/instance-sync/export';

        $response = Http::withToken($bearerToken)
            ->timeout($timeoutSeconds)
            ->acceptJson()
            ->get($url);

        if (! $response->successful()) {
            throw new InvalidArgumentException(
                'دریافت از سرور مقصد ناموفق بود (کد '.$response->status().'). آدرس پایه، کلید سمت دیگر و '
                .'مسیر /instance-sync/export را بررسی کنید.'
            );
        }

        $data = $response->json();
        if (! is_array($data)) {
            throw new InvalidArgumentException('پاسخ سرور مقصد ساختار JSON جدول‌دار نیست.');
        }

        return $data;
    }
}
