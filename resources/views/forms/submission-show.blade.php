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
                <div style="font-weight: 600; margin-bottom: 0.5rem; font-size: 0.9375rem; color: var(--ds-text);">{{ $module->getConfig('label') ?: (\App\Models\FormModule::typeLabels()[$module->type] ?? $module->type) }}</div>
                @if($module->type === 'custom_text')
                    <div style="white-space: pre-wrap; color: var(--ds-text-subtle); font-size: 0.875rem;">{!! nl2br(e($module->getConfig('content', ''))) !!}</div>
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
                    @if(is_array($value) && array_filter($value))
                        <div style="font-size: 0.875rem; line-height: 1.6;">
                            @foreach(['receiver_name' => 'نام گیرنده', 'phone' => 'تلفن', 'province' => 'استان', 'city' => 'شهر', 'postal_code' => 'کد پستی', 'address' => 'آدرس'] as $k => $label)
                                @if(!empty($value[$k]))
                                    <div><span style="color: var(--ds-text-subtle);">{{ $label }}:</span> {{ $value[$k] }}</div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <span style="color: var(--ds-text-subtle);">—</span>
                    @endif
                @elseif($module->type === 'consent')
                    @php $items = $module->getConfig('items', []); $items = array_values(array_filter($items, function ($i) { return trim($i['text'] ?? '') !== ''; })); @endphp
                    @if(!empty($items))
                        <div style="font-size: 0.875rem;">
                            @foreach($items as $i => $item)
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    @if(is_array($value) && !empty($value[$i]))
                                        <span style="color: #059669;">✓</span>
                                    @else
                                        <span style="color: var(--ds-text-subtle);">—</span>
                                    @endif
                                    <span>{{ $item['text'] ?? 'تأیید' }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="color: var(--ds-text-subtle);">—</span>
                    @endif
                @elseif($module->type === 'survey')
                    @php $questions = $module->getConfig('questions', []); $questions = array_values(array_filter($questions, function ($q) { return trim($q['text'] ?? '') !== ''; })); @endphp
                    @if(!empty($questions))
                        <div style="font-size: 0.875rem;">
                            @foreach($questions as $q)
                                @php $qId = $q['id'] ?? null; $ans = is_array($value) ? ($value[$qId] ?? '') : ''; @endphp
                                <div style="margin-bottom: 0.75rem;">
                                    <div style="color: var(--ds-text-subtle); margin-bottom: 0.15rem;">{{ $q['text'] ?? 'سؤال' }}</div>
                                    <div style="font-weight: 500;">
                                        @if(($q['type'] ?? '') === 'nps' && $ans !== '')
                                            {{ $ans }}/۱۰
                                        @else
                                            {{ $ans !== '' ? $ans : '—' }}
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <span style="color: var(--ds-text-subtle);">—</span>
                    @endif
                @elseif($module->type === 'custom_fields')
                    @if(is_scalar($value) && (string)$value !== '')
                        <div style="font-size: 0.875rem;">{{ $value }}</div>
                    @else
                        <span style="color: var(--ds-text-subtle);">—</span>
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
