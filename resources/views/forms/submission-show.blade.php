@extends('layouts.app')

@section('title', 'ارسال فرم — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #e0f2fe; color: #0369a1;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                {{ $form->title }} — ارسال #{{ $submission->id }}
            </h1>
            <p class="ds-page-subtitle">
                {{ $submission->submitted_at ? $submission->submitted_at->format('Y/m/d H:i') : 'پیش‌نویس' }}
                @if($submission->contact)
                    · {{ $submission->contact->name }}
                @endif
            </p>
        </div>
        <a href="{{ route('forms.inbox') }}" class="ds-btn ds-btn-outline">صندوق ورودی</a>
    </div>

    <div class="ds-form-card">
        @php $data = $submission->data ?? []; @endphp
        @foreach($form->modules as $module)
            @php $key = 'm' . $module->id; $value = $data[$key] ?? null; @endphp
            <div style="margin-bottom: 1.5rem; padding-bottom: 1.5rem; border-bottom: 1px solid var(--ds-border);">
                <div style="font-weight: 600; margin-bottom: 0.5rem;">{{ $module->getConfig('label') ?: (\App\Models\FormModule::typeLabels()[$module->type] ?? $module->type) }}</div>
                @if($module->type === 'custom_text')
                    <div style="white-space: pre-wrap; color: var(--ds-text-muted);">{!! nl2br(e($module->getConfig('content', ''))) !!}</div>
                @elseif($module->type === 'file_upload')
                    @if(is_array($value) && !empty($value['attachment_id']))
                        @php $att = $submission->attachments->firstWhere('id', $value['attachment_id']); @endphp
                        @if($att)
                            <a href="{{ $att->url() }}" target="_blank" class="ds-btn ds-btn-ghost" style="display: inline-flex; align-items: center; gap: 0.5rem;">@include('components._icons', ['name' => 'download', 'class' => 'w-4 h-4']) {{ $att->original_name }}</a>
                        @else
                            <span style="color: var(--ds-text-subtle);">—</span>
                        @endif
                    @else
                        <span style="color: var(--ds-text-subtle);">آپلود نشده</span>
                    @endif
                @elseif($module->type === 'postal_address')
                    @if(is_array($value))
                        <div style="font-size: 0.875rem;">{{ implode(' — ', array_filter($value)) ?: '—' }}</div>
                    @else
                        <span style="color: var(--ds-text-subtle);">—</span>
                    @endif
                @elseif($module->type === 'consent' || $module->type === 'survey' || $module->type === 'custom_fields')
                    @if(is_array($value))
                        <pre style="font-size: 0.8125rem; white-space: pre-wrap; margin: 0;">{{ json_encode($value, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) }}</pre>
                    @else
                        <span style="color: var(--ds-text-subtle);">{{ $value ?? '—' }}</span>
                    @endif
                @else
                    <span style="color: var(--ds-text-subtle);">{{ is_scalar($value) ? $value : (is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : '—') }}</span>
                @endif
            </div>
        @endforeach
    </div>

    @if($submission->contact || $submission->lead || $submission->task)
        <div class="ds-form-card" style="margin-top: 1rem;">
            <h2 class="ds-form-card-title">مرتبط با</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @if($submission->contact)
                    <a href="{{ route('contacts.show', $submission->contact) }}" class="ds-btn ds-btn-ghost">مخاطب: {{ $submission->contact->name }}</a>
                @endif
                @if($submission->lead)
                    <a href="{{ route('leads.show', $submission->lead) }}" class="ds-btn ds-btn-ghost">سرنخ: {{ $submission->lead->name }}</a>
                @endif
                @if($submission->task)
                    <a href="{{ route('tasks.show', $submission->task) }}" class="ds-btn ds-btn-ghost">وظیفه</a>
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
