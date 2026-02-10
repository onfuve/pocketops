<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'code_global',
        'code_internal',
        'default_unit_price',
        'unit',
        'photo_path',
        'user_id',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'default_unit_price' => 'decimal:0',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function landingPage(): HasOne
    {
        return $this->hasOne(ProductLandingPage::class);
    }

    /**
     * Products visible to the given user.
     *
     * All authenticated users (admin + team) share the same list of
     * goods/services, similar to contacts. Only unauthenticated users
     * are blocked here; per-action authorization is handled elsewhere.
     */
    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        return $query;
    }

    /** Smart search: word-split, match name/description/tags, not sequential. */
    public static function scopeSearch($query, ?string $q): void
    {
        if (blank($q)) {
            return;
        }
        $words = preg_split('/\s+/u', trim($q), -1, PREG_SPLIT_NO_EMPTY);
        if (empty($words)) {
            return;
        }
        $query->where(function ($qry) use ($words) {
            foreach ($words as $word) {
                $w = '%' . $word . '%';
                $qry->where(function ($sub) use ($w) {
                    $sub->where('products.name', 'like', $w)
                        ->orWhere('products.description', 'like', $w)
                        ->orWhereHas('tags', fn ($t) => $t->where('tags.name', 'like', $w));
                });
            }
        });
        $starts = trim($q) . '%';
        $contains = '%' . trim($q) . '%';
        $query->orderByRaw("CASE WHEN products.name LIKE ? THEN 0 WHEN products.name LIKE ? THEN 1 ELSE 2 END", [$starts, $contains]);
    }

    /** Public URL for product photo. */
    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::url($this->photo_path);
    }
}
