<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContactTransaction extends Model
{
    protected $fillable = [
        'contact_id',
        'type',
        'amount',
        'paid_at',
        'bank_account_id',
        'payment_option_id',
        'counterparty_contact_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:0',
        'paid_at' => 'date',
    ];

    public const TYPE_RECEIVE = 'receive';
    public const TYPE_PAY = 'pay';

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    public function paymentOption(): BelongsTo
    {
        return $this->belongsTo(PaymentOption::class);
    }

    public function counterpartyContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'counterparty_contact_id');
    }

    /**
     * Balance convention: positive = we owe them, negative = they owe us.
     * Receive from contact: they paid us → contact.balance += amount.
     * Pay to contact: we paid them → contact.balance -= amount.
     * If counterparty: receive = they got paid on our behalf → counterparty.balance -= amount; pay = we paid through them → counterparty.balance += amount.
     */
    public function applyBalances(): void
    {
        $amount = (float) $this->amount;
        $contact = $this->contact;

        if ($this->type === self::TYPE_RECEIVE) {
            $contact->balance += $amount;
        } else {
            $contact->balance -= $amount;
        }
        $contact->save();

        if ($this->counterparty_contact_id) {
            $counterparty = $this->counterpartyContact;
            if ($this->type === self::TYPE_RECEIVE) {
                $counterparty->balance -= $amount;
            } else {
                $counterparty->balance += $amount;
            }
            $counterparty->save();
        }
    }

    public function reverseBalances(): void
    {
        $amount = (float) $this->amount;
        $contact = $this->contact;

        if ($this->type === self::TYPE_RECEIVE) {
            $contact->balance -= $amount;
        } else {
            $contact->balance += $amount;
        }
        $contact->save();

        if ($this->counterparty_contact_id) {
            $counterparty = $this->counterpartyContact;
            if ($this->type === self::TYPE_RECEIVE) {
                $counterparty->balance += $amount;
            } else {
                $counterparty->balance -= $amount;
            }
            $counterparty->save();
        }
    }
}
