<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServqualQuestionBank extends Model
{
    protected $table = 'servqual_question_bank';

    protected $fillable = [
        'dimension_id',
        'text',
        'text_fa',
        'weight',
        'is_reverse_scored',
        'service_type',
        'sort',
    ];

    protected $casts = [
        'weight' => 'integer',
        'is_reverse_scored' => 'boolean',
        'sort' => 'integer',
    ];

    public function dimension(): BelongsTo
    {
        return $this->belongsTo(ServqualDimension::class, 'dimension_id');
    }

    /** Adjusted Likert value (1-5) for scoring: reverse if needed. */
    public function adjustedValue(int $selectedValue): int
    {
        $max = 5;
        if ($this->is_reverse_scored) {
            return $max + 1 - $selectedValue;
        }
        return $selectedValue;
    }

    /** Normalized score 0–100 for one response. */
    public static function normalizedScore(int $value): float
    {
        return max(0, min(100, (($value - 1) / 4) * 100));
    }
}
