@php
    use App\Helpers\FormatHelper;
    $categories = config('subscription.categories', []);
    $billingCycles = config('subscription.billing_cycles', []);
    $paymentStatuses = config('subscription.payment_statuses', []);
@endphp
@extends('layouts.app')

@section('title', $subscription->service_name . ' — اشتراک — ' . config('app.name'))

@push('styles')
<style>
.sub-show .ds-form-card { margin-bottom: 1.5rem; }
.sub-show .ds-form-card-title { margin-bottom: 0.75rem; }
.sub-show .info-row { display: flex; flex-wrap: wrap; gap: 0.5rem 1.5rem; margin-bottom: 0.5rem; font-size: 0.9375rem; }
.sub-show .info-label { color: var(--ds-text-muted); min-width: 7rem; }
.sub-show .info-value { color: var(--ds-text); }
.sub-show .badge { display: inline-block; padding: 0.25rem 0.5rem; border-radius: var(--ds-radius-sm); font-size: 0.75rem; font-weight: 500; }
.sub-show .badge-overdue { background: #fef2f2; color: #b91c1c; }
.sub-show .badge-pending { background: #fef3c7; color: #92400e; }
.sub-show .badge-paid { background: #d1fae5; color: #047857; }
.sub-show .reminder-list { list-style: none; padding: 0; margin: 0; }
.sub-show .reminder-list li { padding: 0.5rem 0; border-bottom: 1px solid var(--ds-border); font-size: 0.875rem; }
.sub-show .reminder-list li:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="ds-page sub-show">
    @if (session('success'))
        <div class="mb-4 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="ds-page-header" style="margin-bottom: 1.5rem;">
        <div>
            <h1 class="ds-page-title" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                <span class="ds-page-title-icon" style="background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe;">
                    @include('components._icons', ['name' => 'calendar', 'class' => 'w-5 h-5'])
                </span>
                {{ $subscription->service_name }}
                @php
                    $badgeClass = 'badge-paid';
                    if ($subscription->payment_status === 'overdue' || $subscription->isOverdue()) $badgeClass = 'badge-overdue';
                    elseif ($subscription->payment_status === 'pending') $badgeClass = 'badge-pending';
                @endphp
                <span class="badge {{ $badgeClass }}">{{ $paymentStatuses[$subscription->payment_status] ?? $subscription->payment_status }}</span>
                @if ($subscription->isOverdue())
                    <span class="badge badge-overdue">معوق</span>
                @endif
            </h1>
            <p class="ds-page-subtitle" style="margin-top: 0.25rem;">
                {{ $subscription->contact?->name ?? '—' }}
                · {{ $categories[$subscription->category] ?? $subscription->category }}
                · انقضا: {{ $subscription->expiry_date ? FormatHelper::shamsi($subscription->expiry_date) : '—' }}
            </p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            @if(auth()->user()->canModule('subscriptions', \App\Models\User::ABILITY_EDIT))
            <a href="{{ route('subscriptions.edit', $subscription) }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                ویرایش
            </a>
            @endif
            @if(auth()->user()->canDeleteSubscription())
            <form action="{{ route('subscriptions.destroy', $subscription) }}" method="post" onsubmit="return confirm('این اشتراک حذف شود؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ds-btn ds-btn-danger">
                    @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                    حذف
                </button>
            </form>
            @endif
            <a href="{{ route('subscriptions.index') }}" class="ds-btn ds-btn-outline">لیست اشتراک‌ها</a>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اطلاعات سرویس</h2>
        <div class="info-row"><span class="info-label">مشتری</span><span class="info-value">@if($subscription->contact)<a href="{{ route('contacts.show', $subscription->contact) }}" style="color: var(--ds-primary); text-decoration: none;">{{ $subscription->contact->name }}</a>@else — @endif</span></div>
        <div class="info-row"><span class="info-label">دسته‌بندی</span><span class="info-value">{{ $categories[$subscription->category] ?? $subscription->category }}</span></div>
        <div class="info-row"><span class="info-label">دوره پرداخت</span><span class="info-value">{{ $billingCycles[$subscription->billing_cycle] ?? $subscription->billing_cycle }}</span></div>
        @if ($subscription->description)
        <div class="info-row"><span class="info-label">توضیحات</span><span class="info-value">{{ $subscription->description }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">تاریخ شروع</span><span class="info-value">{{ $subscription->start_date ? FormatHelper::shamsi($subscription->start_date) : '—' }}</span></div>
        <div class="info-row"><span class="info-label">تاریخ انقضا</span><span class="info-value">{{ $subscription->expiry_date ? FormatHelper::shamsi($subscription->expiry_date) : '—' }}</span></div>
        <div class="info-row"><span class="info-label">مبلغ</span><span class="info-value">{{ FormatHelper::rial($subscription->price) }}</span></div>
        @if ($subscription->cost !== null)
        <div class="info-row"><span class="info-label">هزینه</span><span class="info-value">{{ FormatHelper::rial($subscription->cost) }}</span></div>
        <div class="info-row"><span class="info-label">سود</span><span class="info-value">{{ $subscription->profit !== null ? FormatHelper::rial($subscription->profit) : '—' }}</span></div>
        @endif
        <div class="info-row"><span class="info-label">وضعیت پرداخت</span><span class="info-value">{{ $paymentStatuses[$subscription->payment_status] ?? $subscription->payment_status }}</span></div>
        <div class="info-row"><span class="info-label">تمدید خودکار</span><span class="info-value">{{ $subscription->auto_renewal ? 'بله' : 'خیر' }}</span></div>
        @if ($subscription->supplier)
        <div class="info-row"><span class="info-label">تأمین‌کننده</span><span class="info-value">{{ $subscription->supplier }}</span></div>
        @endif
        @if ($subscription->assignedTo)
        <div class="info-row"><span class="info-label">مسئول پیگیری</span><span class="info-value">{{ $subscription->assignedTo->name }}</span></div>
        @endif
        @if ($subscription->reminder_days_before)
        <div class="info-row"><span class="info-label">یادآوری</span><span class="info-value">{{ $subscription->reminder_days_before }} روز قبل از انقضا</span></div>
        @endif
        @if ($subscription->notes)
        <div class="info-row"><span class="info-label">یادداشت</span><span class="info-value">{{ $subscription->notes }}</span></div>
        @endif
        @if ($subscription->account_credentials)
        <div class="info-row"><span class="info-label">اطلاعات ورود</span><span class="info-value" style="font-family: monospace;">••••••••</span></div>
        @endif
    </div>

    @if ($subscription->reminders->isNotEmpty())
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">یادآوری‌های تقویم</h2>
        <ul class="reminder-list">
            @foreach ($subscription->reminders as $rem)
                <li>
                    {{ $rem->title }}
                    — {{ $rem->due_date ? FormatHelper::shamsi($rem->due_date) : '' }}
                    @if ($rem->isDone())
                        <span style="color: var(--ds-text-muted);">(انجام شده)</span>
                    @endif
                </li>
            @endforeach
        </ul>
        <p style="margin: 0.5rem 0 0 0; font-size: 0.8125rem; color: var(--ds-text-muted);">این رویدادها در <a href="{{ route('calendar.index') }}" style="color: var(--ds-primary);">تقویم</a> نمایش داده می‌شوند.</p>
    </div>
    @endif
</div>
@endsection
