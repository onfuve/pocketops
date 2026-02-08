<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'paid_at',
        'bank_account_id',
        'payment_option_id',
        'contact_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'paid_at' => 'date',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentOption(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * Balance convention: positive = we owe them, negative = they owe us.
     *
     * When a payment is recorded:
     * - Invoice contact (A): A paid us (sell) or we paid A (buy) → update A's balance so invoice is reflected.
     * - Payment contact (B), if set: A paid B (sell) = B got paid on our behalf → we owe B less. We paid via B (buy) = we owe B more.
     */

    /** Update the invoice's contact (A): they paid us (sell) or we paid them (buy). */
    public function applyInvoiceContactBalance(): void
    {
        $invoice = $this->invoice;
        $customer = $invoice->contact;
        if (!$customer) {
            return;
        }
        $amount = (float) $this->amount;
        if ($invoice->type === Invoice::TYPE_SELL) {
            // A (customer) paid us → A owes us less → balance (we owe them = negative when they owe us) goes up
            $customer->balance += $amount;
        } else {
            // We paid A (supplier) → we owe A less
            $customer->balance -= $amount;
        }
        $customer->save();
    }

    public function reverseInvoiceContactBalance(): void
    {
        $invoice = $this->invoice;
        $customer = $invoice->contact;
        if (!$customer) {
            return;
        }
        $amount = (float) $this->amount;
        if ($invoice->type === Invoice::TYPE_SELL) {
            $customer->balance -= $amount;
        } else {
            $customer->balance += $amount;
        }
        $customer->save();
    }

    /**
     * Apply balance change to payment contact (B) when payment is "via contact".
     * Sell: customer A paid B on our behalf → we owe B less → decrease B's balance.
     * Buy: we paid via B → we owe B more → increase B's balance.
     */
    public function applyContactBalance(): void
    {
        if (!$this->contact_id) {
            return;
        }
        $contact = $this->contact;
        $amount = (float) $this->amount;
        $invoice = $this->invoice;
        if ($invoice->type === Invoice::TYPE_SELL) {
            $contact->balance -= $amount;
        } else {
            $contact->balance += $amount;
        }
        $contact->save();
    }

    /** Reverse balance change when a payment is deleted. */
    public function reverseContactBalance(): void
    {
        if (!$this->contact_id) {
            return;
        }
        $contact = $this->contact;
        $amount = (float) $this->amount;
        $invoice = $this->invoice;
        if ($invoice->type === Invoice::TYPE_SELL) {
            $contact->balance += $amount;
        } else {
            $contact->balance -= $amount;
        }
        $contact->save();
    }
}
