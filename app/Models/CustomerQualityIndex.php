<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerQualityIndex extends Model
{
    protected $table = 'customer_quality_index';

    protected $fillable = [
        'contact_id',
        'overall_score',
        'overall_gap',
        'dimension_scores',
        'dimension_gaps',
        'ewma_per_dimension',
        'recency_weighted_score',
        'confidence_ratio',
        'risk_flags',
        'last_calculated_at',
    ];

    protected $casts = [
        'overall_score' => 'decimal:2',
        'overall_gap' => 'decimal:2',
        'recency_weighted_score' => 'decimal:2',
        'confidence_ratio' => 'decimal:4',
        'dimension_scores' => 'array',
        'dimension_gaps' => 'array',
        'ewma_per_dimension' => 'array',
        'risk_flags' => 'array',
        'last_calculated_at' => 'datetime',
    ];

    public const BAND_EXCEPTIONAL = 'exceptional';   // 90–100
    public const BAND_STRONG = 'strong';             // 75–89
    public const BAND_ACCEPTABLE = 'acceptable';     // 60–74
    public const BAND_RISK = 'risk';                 // 45–59
    public const BAND_CRITICAL = 'critical';         // <45

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public static function bandForScore(?float $score): ?string
    {
        if ($score === null) {
            return null;
        }
        if ($score >= 90) {
            return self::BAND_EXCEPTIONAL;
        }
        if ($score >= 75) {
            return self::BAND_STRONG;
        }
        if ($score >= 60) {
            return self::BAND_ACCEPTABLE;
        }
        if ($score >= 45) {
            return self::BAND_RISK;
        }
        return self::BAND_CRITICAL;
    }
}
