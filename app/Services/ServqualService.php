<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\CustomerQualityIndex;
use App\Models\FormSubmission;
use App\Models\Invoice;
use App\Models\ServqualCustomerExpectation;
use App\Models\ServqualDimension;
use App\Models\ServqualMicroResponse;
use App\Models\ServqualQuestionBank;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ServqualService
{
    /** Likert scale max (1–5). */
    public const LIKERT_MAX = 5;

    /** Number of dimensions (SERVQUAL). */
    public const DIMENSION_COUNT = 5;

    /** Minimum dimensions with data for reliable index. */
    public const MIN_DIMENSIONS_FOR_CONFIDENCE = 3;

    /** Threshold: flag account if two dimensions below this. */
    public const DIMENSION_RISK_THRESHOLD = 55;

    /** Max gap range (P and E 1–5 so gap in [-4, 4]). Normalized to 0–100 scale: (gap/4)*100. */
    public const GAP_RANGE = 4;

    /** Normalized score 0–100 from Likert value 1–5. */
    public static function normalizedScore(int $value): float
    {
        return max(0, min(100, (($value - 1) / 4) * 100));
    }

    /** Check if contact has full baseline expectation (all 5 dimensions). */
    public function hasBaselineExpectation(int $contactId): bool
    {
        return ServqualCustomerExpectation::where('contact_id', $contactId)->count() >= self::DIMENSION_COUNT;
    }

    /** Get baseline expectation per dimension (dimension_id => value 1–5). */
    public function getBaselineExpectation(int $contactId): array
    {
        $rows = ServqualCustomerExpectation::where('contact_id', $contactId)->get();
        $map = [];
        foreach ($rows as $r) {
            $map[$r->dimension_id] = (int) $r->value;
        }
        return $map;
    }

    /** Save or update baseline expectations. Keys: dimension_id, values: 1–5. */
    public function saveBaselineExpectation(int $contactId, array $dimensionValues): void
    {
        foreach ($dimensionValues as $dimensionId => $value) {
            $value = max(1, min(self::LIKERT_MAX, (int) $value));
            ServqualCustomerExpectation::updateOrCreate(
                ['contact_id' => $contactId, 'dimension_id' => $dimensionId],
                ['value' => $value, 'captured_at' => now()]
            );
        }
    }

    /** Question IDs used for this contact in last N invoice surveys (to avoid repetition). */
    public function getExcludedQuestionIdsForContact(int $contactId): array
    {
        $n = (int) config('servqual.avoid_question_repeat_last_n', 3);
        $responses = ServqualMicroResponse::query()
            ->join('invoices', 'servqual_micro_responses.invoice_id', '=', 'invoices.id')
            ->where('invoices.contact_id', $contactId)
            ->whereNotNull('servqual_micro_responses.form_submission_id')
            ->select('servqual_micro_responses.form_submission_id')
            ->orderBy('servqual_micro_responses.created_at', 'desc')
            ->get();
        $seen = [];
        foreach ($responses as $r) {
            $sid = $r->form_submission_id;
            if ($sid && !in_array($sid, $seen, true)) {
                $seen[] = $sid;
                if (count($seen) >= $n) {
                    break;
                }
            }
        }
        if (empty($seen)) {
            return [];
        }
        $ids = FormSubmission::whereIn('id', $seen)->get()->pluck('data')->filter()->map(function ($d) {
            return $d['servqual_question_ids'] ?? [];
        })->flatten()->unique()->values()->all();
        return $ids;
    }

    /**
     * Select one random question per dimension from the question bank.
     * Excludes question IDs recently used for this contact (last N surveys) to avoid repetition.
     */
    public function pickOneQuestionPerDimension(?Invoice $invoice = null): array
    {
        $dimensions = ServqualDimension::with('questions')->orderBy('sort')->get();
        $excludedIds = [];
        if ($invoice && $invoice->contact_id) {
            $excludedIds = $this->getExcludedQuestionIdsForContact($invoice->contact_id);
        }
        $questions = [];
        foreach ($dimensions as $dimension) {
            if ($dimension->questions->isEmpty()) {
                continue;
            }
            $pool = $dimension->questions->reject(fn ($q) => in_array($q->id, $excludedIds, true));
            $q = $pool->isEmpty() ? $dimension->questions->random() : $pool->random();
            $questions[] = $q;
        }
        return $questions;
    }

    /**
     * @deprecated Use pickOneQuestionPerDimension() for one question per dimension (5 total).
     */
    public function pickTwoQuestionsForMicroSurvey(?int $invoiceId = null): array
    {
        $all = $this->pickOneQuestionPerDimension();
        return array_slice($all, 0, 2);
    }

    /**
     * Store micro survey responses (one per dimension). Returns created ServqualMicroResponse models.
     */
    public function storeMicroResponses(
        Invoice $invoice,
        array $responses,
        ?FormSubmission $submission = null,
        ?string $formLinkCode = null
    ): array {
        $created = [];
        foreach ($responses as $questionId => $value) {
            $value = (int) $value;
            if ($value < 1 || $value > self::LIKERT_MAX) {
                continue;
            }
            $question = ServqualQuestionBank::find($questionId);
            if (!$question) {
                continue;
            }
            $adjusted = $question->adjustedValue($value);
            $r = ServqualMicroResponse::create([
                'invoice_id' => $invoice->id,
                'form_submission_id' => $submission?->id,
                'dimension_id' => $question->dimension_id,
                'question_id' => $question->id,
                'value' => $adjusted,
                'form_link_code' => $formLinkCode,
            ]);
            $created[] = $r;
        }
        if (count($created) > 0 && $invoice->contact_id) {
            $this->updateCustomerQualityIndex($invoice->contact_id);
        }
        return $created;
    }

    /**
     * Dimension scores (0–100) for a contact over last 90 days.
     */
    public function dimensionScoresForContact(int $contactId, int $days = 90): array
    {
        $since = Carbon::now()->subDays($days);
        $rows = ServqualMicroResponse::query()
            ->join('invoices', 'servqual_micro_responses.invoice_id', '=', 'invoices.id')
            ->where('invoices.contact_id', $contactId)
            ->where('servqual_micro_responses.created_at', '>=', $since)
            ->select('servqual_dimensions.code as dimension_code', DB::raw('AVG((servqual_micro_responses.value - 1) / 4 * 100) as score'))
            ->join('servqual_dimensions', 'servqual_micro_responses.dimension_id', '=', 'servqual_dimensions.id')
            ->groupBy('servqual_dimensions.id', 'servqual_dimensions.code')
            ->get()
            ->keyBy('dimension_code');

        $scores = [];
        foreach ($rows as $code => $r) {
            $scores[$code] = round((float) $r->score, 2);
        }
        return $scores;
    }

    /**
     * Perception averages per dimension (1–5) for contact over period. Keys: dimension_id.
     */
    public function perceptionAveragesForContact(int $contactId, int $days = 90): array
    {
        $since = Carbon::now()->subDays($days);
        $rows = ServqualMicroResponse::query()
            ->join('invoices', 'servqual_micro_responses.invoice_id', '=', 'invoices.id')
            ->where('invoices.contact_id', $contactId)
            ->where('servqual_micro_responses.created_at', '>=', $since)
            ->select('servqual_micro_responses.dimension_id', DB::raw('AVG(servqual_micro_responses.value) as avg_value'))
            ->groupBy('servqual_micro_responses.dimension_id')
            ->get();
        $out = [];
        foreach ($rows as $r) {
            $out[$r->dimension_id] = round((float) $r->avg_value, 4);
        }
        return $out;
    }

    /**
     * Gap per dimension: P - E. Normalized to 0–100 scale: (gap/4)*100. Keys: dimension code.
     * Returns [ 'gaps' => code => normalized_gap, 'overall_gap' => weighted avg ].
     */
    public function computeGapsForContact(int $contactId, int $days = 90): array
    {
        $baseline = $this->getBaselineExpectation($contactId);
        if (count($baseline) < self::DIMENSION_COUNT) {
            return ['gaps' => [], 'overall_gap' => null];
        }
        $perception = $this->perceptionAveragesForContact($contactId, $days);
        $dimensions = ServqualDimension::orderBy('sort')->get()->keyBy('id');
        $gaps = [];
        $weightedSum = 0;
        $weightTotal = 0;
        foreach ($dimensions as $id => $dim) {
            $p = $perception[$id] ?? null;
            $e = $baseline[$id] ?? null;
            if ($p === null || $e === null) {
                continue;
            }
            $gapRaw = $p - $e;
            $normalizedGap = ($gapRaw / self::GAP_RANGE) * 100; // -100 to +100
            $gaps[$dim->code] = round($normalizedGap, 2);
            $w = (float) ($dim->weight ?? 1);
            $weightedSum += $normalizedGap * $w;
            $weightTotal += $w;
        }
        $overallGap = $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : null;
        return ['gaps' => $gaps, 'overall_gap' => $overallGap];
    }

    /**
     * Overall SERVQUAL index (weighted average of dimension scores). Reliability + Assurance weighted 1.2.
     */
    public function overallScoreForContact(int $contactId, int $days = 90): ?float
    {
        $scores = $this->dimensionScoresForContact($contactId, $days);
        if (empty($scores)) {
            return null;
        }
        $dimensions = ServqualDimension::orderBy('sort')->get()->keyBy('code');
        $weightedSum = 0;
        $weightTotal = 0;
        foreach ($scores as $code => $score) {
            $dim = $dimensions->get($code);
            $w = $dim ? (float) ($dim->weight ?? 1) : 1;
            $weightedSum += $score * $w;
            $weightTotal += $w;
        }
        return $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : null;
    }

    /**
     * Confidence ratio: dimensions_with_data / 5.
     */
    public function confidenceRatioForContact(int $contactId, int $days = 90): float
    {
        $scores = $this->dimensionScoresForContact($contactId, $days);
        return round(count($scores) / self::DIMENSION_COUNT, 4);
    }

    /**
     * Recency-weighted score (more recent = higher weight).
     */
    public function recencyWeightedScoreForContact(int $contactId, int $days = 90): ?float
    {
        $responses = ServqualMicroResponse::query()
            ->join('invoices', 'servqual_micro_responses.invoice_id', '=', 'invoices.id')
            ->where('invoices.contact_id', $contactId)
            ->where('servqual_micro_responses.created_at', '>=', Carbon::now()->subDays($days))
            ->select('servqual_micro_responses.value', 'servqual_micro_responses.created_at')
            ->get();

        if ($responses->isEmpty()) {
            return null;
        }

        $weightedSum = 0;
        $weightSum = 0;
        foreach ($responses as $r) {
            $norm = self::normalizedScore($r->value);
            $daysAgo = Carbon::parse($r->created_at)->diffInDays(Carbon::now());
            $recencyWeight = 1 / ($daysAgo + 1);
            $weightedSum += $norm * $recencyWeight;
            $weightSum += $recencyWeight;
        }
        return $weightSum > 0 ? round($weightedSum / $weightSum, 2) : null;
    }

    /**
     * Risk flags: two dimensions < 55 → flag; transparency/assurance < 50 → review.
     */
    public function riskFlagsForContact(int $contactId, int $days = 90): array
    {
        $scores = $this->dimensionScoresForContact($contactId, $days);
        $flags = [];
        $lowCount = 0;
        foreach ($scores as $code => $score) {
            if ($score < self::DIMENSION_RISK_THRESHOLD) {
                $lowCount++;
            }
            if ($score < 50 && in_array($code, ['assurance', 'reliability'], true)) {
                $flags[] = 'reputation_risk';
            }
        }
        if ($lowCount >= 2) {
            $flags[] = 'account_risk';
        }
        return array_unique($flags);
    }

    /**
     * Update or create CustomerQualityIndex for contact. Includes gap and EWMA when baseline exists.
     */
    public function updateCustomerQualityIndex(int $contactId): void
    {
        $days = (int) config('servqual.days_for_scoring', 90);
        $overall = $this->overallScoreForContact($contactId, $days);
        $dimensionScores = $this->dimensionScoresForContact($contactId, $days);
        $recency = $this->recencyWeightedScoreForContact($contactId, $days);
        $confidence = $this->confidenceRatioForContact($contactId, $days);
        $riskFlags = $this->riskFlagsForContact($contactId, $days);

        $dimensionGaps = null;
        $overallGap = null;
        $ewma = null;
        if ($this->hasBaselineExpectation($contactId)) {
            $gapResult = $this->computeGapsForContact($contactId, $days);
            $dimensionGaps = $gapResult['gaps'];
            $overallGap = $gapResult['overall_gap'];
            $alpha = (float) config('servqual.ewma_alpha', 0.25);
            $index = CustomerQualityIndex::where('contact_id', $contactId)->first();
            $previousEwma = $index && is_array($index->ewma_per_dimension) ? $index->ewma_per_dimension : [];
            $ewma = [];
            foreach ($dimensionGaps as $code => $currentGap) {
                $prev = $previousEwma[$code] ?? $currentGap;
                $ewma[$code] = round($alpha * $currentGap + (1 - $alpha) * $prev, 2);
            }
        }

        CustomerQualityIndex::updateOrCreate(
            ['contact_id' => $contactId],
            [
                'overall_score' => $overall,
                'overall_gap' => $overallGap,
                'dimension_scores' => $dimensionScores,
                'dimension_gaps' => $dimensionGaps,
                'ewma_per_dimension' => $ewma,
                'recency_weighted_score' => $recency,
                'confidence_ratio' => $confidence,
                'risk_flags' => array_values($riskFlags),
                'last_calculated_at' => now(),
            ]
        );
    }

    /**
     * Interpretation band for overall score.
     */
    public static function bandForScore(?float $score): ?string
    {
        return CustomerQualityIndex::bandForScore($score);
    }

    /**
     * Company-wide SERVQUAL stats for the overall report. Uses last $days for dimension/response counts.
     *
     * @return array{ dimension_scores: array<string, float>, overall_score_avg: float|null, overall_gap_avg: float|null, contacts_with_index: int, total_responses: int, responses_last_30_days: int, submissions_count: int }
     */
    public function companyWideStats(int $days = 90): array
    {
        $since = Carbon::now()->subDays($days);
        $since30 = Carbon::now()->subDays(30);

        // Dimension averages (0–100) from all micro responses in period
        $dimRows = ServqualMicroResponse::query()
            ->where('servqual_micro_responses.created_at', '>=', $since)
            ->join('servqual_dimensions', 'servqual_micro_responses.dimension_id', '=', 'servqual_dimensions.id')
            ->select('servqual_dimensions.code as dimension_code', DB::raw('AVG((servqual_micro_responses.value - 1) / 4 * 100) as score'))
            ->groupBy('servqual_dimensions.id', 'servqual_dimensions.code')
            ->get()
            ->keyBy('dimension_code');

        $dimensionScores = [];
        foreach ($dimRows as $code => $r) {
            $dimensionScores[$code] = round((float) $r->score, 2);
        }

        // Weighted overall score from dimension averages
        $dimensions = ServqualDimension::orderBy('sort')->get()->keyBy('code');
        $overallScoreAvg = null;
        if (!empty($dimensionScores)) {
            $weightedSum = 0;
            $weightTotal = 0;
            foreach ($dimensionScores as $code => $score) {
                $dim = $dimensions->get($code);
                $w = $dim ? (float) ($dim->weight ?? 1) : 1;
                $weightedSum += $score * $w;
                $weightTotal += $w;
            }
            $overallScoreAvg = $weightTotal > 0 ? round($weightedSum / $weightTotal, 2) : null;
        }

        // From customer_quality_index: contacts count and average gap
        $indexAgg = CustomerQualityIndex::query()
            ->selectRaw('COUNT(*) as cnt, AVG(overall_score) as avg_score, AVG(overall_gap) as avg_gap')
            ->first();
        $contactsWithIndex = (int) ($indexAgg->cnt ?? 0);
        $overallGapAvg = $indexAgg && $indexAgg->avg_gap !== null ? round((float) $indexAgg->avg_gap, 2) : null;

        // Response counts
        $totalResponses = ServqualMicroResponse::query()->count();
        $responsesLast30Days = ServqualMicroResponse::query()->where('created_at', '>=', $since30)->count();
        $submissionsCount = (int) ServqualMicroResponse::query()
            ->whereNotNull('form_submission_id')
            ->distinct()
            ->count('form_submission_id');

        return [
            'dimension_scores' => $dimensionScores,
            'overall_score_avg' => $overallScoreAvg,
            'overall_gap_avg' => $overallGapAvg,
            'contacts_with_index' => $contactsWithIndex,
            'total_responses' => $totalResponses,
            'responses_last_30_days' => $responsesLast30Days,
            'submissions_count' => $submissionsCount,
        ];
    }
}
