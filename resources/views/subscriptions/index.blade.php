@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'اشتراک‌ها — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
.subs-search-wrap { position: relative; flex: 1; min-width: 0; }
.subs-search-wrap .search-icon { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--ds-text-faint); }
.subs-list { display: flex; flex-direction: column; gap: 0.5rem; }
.subs-list .sub-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1rem; transition: border-color 0.2s, box-shadow 0.2s; }
.subs-list .sub-card:hover { border-color: var(--ds-border-hover); box-shadow: var(--ds-shadow-hover); }
.subs-list .sub-card-inner { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; }
.subs-list .sub-card-body { min-width: 0; flex: 1; }
.subs-list .sub-card-meta { font-size: 0.875rem; color: var(--ds-text-muted); margin-top: 0.25rem; }
.subs-list .sub-card-actions { flex-shrink: 0; display: flex; flex-wrap: wrap; gap: 0.5rem; }
.subs-list .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: var(--ds-radius-sm); font-size: 0.75rem; font-weight: 500; }
.subs-list .badge-overdue { background: #fef2f2; color: #b91c1c; }
.subs-list .badge-pending { background: #fef3c7; color: #92400e; }
.subs-list .badge-paid { background: #d1fae5; color: #047857; }
.subs-filters { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'calendar', 'class' => 'w-5 h-5'])
                </span>
                اشتراک‌ها
            </h1>
            <p class="ds-page-subtitle">مدیریت سرویس‌های recurring — هاست، VPN، لایسنس، دامنه و غیره. یادآوری انقضا در تقویم نمایش داده می‌شود.</p>
        </div>
        @if(auth()->user()->canModule('subscriptions', \App\Models\User::ABILITY_CREATE))
        <a href="{{ route('subscriptions.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            اشتراک جدید
        </a>
        @endif
    </div>

    <form action="{{ route('subscriptions.index') }}" method="get" class="ds-search-row" style="margin-bottom: 1rem;">
        <input type="hidden" name="per_page" value="{{ $perPage ?? 20 }}">
        <div class="subs-search-wrap" style="flex: 1; min-width: 0;">
            <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="جستجو سرویس، توضیح یا نام مشتری…" class="ds-input" style="width: 100%;">
            <span class="search-icon">@include('components._icons', ['name' => 'search', 'class' => 'w-5 h-5'])</span>
        </div>
        <select name="category" class="ds-select" style="width: auto;">
            <option value="">همه دسته‌ها</option>
            @foreach (config('subscription.categories', []) as $key => $label)
                <option value="{{ $key }}" {{ ($category ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <select name="status" class="ds-select" style="width: auto;">
            <option value="">همه وضعیت‌ها</option>
            @foreach (config('subscription.payment_statuses', []) as $key => $label)
                <option value="{{ $key }}" {{ ($status ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
        <button type="submit" class="ds-btn ds-btn-secondary">
            @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
            جستجو
        </button>
    </form>

    @if (!empty($q) || !empty($category) || !empty($status))
        <div class="subs-filters">
            <span style="font-size: 0.875rem; color: var(--ds-text-muted);">
                @if ($subscriptions->total() > 0)
                    {{ FormatHelper::englishToPersian((string) $subscriptions->total()) }} نتیجه
                @else
                    نتیجه‌ای یافت نشد.
                @endif
            </span>
            <a href="{{ route('subscriptions.index') }}" style="color: var(--ds-primary); font-weight: 500;">نمایش همه</a>
        </div>
    @endif

    @if ($subscriptions->isEmpty())
        <div class="ds-empty">
            @if (!empty($q) || !empty($category) || !empty($status))
                <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-muted);">نتیجه‌ای یافت نشد.</p>
                <a href="{{ route('subscriptions.index') }}" class="ds-btn ds-btn-outline">نمایش همه اشتراک‌ها</a>
            @else
                <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-muted);">هنوز اشتراکی ثبت نشده است.</p>
                @if(auth()->user()->canModule('subscriptions', \App\Models\User::ABILITY_CREATE))
                <a href="{{ route('subscriptions.create') }}" class="ds-btn ds-btn-primary">ثبت اولین اشتراک</a>
                @endif
            @endif
        </div>
    @else
        <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--ds-text-subtle);">
            نمایش {{ FormatHelper::englishToPersian((string) $subscriptions->firstItem()) }}–{{ FormatHelper::englishToPersian((string) $subscriptions->lastItem()) }} از {{ FormatHelper::englishToPersian((string) $subscriptions->total()) }} اشتراک
        </p>
        <div class="subs-list">
            @foreach ($subscriptions as $sub)
                @php
                    $badgeClass = 'badge-paid';
                    if ($sub->payment_status === 'overdue' || $sub->isOverdue()) $badgeClass = 'badge-overdue';
                    elseif ($sub->payment_status === 'pending') $badgeClass = 'badge-pending';
                    $statusLabel = config('subscription.payment_statuses')[$sub->payment_status] ?? $sub->payment_status;
                @endphp
                <div class="sub-card">
                    <div class="sub-card-inner">
                        <div class="sub-card-body">
                            <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="{{ route('subscriptions.show', $sub) }}" class="font-semibold" style="color: var(--ds-text); text-decoration: none;">{{ $sub->service_name }}</a>
                                <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                                @if ($sub->isOverdue())
                                    <span class="badge badge-overdue">معوق</span>
                                @endif
                            </div>
                            <div class="sub-card-meta">
                                {{ $sub->contact?->name ?? '—' }}
                                · {{ config('subscription.categories')[$sub->category] ?? $sub->category }}
                                · انقضا: {{ $sub->expiry_date ? FormatHelper::shamsi($sub->expiry_date) : '—' }}
                                · {{ FormatHelper::rial($sub->price) }}
                            </div>
                        </div>
                        <div class="sub-card-actions">
                            <a href="{{ route('subscriptions.show', $sub) }}" class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem;">مشاهده</a>
                            @if(auth()->user()->canModule('subscriptions', \App\Models\User::ABILITY_EDIT))
                            <a href="{{ route('subscriptions.edit', $sub) }}" class="ds-btn ds-btn-secondary" style="padding: 0.375rem 0.75rem;">ویرایش</a>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 1.5rem;">
            {{ $subscriptions->links() }}
        </div>
    @endif
</div>
@endsection
