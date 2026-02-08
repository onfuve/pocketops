<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskLog extends Model
{
    protected $fillable = [
        'task_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'message',
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return match ($this->action) {
            'created' => 'ایجاد شد',
            'status_changed' => 'تغییر وضعیت',
            'note_updated' => 'یادداشت به‌روز شد',
            'attachment_added' => 'پیوست اضافه شد',
            'user_assigned' => 'واگذار شد',
            'user_unassigned' => 'واگذاری لغو شد',
            default => $this->action,
        };
    }
}
