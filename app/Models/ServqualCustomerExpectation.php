<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServqualCustomerExpectation extends Model
{
    protected $table = 'servqual_customer_expectations';

    protected $fillable = [
        'contact_id',
        'dimension_id',
        'value',
        'captured_at',
    ];

    protected $casts = [
        'value' => 'integer',
        'captured_at' => 'datetime',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(ServqualDimension::class, 'dimension_id');
    }
}
