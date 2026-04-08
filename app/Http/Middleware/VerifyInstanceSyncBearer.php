<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyInstanceSyncBearer
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedHash = Setting::get('instance_sync.incoming_token_hash');
        if (! is_string($expectedHash) || $expectedHash === '') {
            abort(403, 'همگام‌سازی روی این نصب فعال نشده است.');
        }

        $token = $request->bearerToken();
        if (! is_string($token) || $token === '') {
            abort(401, 'توکن همگام‌سازی ارسال نشده است.');
        }

        $hash = hash('sha256', $token);
        if (! hash_equals($expectedHash, $hash)) {
            abort(401, 'توکن همگام‌سازی نامعتبر است.');
        }

        return $next($request);
    }
}
