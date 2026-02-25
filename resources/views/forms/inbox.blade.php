@extends('layouts.app')

@section('title', 'صندوق ورودی فرم‌ها — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #e0f2fe; color: #0369a1;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                صندوق ورودی فرم‌ها
            </h1>
            <p class="ds-page-subtitle">همهٔ ارسال‌های فرم‌های شما.</p>
        </div>
        <a href="{{ route('forms.index') }}" class="ds-btn ds-btn-outline">لیست فرم‌ها</a>
    </div>

    @if ($submissions->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0; color: var(--ds-text-subtle);">هنوز ارسالی ثبت نشده.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($submissions as $sub)
                @php $data = $sub->data ?? []; $invId = $data['invoice_id'] ?? null; $invNum = $data['invoice_number'] ?? $invId; @endphp
                <a href="{{ route('forms.submissions.show', $sub) }}" class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                    <div>
                        <span style="font-weight: 600;">{{ $sub->form->title }}</span>
                        <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">— ارسال #{{ $sub->id }}</span>
                        @if($sub->contact)
                            <span style="font-size: 0.875rem;">· {{ $sub->contact->name }}</span>
                        @endif
                        @if($invId)
                            <span style="font-size: 0.8125rem; color: var(--ds-primary); margin-right: 0.35rem;">· فاکتور #{{ $invNum }}</span>
                        @endif
                        <div style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">
                            {{ $sub->submitted_at ? $sub->submitted_at->format('Y/m/d H:i') : 'پیش‌نویس' }}
                        </div>
                    </div>
                    @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                </a>
            @endforeach
        </div>
        <div style="margin-top: 1.5rem;">{{ $submissions->links() }}</div>
    @endif
</div>
@endsection
