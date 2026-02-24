<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class Invoice extends Model
{
    protected $fillable = [
        'contact_id',
        'type',
        'invoice_number',
        'date',
        'due_date',
        'status',
        'subtotal',
        'discount',
        'discount_percent',
        'total',
        'notes',
        'payment_option_ids',
        'payment_option_fields',
        'user_id',
        'assigned_to_id',
        'form_link_id',
    ];

    protected $casts = [
        'date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:0',
        'discount' => 'decimal:0',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:0',
        'payment_option_ids' => 'array',
        'payment_option_fields' => 'array',
    ];

    public const TYPE_SELL = 'sell';
    public const TYPE_BUY = 'buy';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_FINAL = 'final';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class)->orderBy('paid_at');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to_id');
    }

    public function formLink(): BelongsTo
    {
        return $this->belongsTo(FormLink::class);
    }

    /** Invoices visible to the given user: own, assigned to them, or all for admin / can_see_all_invoices. */
    public function scopeVisibleToUser($query, $user)
    {
        if (!$user) {
            return $query->whereRaw('1 = 0');
        }
        if ($user->canSeeAllInvoices()) {
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
        if ($user->canSeeAllInvoices()) {
            return true;
        }
        return $this->user_id === $user->id || $this->assigned_to_id === $user->id;
    }

    /** Payment options selected for this invoice (for print/pdf). Empty = use all. */
    public function selectedPaymentOptions(): \Illuminate\Database\Eloquent\Collection
    {
        $ids = $this->payment_option_ids ?? [];
        if ($ids === []) {
            return PaymentOption::forPrint();
        }
        return PaymentOption::whereIn('id', $ids)->orderBy('sort')->get();
    }

    /** Flat list of payment lines for print/pdf, using payment_option_fields override when set. */
    public function paymentLinesForPrint(): array
    {
        $options = $this->selectedPaymentOptions();
        $overrides = $this->payment_option_fields ?? [];
        $lines = [];
        foreach ($options as $opt) {
            $override = $overrides[(string) $opt->id] ?? null;
            foreach ($opt->linesForPrint($override) as $line) {
                $lines[] = $line;
            }
        }
        return $lines;
    }

    public function recalculateTotals(): void
    {
        $subtotal = (int) $this->items()->sum('amount');
        $discount = $this->discount_percent !== null
            ? (int) round($subtotal * (float) $this->discount_percent / 100)
            : (int) $this->discount;
        $this->update([
            'subtotal' => $subtotal,
            'total' => max(0, $subtotal - $discount),
        ]);
    }

    /** Effective discount amount (from fixed amount or percent of subtotal). */
    public function effectiveDiscount(): int
    {
        $subtotal = (int) $this->subtotal ?: $this->items()->sum('amount');
        if ($this->discount_percent !== null) {
            return (int) round($subtotal * (float) $this->discount_percent / 100);
        }
        return (int) $this->discount;
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable')->latest();
    }

    public function reminders(): MorphMany
    {
        return $this->morphMany(Reminder::class, 'remindable')->latest();
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function scopeOfType($query, ?string $type): void
    {
        if ($type && in_array($type, [self::TYPE_SELL, self::TYPE_BUY], true)) {
            $query->where('type', $type);
        }
    }

    /** Calculate total paid amount */
    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    /** Check if invoice is fully paid */
    public function isPaid(): bool
    {
        return $this->totalPaid() >= $this->total;
    }

    /**
     * When invoice is marked final: update contact balance so they owe us (sell) or we owe them (buy).
     * Balance convention: positive = we owe them, negative = they owe us.
     */
    public function applyContactBalanceForInvoice(): void
    {
        $contact = $this->contact;
        if (!$contact) {
            return;
        }
        $total = (float) $this->total;
        if ($this->type === self::TYPE_SELL) {
            $contact->balance -= $total;
        } else {
            $contact->balance += $total;
        }
        $contact->save();
    }

    /** Reverse the balance applied by applyContactBalanceForInvoice (e.g. when deleting or editing finalized invoice with no payments). */
    public function reverseContactBalanceForInvoice(): void
    {
        $contact = $this->contact;
        if (!$contact) {
            return;
        }
        $total = (float) $this->total;
        if ($this->type === self::TYPE_SELL) {
            $contact->balance += $total;
        } else {
            $contact->balance -= $total;
        }
        $contact->save();
    }

    /** Whether this finalized invoice can be edited or deleted (no payments/transactions recorded). */
    public function canEditOrDelete(): bool
    {
        return !$this->payments()->exists();
    }
}
