@php use App\Helpers\FormatHelper; use App\Models\Lead; use App\Models\Reminder; use App\Models\Task; use App\Models\Invoice; @endphp
@extends('layouts.app')

@section('title', 'داشبورد — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: linear-gradient(135deg, #d1fae5 0%, #e0f2fe 100%); color: #047857; border-color: #a7f3d0; }
.dash-stat-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1rem; text-decoration: none; color: inherit; display: block; transition: all 0.2s; box-shadow: var(--ds-shadow); }
.dash-stat-card:hover { border-color: var(--ds-border-hover); box-shadow: var(--ds-shadow-hover); transform: translateY(-2px); }
.dash-stat-card .stat-value { font-size: 1.5rem; font-weight: 700; color: var(--ds-text); }
.dash-stat-card .stat-label { font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem; }
.dash-stat-card.stat-alert { border-color: var(--ds-danger-border); background: var(--ds-danger-bg); }
.dash-stat-card.stat-alert .stat-value { color: var(--ds-danger); }
.dash-section-title { display: flex; align-items: center; gap: 0.5rem; font-size: 0.9375rem; font-weight: 600; color: var(--ds-text); margin-bottom: 0.75rem; }
.dash-item { display: flex; align-items: center; gap: 0.75rem; padding: 0.625rem 0; border-bottom: 1px solid var(--ds-bg-subtle); text-decoration: none; color: inherit; transition: background 0.15s; }
.dash-item:last-child { border-bottom: none; }
.dash-item:hover { background: var(--ds-bg-muted); }
.dash-item .item-dot { width: 0.5rem; height: 0.5rem; border-radius: 50%; flex-shrink: 0; }
.dash-empty { padding: 1.5rem; text-align: center; font-size: 0.875rem; color: var(--ds-text-faint); }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                </span>
                داشبورد
            </h1>
            <p class="ds-page-subtitle">تقویم، وظایف و مواردی که نیاز به توجه شما دارند.</p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="{{ route('calendar.index') }}" class="ds-btn ds-btn-secondary">
                @include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4'])
                تقویم
            </a>
            <a href="{{ route('tasks.create') }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                وظیفه جدید
            </a>
        </div>
    </div>

    {{-- Stat cards --}}
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 0.75rem; margin-bottom: 1.5rem;">
        <a href="{{ route('contacts.index') }}" class="dash-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $contactsCount) }}</div>
            <div class="stat-label">مخاطبین</div>
        </a>
        <a href="{{ route('leads.index') }}" class="dash-stat-card">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $leadsCount) }}</div>
            <div class="stat-label">سرنخ‌ها</div>
        </a>
        <a href="{{ route('tasks.index') }}" class="dash-stat-card {{ $tasksOverdue > 0 ? 'stat-alert' : '' }}">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $tasksCount) }}</div>
            <div class="stat-label">وظایف باز {{ $tasksOverdue > 0 ? '· ' . FormatHelper::englishToPersian((string) $tasksOverdue) . ' معوق' : '' }}</div>
        </a>
        <a href="{{ route('invoices.index') }}" class="dash-stat-card {{ $invoicesOverdue > 0 ? 'stat-alert' : '' }}">
            <div class="stat-value">{{ FormatHelper::englishToPersian((string) $invoicesUnpaid->count()) }}</div>
            <div class="stat-label">فاکتورهای پرداخت‌نشده {{ $invoicesOverdue > 0 ? '· ' . FormatHelper::englishToPersian((string) $invoicesOverdue) . ' سررسید گذشته' : '' }}</div>
        </a>
    </div>

    <div style="display: grid; gap: 1.5rem; grid-template-columns: 1fr;">
        @if ($todayReminders->isNotEmpty())
        <div class="ds-form-card">
            <h2 class="dash-section-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background: #d1fae5; color: #047857;">@include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4'])</span>
                امروز — {{ FormatHelper::shamsi(now()) }}
            </h2>
            <div style="display: flex; flex-direction: column;">
                @foreach ($todayReminders as $r)
                    <a href="{{ $r->remindable_type === Lead::class && $r->remindable_id ? route('leads.show', $r->remindable_id) : route('calendar.index') }}" class="dash-item">
                        <span class="item-dot" style="background: {{ $r->type === Reminder::TYPE_LEAD_TASK ? '#f59e0b' : '#059669' }};"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $r->title }}</span>
                            @if ($r->due_time)
                                <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ $r->due_time }}</span>
                            @endif
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @if ($upcomingReminders->isNotEmpty() || $upcomingInvoiceDues->isNotEmpty())
        <div class="ds-form-card">
            <h2 class="dash-section-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background: #e0f2fe; color: #0369a1;">@include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4'])</span>
                پیش‌رو (۷ روز آینده)
            </h2>
            <div style="display: flex; flex-direction: column;">
                @foreach ($upcomingReminders as $r)
                    <a href="{{ $r->remindable_type === Lead::class && $r->remindable_id ? route('leads.show', $r->remindable_id) : route('calendar.index') }}" class="dash-item">
                        <span class="item-dot" style="background: #059669;"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $r->title }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ FormatHelper::shamsi($r->due_date) }}{{ $r->due_time ? ' · ' . $r->due_time : '' }}</span>
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
                @foreach ($upcomingInvoiceDues as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="dash-item">
                        <span class="item-dot" style="background: {{ $inv->type === Invoice::TYPE_SELL ? '#047857' : '#0369a1' }};"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $inv->type === Invoice::TYPE_SELL ? 'سررسید فاکتور' : 'سررسید رسید' }} — {{ $inv->contact->name }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ FormatHelper::shamsi($inv->due_date) }} · {{ FormatHelper::rial((int) $inv->total) }}</span>
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
        </div>
        @endif

        @if ($tasksNeedingAttention->isNotEmpty())
        <div class="ds-form-card">
            <h2 class="dash-section-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background: #dbeafe; color: #0369a1;">@include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])</span>
                وظایف نیازمند توجه
            </h2>
            <div style="display: flex; flex-direction: column;">
                @foreach ($tasksNeedingAttention as $t)
                    <a href="{{ route('tasks.show', $t) }}" class="dash-item">
                        <span class="item-dot" style="background: {{ $t->due_date && $t->due_date->format('Y-m-d') < now()->format('Y-m-d') ? '#b91c1c' : Task::statusColors()[$t->status] }};"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $t->title }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ $t->taskableLabel() }} · {{ FormatHelper::shamsi($t->due_date ?? $t->created_at) }}{{ $t->due_date && $t->due_date->format('Y-m-d') < now()->format('Y-m-d') ? ' · معوق' : '' }}</span>
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
            <a href="{{ route('tasks.index') }}" class="ds-btn ds-btn-ghost" style="margin-top: 0.75rem;">همه وظایف</a>
        </div>
        @endif

        @if ($leadsNeedingAttention->isNotEmpty())
        <div class="ds-form-card">
            <h2 class="dash-section-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background: #fef3c7; color: #b45309;">@include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4'])</span>
                سرنخ‌های فعال
            </h2>
            <div style="display: flex; flex-direction: column;">
                @foreach ($leadsNeedingAttention as $l)
                    <a href="{{ route('leads.show', $l) }}" class="dash-item">
                        <span class="item-dot" style="background: {{ Lead::statusTextColor($l->status) }};"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $l->name }}{{ $l->company ? ' · ' . $l->company : '' }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ $l->status_label }}</span>
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
            <a href="{{ route('leads.index') }}" class="ds-btn ds-btn-ghost" style="margin-top: 0.75rem;">همه سرنخ‌ها</a>
        </div>
        @endif

        @if ($overdueInvoices->isNotEmpty())
        <div class="ds-form-card" style="border-color: var(--ds-danger-border); background: var(--ds-danger-bg);">
            <h2 class="dash-section-title">
                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; border-radius: 0.5rem; background: #fecaca; color: #b91c1c;">@include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])</span>
                فاکتورهای سررسید گذشته
            </h2>
            <div style="display: flex; flex-direction: column;">
                @foreach ($overdueInvoices as $inv)
                    <a href="{{ route('invoices.show', $inv) }}" class="dash-item">
                        <span class="item-dot" style="background: #b91c1c;"></span>
                        <div style="flex: 1; min-width: 0;">
                            <span style="font-weight: 500; color: var(--ds-text);">{{ $inv->contact->name }} — {{ FormatHelper::rial((int) $inv->total) }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">{{ FormatHelper::shamsi($inv->due_date) }}</span>
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
            <a href="{{ route('invoices.index') }}" class="ds-btn ds-btn-danger" style="margin-top: 0.75rem;">ثبت پرداخت</a>
        </div>
        @endif
    </div>

    @if ($todayReminders->isEmpty() && $upcomingReminders->isEmpty() && $upcomingInvoiceDues->isEmpty() && $tasksNeedingAttention->isEmpty() && $leadsNeedingAttention->isEmpty() && $overdueInvoices->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 500; color: var(--ds-text-subtle);">هیچ موردی نیاز به توجه ندارد.</p>
            <p style="margin: 0; font-size: 0.875rem;">از <a href="{{ route('calendar.index') }}" style="color: var(--ds-primary); font-weight: 500;">تقویم</a> یا <a href="{{ route('tasks.index') }}" style="color: var(--ds-primary); font-weight: 500;">وظایف</a> شروع کنید.</p>
        </div>
    @endif
</div>
@endsection
