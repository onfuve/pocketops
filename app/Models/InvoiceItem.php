<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /** Cost allocations: this row is a sell line; links to buy receipt lines. */
    public function sellBuyCostLinks(): HasMany
    {
        return $this->hasMany(InvoiceItemBuyAllocation::class, 'sell_invoice_item_id');
    }

    /** Operational expense portions attributed to this sell line. */
    public function sellExpenseCostLinks(): HasMany
    {
        return $this->hasMany(InvoiceItemExpenseAllocation::class, 'sell_invoice_item_id');
    }

    /** Cost allocations: this row is a buy line; linked from sell lines. */
    public function buySellCostLinks(): HasMany
    {
        return $this->hasMany(InvoiceItemBuyAllocation::class, 'buy_invoice_item_id');
    }

    /** Unit purchase cost (rial) for a buy invoice line. */
    public function purchaseUnitCostRial(): int
    {
        $q = (float) $this->quantity;
        if ($q > 0) {
            return (int) round((float) $this->amount / $q);
        }

        return (int) $this->unit_price;
    }

    /** Totalrial amount of linked buy cost for this sell line (requires sellBuyCostLinks.buyItem). */
    public function totalLinkedBuyCostRial(): int
    {
        $sum = 0;
        foreach ($this->sellBuyCostLinks as $link) {
            $buy = $link->buyItem;
            if (! $buy) {
                continue;
            }
            $sum += (int) round((float) $link->quantity * (float) $buy->purchaseUnitCostRial());
        }

        return $sum;
    }

    /** Totalrial from linked operational expenses for this sell line. */
    public function totalLinkedExpenseCostRial(): int
    {
        return (int) $this->sellExpenseCostLinks->sum('amount_rial');
    }

    /** Linked buy cost + allocated expenses (full attributed COGS for this line). */
    public function totalAttributedCostRial(): int
    {
        return $this->totalLinkedBuyCostRial() + $this->totalLinkedExpenseCostRial();
    }
}
