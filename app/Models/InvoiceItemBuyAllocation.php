<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItemBuyAllocation extends Model
{
    protected $table = 'invoice_item_buy_allocations';

    protected $fillable = [
        'sell_invoice_item_id',
        'buy_invoice_item_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:4',
    ];

    public function sellItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'sell_invoice_item_id');
    }

    public function buyItem(): BelongsTo
    {
        return $this->belongsTo(InvoiceItem::class, 'buy_invoice_item_id');
    }
}
