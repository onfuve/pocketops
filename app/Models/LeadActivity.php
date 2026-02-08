<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeadActivity extends Model
{
    protected $fillable = [
        'lead_id',
        'from_status',
        'to_status',
        'comment',
        'activity_date',
    ];

    protected $casts = [
        'activity_date' => 'date',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
