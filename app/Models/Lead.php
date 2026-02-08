<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'company',
        'phone',
        'email',
        'source',
        'details',
        'status',
        'value',
        'lead_date',
        'lead_channel_id',
        'referrer_contact_id',
        'contact_id',
        'user_id',
        'assigned_to_id',
    ];

    protected $casts = [
        'lead_date' => 'date',
        'value' => 'decimal:0',
    ];

    public const STATUS_NEW = 'new';
    public const STATUS_CONTACTED = 'contacted';
    public const STATUS_QUALIFIED = 'qualified';
    public const STATUS_PROPOSAL = 'proposal';
    public const STATUS_WON = 'won';
    public const STATUS_LOST = 'lost';

    /** Short labels + colors for small-business pipeline (6 stages). */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_NEW => 'جدید',
            self::STATUS_CONTACTED => 'تماس',
            self::STATUS_QUALIFIED => 'جدی',
            self::STATUS_PROPOSAL => 'پیشنهاد',
            self::STATUS_WON => 'بسته شد',
            self::STATUS_LOST => 'رد شد',
        ];
    }

    public static function statusColor(string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => 'bg-sky-100 text-sky-800 border-sky-200',
            self::STATUS_CONTACTED => 'bg-amber-50 text-amber-800 border-amber-200',
            self::STATUS_QUALIFIED => 'bg-violet-50 text-violet-800 border-violet-200',
            self::STATUS_PROPOSAL => 'bg-blue-100 text-blue-800 border-blue-200',
            'negotiation' => 'bg-blue-100 text-blue-800 border-blue-200', // legacy
            self::STATUS_WON => 'bg-emerald-100 text-emerald-800 border-emerald-300',
            self::STATUS_LOST => 'bg-red-50 text-red-700 border-red-200',
            default => 'bg-stone-100 text-stone-700 border-stone-200',
        };
    }

    /** Get status background color (hex) for inline styles. */
    public static function statusBgColor(string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => '#e0f2fe', // sky-100
            self::STATUS_CONTACTED => '#fffbeb', // amber-50
            self::STATUS_QUALIFIED => '#f5f3ff', // violet-50
            self::STATUS_PROPOSAL => '#dbeafe', // blue-100
            'negotiation' => '#dbeafe',
            self::STATUS_WON => '#d1fae5', // emerald-100
            self::STATUS_LOST => '#fef2f2', // red-50
            default => '#f5f5f4', // stone-100
        };
    }

    /** Get status text/border color (hex) for inline styles. */
    public static function statusTextColor(string $status): string
    {
        return match ($status) {
            self::STATUS_NEW => '#075985', // sky-800
            self::STATUS_CONTACTED => '#92400e', // amber-800
            self::STATUS_QUALIFIED => '#6b21a8', // violet-800
            self::STATUS_PROPOSAL => '#1e40af', // blue-800
            'negotiation' => '#1e40af',
            self::STATUS_WON => '#065f46', // emerald-800
            self::STATUS_LOST => '#b91c1c', // red-700
            default => '#44403c', // stone-700
        };
    }

    public static function pipelineStatuses(): array
    {
        return [
            self::STATUS_NEW,
            self::STATUS_CONTACTED,
            self::STATUS_QUALIFIED,
            self::STATUS_PROPOSAL,
            self::STATUS_WON,
            self::STATUS_LOST,
        ];
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function leadChannel(): BelongsTo
    {
        return $this->belongsTo(LeadChannel::class);
    }

    public function referrerContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'referrer_contact_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    /** Leads visible to the given user: own, assigned to them, or all for admin. */
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
                ->orWhere('assigned_to_id', $user->id);
        });
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

    public function getStatusLabelAttribute(): string
    {
        $labels = self::statusLabels();
        $labels['negotiation'] = 'مذاکره'; // legacy

        return $labels[$this->status] ?? $this->status;
    }

    public function scopeOfStatus($query, ?string $status): void
    {
        if ($status && in_array($status, self::pipelineStatuses(), true)) {
            $query->where('status', $status);
        }
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class)->latest('activity_date')->latest('id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(LeadComment::class)->latest();
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable')->latest();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeSearch($query, ?string $q): void
    {
        if (blank($q)) {
            return;
        }
        $query->where(function ($qry) use ($q) {
            $qry->where('name', 'like', "%{$q}%")
                ->orWhere('company', 'like', "%{$q}%")
                ->orWhere('phone', 'like', "%{$q}%")
                ->orWhere('email', 'like', "%{$q}%")
                ->orWhere('source', 'like', "%{$q}%")
                ->orWhere('details', 'like', "%{$q}%");
        });
    }
}
