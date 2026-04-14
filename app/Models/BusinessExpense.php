<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class BusinessExpense extends Model
{
    protected $table = 'business_expenses';

    protected $fillable = [
        'user_id',
        'amount',
        'fee_amount',
        'paid_at',
        'expense_category_id',
        'payment_option_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'fee_amount' => 'decimal:0',
        'paid_at' => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function expenseCategory(): BelongsTo
    {
        return $this->belongsTo(ExpenseCategory::class, 'expense_category_id');
    }

    public function paymentOption(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /** Portions of this expense attributed to sell invoice lines (COGS). */
    public function itemExpenseAllocations(): HasMany
    {
        return $this->hasMany(InvoiceItemExpenseAllocation::class, 'business_expense_id');
    }

    /** Rial already allocated from this expense to sell lines (all invoices). */
    public function totalAllocatedToSellLinesRial(): int
    {
        return (int) $this->itemExpenseAllocations()->sum('amount_rial');
    }

    /** Rial still available to allocate from this expense. */
    public function remainingAllocatableRial(): int
    {
        return max(0, $this->totalOutlayRial() - $this->totalAllocatedToSellLinesRial());
    }

    public function scopeVisibleToUser($query, ?User $user)
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }

    public function isVisibleTo(?User $user): bool
    {
        if (! $user) {
            return false;
        }
        if ($user->isAdmin()) {
            return true;
        }

        return (int) $this->user_id === (int) $user->id;
    }

    /** Amount + card/transfer fee (rial). */
    public function totalOutlayRial(): int
    {
        return (int) $this->amount + (int) ($this->fee_amount ?? 0);
    }
}
