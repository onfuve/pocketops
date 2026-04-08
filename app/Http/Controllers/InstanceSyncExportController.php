<?php

namespace App\Http\Controllers;

use App\Services\DataBackupService;
use Symfony\Component\HttpFoundation\Response;

class InstanceSyncExportController extends Controller
{
    public function export(DataBackupService $backupService): Response
    {
        $payload = $backupService->exportPayload();
        $json = json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            abort(500, 'ساختن پاسخ JSON ممکن نیست.');
        }

        return response($json, 200, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }
}
