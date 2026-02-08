<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductLandingPage extends Model
{
    protected $fillable = [
        'product_id',
        'code',
        'headline',
        'subheadline',
        'cta_type',
        'cta_url',
        'cta_button_text',
        'template',
        'primary_color',
        'font_family',
        'photo_path',
        'photos',
        'show_price',
        'price',
        'price_format',
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
        'is_active',
        'user_id',
    ];

    protected $casts = [
        'photos' => 'array',
        'price' => 'decimal:0',
        'show_price' => 'boolean',
        'show_notes' => 'boolean',
        'show_social' => 'boolean',
        'show_address' => 'boolean',
        'show_contact' => 'boolean',
        'show_share_buttons' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

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
        return url('/product/' . $this->code);
    }

    public function getCtaHrefAttribute(): ?string
    {
        $url = $this->cta_url ? trim($this->cta_url) : null;
        if (!$url) {
            return null;
        }
        $phone = preg_replace('/\D/', '', $url);
        return match ($this->cta_type) {
            'call' => 'tel:' . ($phone ?: $url),
            'whatsapp' => 'https://wa.me/' . ($phone ? (str_starts_with($phone, '98') ? $phone : '98' . ltrim($phone, '0')) : $url),
            'purchase', 'link' => str_starts_with($url, 'http') ? $url : 'https://' . $url,
            default => $url,
        };
    }

    public function getDisplayHeadlineAttribute(): string
    {
        return $this->headline ?: $this->product?->name ?? '';
    }

    /** Main/hero image URL: landing page photo, else product photo. */
    public function getMainPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path) {
            return \Illuminate\Support\Facades\Storage::url($this->photo_path);
        }
        return $this->product?->photo_url;
    }

    /** All display photos: main first, then gallery. */
    public function getDisplayPhotoUrlsAttribute(): array
    {
        $urls = [];
        if ($this->main_photo_url) {
            $urls[] = $this->main_photo_url;
        }
        foreach ($this->photos ?? [] as $path) {
            if ($path) {
                $urls[] = \Illuminate\Support\Facades\Storage::url($path);
            }
        }
        return $urls;
    }
}
