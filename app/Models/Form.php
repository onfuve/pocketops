<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Form extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'status',
        'edit_period_minutes',
        'submission_mode',
    ];

    protected $casts = [
        'edit_period_minutes' => 'integer',
    ];

    public const STATUS_DRAFT = 'draft';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_CLOSED = 'closed';

    public const MODE_SINGLE = 'single';
    public const MODE_MULTI = 'multi';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(FormModule::class)->orderBy('sort_order')->orderBy('id');
    }

    public function links(): HasMany
    {
        return $this->hasMany(FormLink::class);
    }

    public function submissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isAdmin()) {
            return $query;
        }
        return $query->where('user_id', $user->id);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isVisibleTo($user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        return $this->user_id === $user->id;
    }
}
