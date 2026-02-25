@php
use App\Helpers\FormatHelper;
$dimOrder = ['tangibles', 'reliability', 'responsiveness', 'assurance', 'empathy'];
$dimLabels = [
    'tangibles' => 'ملموسات',
    'reliability' => 'قابلیت اطمینان',
    'responsiveness' => 'پاسخگویی',
    'assurance' => 'اطمینان',
    'empathy' => 'همدلی',
];
$dimensionScores = $stats['dimension_scores'] ?? [];
@endphp
@extends('layouts.app')

@section('title', 'گزارش کیفیت خدمات (SERVQUAL) — ' . config('app.name'))

@push('styles')
<style>
.report-stat-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1rem; text-decoration: none; color: inherit; display: block; transition: all 0.2s; box-shadow: var(--ds-shadow); }
.report-stat-card:hover { border-color: var(--ds-border-hover); }
.report-stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--ds-text); }
.report-stat-card .stat-label { font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem; }
.servqual-radar-label { font-size: 10px !important; font-weight: 500; font-family: inherit; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header" style="margin-bottom: 1.5rem;">
        <div>
            <a href="{{ route('dashboard') }}" class="ds-btn ds-btn-ghost" style="margin-bottom: 0.5rem; display: inline-flex; align-items: center; gap: 0.375rem;">
                @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4'])
                بازگشت به داشبورد
            </a>
            <h1 class="ds-page-title" style="display: flex; align-items: center; gap: 0.5rem;">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #d1fae5 0%, #e0f2fe 100%); color: #047857;">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5'])
                </span>
                گزارش کیفیت خدمات (SERVQUAL)
            </h1>
            <p class="ds-page-subtitle">میانگین شرکت در {{ $days }} روز اخیر · شاخص‌های کسب‌وکار</p>
        </div>
    </div>

    {{-- Business indicators --}}
    <h2 class="text-sm font-semibold text-stone-600 mb-2" style="display: flex; align-items: center; gap: 0.5rem;">
        @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
        شاخص‌های کسب‌وکار
    </h2>
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 2rem;">
        <a href="{{ route('contacts.index') }}" class="report-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $contactsCount) }}</div>
            <div class="stat-label">مخاطبین</div>
        </a>
        <a href="{{ route('leads.index') }}" class="report-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $leadsCount) }}</div>
            <div class="stat-label">سرنخ‌ها</div>
        </a>
        <a href="{{ route('invoices.index') }}" class="report-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $invoicesSellTotal) }}</div>
            <div class="stat-label">فاکتور فروش (کل)</div>
        </a>
        <a href="{{ route('invoices.index') }}" class="report-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $invoicesSellLast30) }}</div>
            <div class="stat-label">فاکتور فروش (۳۰ روز)</div>
        </a>
    </div>

    {{-- SERVQUAL overall --}}
    <h2 class="text-sm font-semibold text-stone-600 mb-2" style="display: flex; align-items: center; gap: 0.5rem;">
        @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
        نظرسنجی SERVQUAL
    </h2>
    <div class="ds-form-card" style="margin-bottom: 1.5rem;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap: 1rem; margin-bottom: 1rem;">
            <div>
                <span class="text-sm text-stone-500">میانگین امتیاز درک (P)</span>
                <p class="text-xl font-bold m-0" style="color: #047857;">{{ $stats['overall_score_avg'] !== null ? round($stats['overall_score_avg'], 1) : '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-stone-500">میانگین فاصله (Gap)</span>
                <p class="text-xl font-bold m-0" style="color: {{ ($stats['overall_gap_avg'] ?? 0) >= 0 ? '#047857' : '#b91c1c' }};">{{ $stats['overall_gap_avg'] !== null ? round($stats['overall_gap_avg'], 1) : '—' }}</p>
            </div>
            <div>
                <span class="text-sm text-stone-500">مخاطبین با شاخص</span>
                <p class="text-xl font-bold m-0">{{ FormatHelper::englishToPersian((string) $stats['contacts_with_index']) }}</p>
            </div>
            <div>
                <span class="text-sm text-stone-500">کل پاسخ‌ها</span>
                <p class="text-xl font-bold m-0">{{ FormatHelper::englishToPersian((string) $stats['total_responses']) }}</p>
            </div>
            <div>
                <span class="text-sm text-stone-500">پاسخ‌های ۳۰ روز</span>
                <p class="text-xl font-bold m-0">{{ FormatHelper::englishToPersian((string) $stats['responses_last_30_days']) }}</p>
            </div>
            <div>
                <span class="text-sm text-stone-500">نظرسنجی‌های کامل</span>
                <p class="text-xl font-bold m-0">{{ FormatHelper::englishToPersian((string) $stats['submissions_count']) }}</p>
            </div>
        </div>

        @if(!empty($dimensionScores))
            @php
                $radarCx = 100;
                $radarCy = 100;
                $radarR = 82;
                $radarPoints = [];
                $radarLabels = [];
                foreach ($dimOrder as $i => $code) {
                    $score = isset($dimensionScores[$code]) ? (float) $dimensionScores[$code] : 0;
                    $angleDeg = -90 + $i * 72;
                    $rad = deg2rad($angleDeg);
                    $radarPoints[] = round($radarCx + ($score / 100) * $radarR * cos($rad), 2) . ',' . round($radarCy - ($score / 100) * $radarR * sin($rad), 2);
                    $labelR = $radarR + 14;
                    $radarLabels[] = [
                        'x' => round($radarCx + $labelR * cos($rad), 2),
                        'y' => round($radarCy - $labelR * sin($rad), 2),
                        'text' => $dimLabels[$code] ?? $code,
                    ];
                }
                $radarPath = implode(' ', $radarPoints);
            @endphp
            <div style="border-top: 1px solid var(--ds-border); padding-top: 1rem;">
                <p class="text-xs font-medium text-stone-500 mb-2">نمودار راداری ابعاد (میانگین شرکت)</p>
                <figure class="flex justify-center my-3" style="min-height: 220px;" aria-hidden="true">
                    <svg viewBox="0 0 200 200" role="img" aria-label="نمودار راداری ابعاد SERVQUAL" style="max-width: 220px; height: auto;" class="mx-auto">
                        <defs>
                            <linearGradient id="servqual-report-radar-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" style="stop-color:#059669;stop-opacity:0.35" />
                                <stop offset="100%" style="stop-color:#047857;stop-opacity:0.12" />
                            </linearGradient>
                        </defs>
                        @foreach([25, 50, 75, 100] as $pct)
                            <circle cx="{{ $radarCx }}" cy="{{ $radarCy }}" r="{{ ($pct/100) * $radarR }}" fill="none" stroke="#e7e5e4" stroke-width="0.5" />
                        @endforeach
                        @foreach($dimOrder as $i => $code)
                            @php
                                $angleDeg = -90 + $i * 72;
                                $rad = deg2rad($angleDeg);
                                $ax = $radarCx + $radarR * cos($rad);
                                $ay = $radarCy - $radarR * sin($rad);
                            @endphp
                            <line x1="{{ $radarCx }}" y1="{{ $radarCy }}" x2="{{ round($ax, 2) }}" y2="{{ round($ay, 2) }}" stroke="#d6d3d1" stroke-width="0.8" />
                        @endforeach
                        <polygon points="{{ $radarPath }}" fill="url(#servqual-report-radar-fill)" stroke="#047857" stroke-width="1.8" stroke-linejoin="round" />
                        @foreach($radarLabels as $l)
                            <text x="{{ $l['x'] }}" y="{{ $l['y'] }}" text-anchor="middle" dominant-baseline="middle" fill="#57534e" direction="rtl" class="servqual-radar-label">{{ $l['text'] }}</text>
                        @endforeach
                    </svg>
                </figure>
                <p class="text-xs font-medium text-stone-500 mb-2">امتیاز به تفکیک بعد (۰–۱۰۰)</p>
                <div class="flex flex-wrap gap-3">
                    @foreach($dimOrder as $code)
                        @php $s = $dimensionScores[$code] ?? 0; @endphp
                        <div style="padding: 0.35rem 0.6rem; border-radius: 0.5rem; background: #f5f5f4; font-size: 0.8125rem;">
                            <span class="font-medium text-stone-700">{{ $dimLabels[$code] ?? $code }}</span>
                            <span class="text-stone-600"> {{ round($s, 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        @else
            <p class="text-sm text-stone-500">هنوز پاسخی برای محاسبه میانگین ابعاد ثبت نشده. پس از تکمیل نظرسنجی‌های میکرو SERVQUAL، نمودار و امتیازها اینجا نمایش داده می‌شوند.</p>
        @endif
    </div>

    <p class="text-xs text-stone-400">داده‌ها بر اساس {{ $days }} روز اخیر. برای مشاهده جزئیات هر مخاطب به صفحهٔ مخاطب مراجعه کنید.</p>
</div>
@endsection
