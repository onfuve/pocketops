<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Task extends Model
{
    protected $fillable = [
        'title',
        'notes',
        'status',
        'due_date',
        'due_time',
        'taskable_type',
        'taskable_id',
        'user_id',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];

    public const STATUS_TODO = 'todo';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_DONE = 'done';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statusLabels(): array
    {
        return [
            self::STATUS_TODO => 'برای انجام',
            self::STATUS_IN_PROGRESS => 'در حال انجام',
            self::STATUS_DONE => 'انجام شده',
            self::STATUS_CANCELLED => 'لغو شده',
        ];
    }

    public static function statusColors(): array
    {
        return [
            self::STATUS_TODO => '#0369a1',
            self::STATUS_IN_PROGRESS => '#b45309',
            self::STATUS_DONE => '#047857',
            self::STATUS_CANCELLED => '#78716c',
        ];
    }

    public function taskable()
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')->withTimestamps();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(TaskLog::class)->orderByDesc('created_at');
    }

    public function log(string $action, ?string $oldValue = null, ?string $newValue = null, ?string $message = null): TaskLog
    {
        return $this->logs()->create([
            'user_id' => auth()->id(),
            'action' => $action,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'message' => $message,
        ]);
    }

    public function taskableLink(): string
    {
        $t = $this->taskable;
        if (!$t) {
            return '#';
        }
        if ($t instanceof Lead) {
            return route('leads.show', $t);
        }
        if ($t instanceof Invoice) {
            return route('invoices.show', $t);
        }
        if ($t instanceof Contact) {
            return route('contacts.show', $t);
        }
        return '#';
    }

    public function taskableLabel(): string
    {
        $t = $this->taskable;
        if (!$t) {
            return '—';
        }
        if ($t instanceof Lead) {
            return 'سرنخ: ' . ($t->name ?? $t->id);
        }
        if ($t instanceof Invoice) {
            $label = $t->type === Invoice::TYPE_BUY ? 'رسید' : 'فاکتور';
            return $label . ': ' . ($t->invoice_number ?? $t->id);
        }
        if ($t instanceof Contact) {
            return 'مخاطب: ' . ($t->name ?? $t->id);
        }
        return '—';
    }

    /** Tasks visible to user: assigned to them, created by them, or admin sees all. */
    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isAdmin()) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhereHas('assignedUsers', fn ($uq) => $uq->where('users.id', $user->id));
        });
    }
}
