<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Contact extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'website',
        'instagram',
        'telegram',
        'whatsapp',
        'referrer_name',
        'is_hamkar',
        'linked_contact_id',
        'balance',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'is_hamkar' => 'boolean',
        'balance' => 'decimal:0',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** Contacts visible to the given user: all authenticated users can see and use all contacts. */
    public function scopeVisibleToUser(Builder $query, $user): void
    {
        if (!$user) {
            $query->whereRaw('1 = 0');
        }
    }

    public function isVisibleTo($user): bool
    {
        return $user !== null;
    }

    public function linkedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'linked_contact_id');
    }

    public function contactPhones(): HasMany
    {
        return $this->hasMany(ContactPhone::class)->orderBy('sort');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class)->latest('date');
    }

    public function invoicePaymentsAsCounterparty(): HasMany
    {
        return $this->hasMany(InvoicePayment::class, 'contact_id');
    }

    public function contactTransactions(): HasMany
    {
        return $this->hasMany(ContactTransaction::class)->orderByDesc('paid_at');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable')->latest();
    }

    public function qualityIndex(): HasOne
    {
        return $this->hasOne(CustomerQualityIndex::class);
    }

    /** Balance we owe (positive) or they owe us (negative). */
    public function getBalanceAttribute($value): float
    {
        return (float) ($value ?? 0);
    }

    public function scopeSearch($query, ?string $q): void
    {
        if (blank($q)) {
            return;
        }
        $query->where(function ($qry) use ($q) {
            $qry->where('name', 'like', "%{$q}%")
                ->orWhere('address', 'like', "%{$q}%")
                ->orWhere('city', 'like', "%{$q}%")
                ->orWhere('referrer_name', 'like', "%{$q}%")
                ->orWhere('notes', 'like', "%{$q}%")
                ->orWhereHas('contactPhones', fn ($p) => $p->where('phone', 'like', "%{$q}%"));
        });
    }
}
