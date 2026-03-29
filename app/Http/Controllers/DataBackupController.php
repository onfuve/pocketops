<?php

namespace App\Http\Controllers;

use App\Services\DataBackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class DataBackupController extends Controller
{
    private const JSON_MAX_DEPTH = 4096;

    public function index()
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر به پشتیبان داده دسترسی دارد.');

        $service = app(DataBackupService::class);
        $summary = $service->summarizeForDisplay();
        $driverLabel = $this->databaseDriverLabel($summary['driver']);

        return view('settings.data-backup', compact('summary', 'driverLabel'));
    }

    public function export(): StreamedResponse
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند از داده پشتیبان بگیرد.');

        $service = app(DataBackupService::class);
        $payload = $service->exportPayload();
        $filename = 'pocket-business-backup-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(function () use ($payload) {
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            if ($json === false) {
                throw new \RuntimeException('ساختن فایل JSON ممکن نیست (دادهٔ غیرقابل‌کدگذاری).');
            }
            echo $json;
        }, $filename, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }

    public function import(Request $request)
    {
        abort_unless($request->user()?->isAdmin(), 403, 'فقط مدیر می‌تواند داده را بازیابی کند.');

        $request->validate([
            'backup' => ['required', 'file', 'max:256000', 'mimes:json,txt'],
            'confirm_restore' => ['accepted'],
        ], [
            'backup.required' => 'فایل پشتیبان را انتخاب کنید.',
            'backup.max' => 'حجم فایل نباید از ۲۵۰ مگابایت بیشتر باشد.',
            'backup.mimes' => 'فقط فایل ‎.json‎ یا ‎.txt‎ (json) قابل قبول است.',
            'confirm_restore.accepted' => 'برای ادامه باید تأیید کنید که از جایگزینی داده‌ها آگاهید.',
        ]);

        $path = $request->file('backup')->getRealPath();
        if ($path === false) {
            return back()->with('error', 'خواندن فایل آپلودشده ممکن نیست.');
        }

        try {
            $contents = file_get_contents($path);
            if ($contents === false) {
                return back()->with('error', 'خواندن فایل پشتیبان ناموفق بود.');
            }
            $payload = json_decode($contents, true, self::JSON_MAX_DEPTH, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return back()->with('error', 'فایل انتخاب‌شده JSON معتبر نیست یا عمق تو در تویش بیش از حد مجاز است.');
        }

        if (! is_array($payload)) {
            return back()->with('error', 'ساختار فایل پشتیبان نادرست است.');
        }

        try {
            app(DataBackupService::class)->importFromDecodedArray($payload);
        } catch (InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (Throwable $e) {
            Log::error($e->getMessage(), ['exception' => $e]);

            return back()->with('error', 'بازیابی با خطای غیرمنتظره متوقف شد. جزئیات در لاگ سرور ثبت شده است.');
        }

        return redirect()->route('settings.data-backup')->with('success', 'داده‌ها از پشتیبان بازیابی شد. در صورت استفاده از فایل‌های آپلودشده، پوشهٔ storage را هم از قبل کپی کرده باشید.');
    }

    private function databaseDriverLabel(string $driver): string
    {
        return match ($driver) {
            'mysql' => 'MySQL / MariaDB',
            'sqlite' => 'SQLite',
            'pgsql' => 'PostgreSQL',
            default => $driver,
        };
    }
}
