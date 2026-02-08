<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = [
        'attachable_type',
        'attachable_id',
        'path',
        'original_name',
        'mime_type',
        'size',
    ];

    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type ?? '', 'image/');
    }

    /** URL to view attachment (served via Laravel route to avoid 403 on direct /storage/). */
    public function url(): string
    {
        return route('attachments.show', $this);
    }

    public function fullPath(): string
    {
        return Storage::disk('public')->path($this->path);
    }
}
