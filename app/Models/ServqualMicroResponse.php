<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServqualMicroResponse extends Model
{
    protected $table = 'servqual_micro_responses';

    protected $fillable = [
        'invoice_id',
        'form_submission_id',
        'dimension_id',
        'value',
        'form_link_code',
    ];

    protected $casts = [
        'value' => 'integer',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function formSubmission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(ServqualDimension::class, 'dimension_id');
    }

    /** Normalized score 0–100 for this response. */
    public function getNormalizedScoreAttribute(): float
    {
        return ServqualQuestionBank::normalizedScore($this->value);
    }
}
