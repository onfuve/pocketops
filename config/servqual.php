<?php

return [
    'likert_max' => 5,
    'dimension_count' => 5,
    'min_dimensions_for_confidence' => 3,
    'dimension_risk_threshold' => 55,
    'reputation_risk_threshold' => 50,
    'days_for_scoring' => 90,

    // Avoid repeating same question for same customer within last N invoice surveys
    'avoid_question_repeat_last_n' => 3,
    // EWMA alpha (0.2–0.3 for service); higher = more weight to latest
    'ewma_alpha' => 0.25,

    'bands' => [
        'exceptional' => ['min' => 90, 'max' => 100, 'label_fa' => 'استثنایی'],
        'strong' => ['min' => 75, 'max' => 89, 'label_fa' => 'قوی'],
        'acceptable' => ['min' => 60, 'max' => 74, 'label_fa' => 'قابل قبول'],
        'risk' => ['min' => 45, 'max' => 59, 'label_fa' => 'در خطر'],
        'critical' => ['min' => 0, 'max' => 44, 'label_fa' => 'بحرانی'],
    ],
];
