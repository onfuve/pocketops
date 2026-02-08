<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BankAccount extends Model
{
    protected $fillable = [
        'name',
        'account_number',
        'bank_name',
        'shaba',
        'notes',
    ];

    public function invoicePayments(): HasMany
    {
        return $this->hasMany(InvoicePayment::class);
    }
}
