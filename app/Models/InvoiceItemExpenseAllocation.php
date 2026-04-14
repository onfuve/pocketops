<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemExpenseAllocation extends Model
{
    protected $table = 'invoice_item_expense_allocations';

    protected $fillable = [
        'sell_invoice_item_id',
        'business_expense_id',
        'amount_rial',
    ];

    protected $casts = [
        'amount_rial' => 'integer',
    ];

    public function sellItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'sell_invoice_item_id');
    }

    public function businessExpense(): BelongsTo
    {
        return $this->belongsTo(BusinessExpense::class, 'business_expense_id');
    }
}
