<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    /**
     * Serve attachment file (image or download).
     * Avoids 403 when direct /storage/ access is blocked.
     */
    public function show(Attachment $attachment): StreamedResponse
    {
        $user = request()->user();
        if ($attachment->attachable_type === \App\Models\Lead::class && $attachment->attachable_id) {
            $lead = \App\Models\Lead::find($attachment->attachable_id);
            if ($lead && !$lead->isVisibleTo($user)) {
                abort(403, 'شما به این فایل دسترسی ندارید.');
            }
        } elseif ($attachment->attachable_type === \App\Models\Invoice::class && $attachment->attachable_id) {
            $invoice = \App\Models\Invoice::find($attachment->attachable_id);
            if ($invoice && !$invoice->isVisibleTo($user)) {
                abort(403, 'شما به این فایل دسترسی ندارید.');
            }
        } elseif ($attachment->attachable_type === \App\Models\Task::class && $attachment->attachable_id) {
            $task = \App\Models\Task::visibleToUser($user)->find($attachment->attachable_id);
            if (!$task) {
                abort(403, 'شما به این فایل دسترسی ندارید.');
            }
        }

        $path = $attachment->path;
        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            abort(404);
        }

        $mime = $attachment->mime_type ?: 'application/octet-stream';
        $name = $attachment->original_name;

        return $disk->response($path, $name, [
            'Content-Type' => $mime,
            'Content-Disposition' => 'inline; filename="' . str_replace('"', '\\"', $name) . '"',
        ]);
    }
}
