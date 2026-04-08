<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use App\Services\DataBackupService;
use App\Services\InstanceSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class InstanceSyncController extends Controller
{
    public const SETTING_INCOMING_HASH = 'instance_sync.incoming_token_hash';

    public const SETTING_REMOTE_URL = 'instance_sync.remote_url';

    public const SETTING_REMOTE_TOKEN = 'instance_sync.remote_token';

    public function index(): View
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر به همگام‌سازی نصب‌ها دسترسی دارد.');

        $hasIncomingKey = is_string(Setting::get(self::SETTING_INCOMING_HASH)) && Setting::get(self::SETTING_INCOMING_HASH) !== '';
        $remoteUrl = Setting::get(self::SETTING_REMOTE_URL, '');
        $hasRemote = is_string($remoteUrl) && $remoteUrl !== '';

        $newToken = session('instance_sync_new_token');

        return view('settings.instance-sync', [
            'hasIncomingKey' => $hasIncomingKey,
            'remoteUrl' => is_string($remoteUrl) ? $remoteUrl : '',
            'hasRemote' => $hasRemote,
            'newToken' => is_string($newToken) ? $newToken : null,
        ]);
    }

    public function generateIncomingToken(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $plain = Str::random(64);
        Setting::set(self::SETTING_INCOMING_HASH, hash('sha256', $plain));
        $request->session()->flash('instance_sync_new_token', $plain);

        return redirect()
            ->route('settings.instance-sync')
            ->with('success', 'کلید جدید ساخته شد. آن را فقط یک‌بار در نصب مقابل ذخیره کنید.');
    }

    public function revokeIncomingToken(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        Setting::set(self::SETTING_INCOMING_HASH, '');

        return redirect()
            ->route('settings.instance-sync')
            ->with('success', 'کلید ورودی همگام‌سازی غیرفعال شد. دریافت از این نصب دیگر با توکن قبلی ممکن نیست.');
    }

    public function saveRemote(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $validated = $request->validate([
            'remote_url' => ['nullable', 'string', 'max:2048'],
            'remote_token' => ['nullable', 'string', 'max:2048'],
        ], [
            'remote_url.max' => 'آدرس خیلی طولانی است.',
        ]);

        $url = isset($validated['remote_url']) ? trim((string) $validated['remote_url']) : '';
        $token = isset($validated['remote_token']) ? trim((string) $validated['remote_token']) : '';

        if ($url !== '' && ! filter_var($url, FILTER_VALIDATE_URL)) {
            return back()->with('error', 'آدرس پایهٔ سرور مقصد معتبر نیست (با https:// یا http:// شروع شود).');
        }

        $existingCipher = Setting::get(self::SETTING_REMOTE_TOKEN, '');
        if ($url !== '' && (! is_string($existingCipher) || $existingCipher === '') && $token === '') {
            return back()->with('error', 'برای ذخیرهٔ آدرس مقصد، کلید Bearer را هم وارد کنید.');
        }

        Setting::set(self::SETTING_REMOTE_URL, $url);

        if ($token !== '') {
            Setting::set(self::SETTING_REMOTE_TOKEN, Crypt::encryptString($token));
        }

        return redirect()->route('settings.instance-sync')->with('success', 'تنظیمات سرور مقصد ذخیره شد.');
    }

    public function clearRemote(Request $request): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        Setting::set(self::SETTING_REMOTE_URL, '');
        Setting::set(self::SETTING_REMOTE_TOKEN, '');

        return redirect()->route('settings.instance-sync')->with('success', 'آدرس و کلید سرور مقصد پاک شد.');
    }

    public function runSync(Request $request, InstanceSyncService $syncService, DataBackupService $backupService): RedirectResponse
    {
        abort_unless($request->user()?->isAdmin(), 403);

        $request->validate([
            'add_missing' => ['sometimes', 'boolean'],
            'update_existing' => ['sometimes', 'boolean'],
            'delete_orphans' => ['sometimes', 'boolean'],
            'include_users' => ['sometimes', 'boolean'],
            'confirm_sync' => ['accepted'],
        ], [
            'confirm_sync.accepted' => 'برای همگام‌سازی باید پیام هشدار را تأیید کنید.',
        ]);

        $remoteUrl = Setting::get(self::SETTING_REMOTE_URL, '');
        $cipher = Setting::get(self::SETTING_REMOTE_TOKEN, '');
        if (! is_string($remoteUrl) || $remoteUrl === '' || ! is_string($cipher) || $cipher === '') {
            return back()->with('error', 'ابتدا آدرس پایه و کلید Bearer سرور مقصد را ذخیره کنید.');
        }

        try {
            $token = Crypt::decryptString($cipher);
        } catch (Throwable) {
            return back()->with('error', 'ذخیرهٔ کلید سرور مقصد خراب است. دوباره کلید را وارد و ذخیره کنید.');
        }

        try {
            $payload = $syncService->fetchRemotePayload($remoteUrl, $token);
            $stats = $backupService->mergeFromRemotePayload($payload, [
                'add_missing' => $request->boolean('add_missing', true),
                'update_existing' => $request->boolean('update_existing', true),
                'delete_orphans' => $request->boolean('delete_orphans', false),
                'include_users' => $request->boolean('include_users', false),
            ]);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error('Instance sync failed', ['exception' => $e]);

            return back()->with('error', 'همگام‌سازی با خطای غیرمنتظره متوقف شد. لاگ سرور را ببینید.');
        }

        $msg = sprintf(
            'همگام‌سازی انجام شد: %s درج، %s به‌روزرسانی، %s حذف (طبق گزینه‌ها).',
            number_format($stats['inserted']),
            number_format($stats['updated']),
            number_format($stats['deleted'])
        );

        return redirect()->route('settings.instance-sync')->with('success', $msg);
    }
}
