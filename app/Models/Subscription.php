<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Subscription extends Model
{
    protected $fillable = [
        'contact_id',
        'service_name',
        'category',
        'description',
        'start_date',
        'expiry_date',
        'billing_cycle',
        'price',
        'cost',
        'payment_status',
        'auto_renewal',
        'supplier',
        'account_credentials',
        'notes',
        'reminder_days_before',
        'assigned_to_id',
        'user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'expiry_date' => 'date',
        'price' => 'decimal:0',
        'cost' => 'decimal:0',
        'auto_renewal' => 'boolean',
        'account_credentials' => 'encrypted',
    ];

    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_OVERDUE = 'overdue';

    public const CATEGORY_CLOUD = 'cloud';
    public const CATEGORY_VPN = 'vpn';
    public const CATEGORY_LICENSE = 'license';
    public const CATEGORY_DOMAIN = 'domain';
    public const CATEGORY_OTHER = 'other';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable');
    }

    /** Profit = price - cost (optional). */
    public function getProfitAttribute(): ?float
    {
        if ($this->cost === null) {
            return null;
        }
        return (float) $this->price - (float) $this->cost;
    }

    public function isOverdue(): bool
    {
        return $this->payment_status === self::PAYMENT_OVERDUE
            || ($this->payment_status === self::PAYMENT_PENDING && $this->expiry_date->isPast());
    }

    public function isVisibleTo($user): bool
    {
        if (!$user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }
        return $this->user_id === $user->id || $this->assigned_to_id === $user->id;
    }

    /** Subscriptions visible to user: all if admin, else own or assigned. */
    public function scopeVisibleToUser(Builder $query, $user): void
    {
        if (!$user) {
            $query->whereRaw('1 = 0');
            return;
        }
        if ($user->isAdmin()) {
            return;
        }
        $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('assigned_to_id', $user->id);
        });
    }

    /** Create or update calendar reminders for expiry (and X days before if set). */
    public function syncExpiryReminders(): void
    {
        $this->reminders()->delete();

        $titleBase = 'اشتراک: ' . $this->service_name . ' — ' . $this->contact->name;
        $userId = $this->assigned_to_id ?? $this->user_id;

        $this->reminders()->create([
            'title' => 'انقضا — ' . $titleBase,
            'body' => $this->description,
            'due_date' => $this->expiry_date,
            'due_time' => null,
            'type' => Reminder::TYPE_SUBSCRIPTION_EXPIRY,
            'user_id' => $userId,
        ]);

        if ($this->reminder_days_before) {
            $remindDate = $this->expiry_date->copy()->subDays($this->reminder_days_before);
            if ($remindDate->isPast()) {
                return;
            }
            $this->reminders()->create([
                'title' => 'یادآوری (' . $this->reminder_days_before . ' روز قبل) — ' . $titleBase,
                'body' => $this->description,
                'due_date' => $remindDate,
                'due_time' => null,
                'type' => Reminder::TYPE_SUBSCRIPTION_REMINDER,
                'user_id' => $userId,
            ]);
        }
    }
}
