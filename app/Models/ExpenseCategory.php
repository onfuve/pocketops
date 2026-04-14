<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class ExpenseCategory extends Model
{
    protected $fillable = [
        'code',
        'name',
        'sort_order',
    ];

    protected static function booted(): void
    {
        static::creating(function (ExpenseCategory $category) {
            if ($category->code === null || $category->code === '') {
                $category->code = self::generateUniqueCode();
            }
        });
    }

    public static function generateUniqueCode(): string
    {
        do {
            $code = 'ec_'.Str::lower(Str::random(12));
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function businessExpenses(): HasMany
    {
        return $this->hasMany(BusinessExpense::class, 'expense_category_id');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
