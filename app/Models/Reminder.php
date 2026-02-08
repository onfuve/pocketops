<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Reminder extends Model
{
    protected $fillable = [
        'title',
        'body',
        'due_date',
        'due_time',
        'type',
        'remindable_type',
        'remindable_id',
        'user_id',
        'done_at',
    ];

    protected $casts = [
        'due_date' => 'date',
        'done_at' => 'datetime',
    ];

    public const TYPE_REMINDER = 'reminder';
    public const TYPE_LEAD_TASK = 'lead_task';
    public const TYPE_INVOICE_DUE = 'invoice_due';

    public function remindable(): MorphTo
    {
        return $this->morphTo();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isDone(): bool
    {
        return $this->done_at !== null;
    }

    public function markDone(): void
    {
        $this->update(['done_at' => now()]);
    }

    public function markUndone(): void
    {
        $this->update(['done_at' => null]);
    }
}
