<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceListItem extends Model
{
    public const BADGES = [
        'new' => 'جدید',
        'hot' => 'جدید',
        'special_offer' => 'پیشنهاد ویژه',
        'sale' => 'تخفیف',
    ];

    protected $fillable = [
        'price_list_section_id',
        'product_id',
        'custom_name',
        'custom_description',
        'unit_price',
        'unit',
        'badge',
        'sort_order',
    ];

    protected $casts = [
        'unit_price' => 'decimal:0',
        'sort_order' => 'integer',
    ];

    public function section(): BelongsTo
    {
        return $this->belongsTo(PriceListSection::class, 'price_list_section_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getDisplayNameAttribute(): string
    {
        if ($this->custom_name) {
            return $this->custom_name;
        }
        return $this->product?->name ?? '—';
    }

    public function getDisplayDescriptionAttribute(): ?string
    {
        if ($this->custom_description !== null && $this->custom_description !== '') {
            return $this->custom_description;
        }
        return $this->product?->description;
    }

    public function getEffectivePriceAttribute(): ?int
    {
        if ($this->unit_price !== null) {
            return (int) $this->unit_price;
        }
        return $this->product?->default_unit_price ? (int) $this->product->default_unit_price : null;
    }

    public function getEffectiveUnitAttribute(): ?string
    {
        return $this->unit ?: $this->product?->unit ?: 'عدد';
    }

    /** Badge label for display, or null if no badge. */
    public function getBadgeLabelAttribute(): ?string
    {
        return $this->badge ? (self::BADGES[$this->badge] ?? $this->badge) : null;
    }
}
