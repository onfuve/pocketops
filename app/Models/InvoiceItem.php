<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    protected $fillable = ['invoice_id', 'product_id', 'description', 'quantity', 'unit_price', 'amount', 'sort'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:0',
        'amount' => 'decimal:0',
        'sort' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
