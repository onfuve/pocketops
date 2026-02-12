<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PriceList extends Model
{
    protected $fillable = [
        'name',
        'code',
        'template',
        'show_prices',
        'show_photos',
        'show_search',
        'show_quick_access',
        'price_format',
        'show_cta',
        'cta_url',
        'cta_text',
        'show_notes',
        'notes_text',
        'show_social',
        'social_instagram',
        'social_telegram',
        'social_whatsapp',
        'show_address',
        'address_text',
        'show_contact',
        'contact_phone',
        'contact_email',
        'show_share_buttons',
        'user_id',
        'title_text',
        'primary_color',
        'font_family',
        'is_active',
    ];

    protected $casts = [
        'show_prices' => 'boolean',
        'show_photos' => 'boolean',
        'show_search' => 'boolean',
        'show_quick_access' => 'boolean',
        'show_cta' => 'boolean',
        'show_notes' => 'boolean',
        'show_social' => 'boolean',
        'show_address' => 'boolean',
        'show_contact' => 'boolean',
        'show_share_buttons' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(PriceListSection::class)->orderBy('sort_order')->orderBy('id');
    }

    /** Price lists visible to the given user: own, or all for admin. */
    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isAdmin()) {
            return $query;
        }
        return $query->where(function ($q) use ($user) {
            $q->where('user_id', $user->id)->orWhereNull('user_id');
        });
    }

    public function isVisibleTo(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }
        return $this->user_id === $user->id || $this->user_id === null;
    }

    public function getPublicUrlAttribute(): ?string
    {
        if (!$this->code) {
            return null;
        }
        return url('/pricelist/' . $this->code);
    }
}
