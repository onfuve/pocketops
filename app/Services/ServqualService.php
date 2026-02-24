<?php

namespace App\Services;

use App\Models\Contact;
use App\Models\CustomerQualityIndex;
use App\Models\FormSubmission;
use App\Models\Invoice;
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

    /** Normalized score 0–100 from Likert value 1–5. */
    public static function normalizedScore(int $value): float
    {
        return max(0, min(100, (($value - 1) / 4) * 100));
    }

    /**
     * Select one random question per dimension for the micro survey (5 questions total).
     * Gives balanced coverage across SERVQUAL dimensions.
     */
    public function pickOneQuestionPerDimension(): array
    {
        $dimensions = ServqualDimension::with('questions')->orderBy('sort')->get();
        $questions = [];
        foreach ($dimensions as $dimension) {
            $q = $dimension->questions->isEmpty()
                ? null
                : $dimension->questions->random();
            if ($q) {
                $questions[] = $q;
            }
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
     * Overall SERVQUAL index (average of dimension scores).
     */
    public function overallScoreForContact(int $contactId, int $days = 90): ?float
    {
        $scores = $this->dimensionScoresForContact($contactId, $days);
        if (empty($scores)) {
            return null;
        }
        return round(array_sum($scores) / count($scores), 2);
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
     * Update or create CustomerQualityIndex for contact.
     */
    public function updateCustomerQualityIndex(int $contactId): void
    {
        $overall = $this->overallScoreForContact($contactId);
        $dimensionScores = $this->dimensionScoresForContact($contactId);
        $recency = $this->recencyWeightedScoreForContact($contactId);
        $confidence = $this->confidenceRatioForContact($contactId);
        $riskFlags = $this->riskFlagsForContact($contactId);

        CustomerQualityIndex::updateOrCreate(
            ['contact_id' => $contactId],
            [
                'overall_score' => $overall,
                'dimension_scores' => $dimensionScores,
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
}
