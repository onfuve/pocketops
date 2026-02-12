@extends('layouts.app-public')

@section('title', $form->title . ' — ' . config('app.name'))

@push('styles')
<style>
.form-public { max-width: 40rem; margin: 0 auto; padding: 0 1.25rem; }
.form-public h1 { font-size: 1.5rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--ds-text); letter-spacing: -0.02em; }
.form-module { margin-bottom: 1.25rem; padding: 1.25rem 1.5rem; border-radius: 1rem; border: 2px solid var(--ds-border); background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.05); transition: box-shadow 0.15s; }
.form-module:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.08); }
.form-module.form-module-text { border: none; background: transparent; box-shadow: none; padding: 0.5rem 0; }
.form-module.form-module-text.form-module-heading { padding: 0.75rem 0; }
.form-module.form-module-heading .text-content { font-size: 1.125rem; font-weight: 600; color: var(--ds-text); }
.form-module label { display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--ds-text); }
.form-module .help { font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.35rem; line-height: 1.4; }
.form-module .field-error { font-size: 0.8125rem; color: #b91c1c; margin-top: 0.35rem; }
.form-module .ds-input, .form-module .ds-textarea { border-radius: 0.5rem; }
.form-module.is-invalid .ds-input, .form-module.is-invalid .ds-textarea { border-color: #b91c1c; }
.form-public .form-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.75rem; padding-top: 1rem; border-top: 2px solid var(--ds-border); }
.form-public .form-actions .ds-btn { min-height: 44px; padding: 0.625rem 1.25rem; }
</style>
@endpush

@section('content')
<div class="form-public">
    <h1>{{ $form->title }}</h1>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #fef2f2; border: 2px solid #fecaca; color: #b91c1c; font-size: 0.875rem;">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #fef2f2; border: 2px solid #fecaca; color: #b91c1c; font-size: 0.875rem;">
            <strong>لطفاً موارد زیر را تکمیل کنید:</strong>
            <ul style="margin: 0.5rem 0 0 1rem; padding: 0;">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('forms.public.submit', $link->code) }}" method="post" enctype="multipart/form-data">
        @csrf
        @php $data = $data ?? ($submission->data ?? []); @endphp

        @foreach($form->modules as $module)
            @php
                $key = 'm' . $module->id;
                $value = $data[$key] ?? null;
                $fieldKey = 'data.' . $key;
                $isInvalid = $errors->has($fieldKey);
                $textStyle = $module->type === 'custom_text' ? ($module->getConfig('style', 'normal') ?: 'normal') : '';
            @endphp
            <div class="form-module {{ $module->type === 'custom_text' ? 'form-module-text' . ($textStyle === 'heading' ? ' form-module-heading' : '') : '' }} {{ $isInvalid ? 'is-invalid' : '' }}">
                @if($module->type === 'custom_text')
                    <div class="{{ $textStyle === 'heading' ? 'text-content' : '' }}" style="white-space: pre-wrap; {{ $textStyle === 'heading' ? '' : 'color: var(--ds-text-subtle); font-size: 0.9375rem;' }}">{!! nl2br(e($module->getConfig('content', ''))) !!}</div>
                @elseif($module->type === 'file_upload')
                    <label for="data_{{ $key }}">{{ $module->getConfig('label', 'فایل') }} @if($module->getConfig('required'))<span style="color: #b91c1c;">*</span>@endif</label>
                    <input type="file" name="data[{{ $key }}]" id="data_{{ $key }}" class="ds-input" accept="{{ $module->getConfig('accept', 'image/*,.pdf') }}">
                    @if($module->getConfig('help'))
                        <p class="help">{{ $module->getConfig('help') }}</p>
                    @endif
                    @if(is_array($value) && !empty($value['attachment_id']))
                        <p class="help" style="margin-top: 0.5rem;">فایل قبلی آپلود شده. با انتخاب فایل جدید جایگزین می‌شود.</p>
                    @endif
                @elseif($module->type === 'consent')
                    @php
                        $items = $module->getConfig('items', [['text' => 'قوانین را می‌پذیرم', 'required' => true]]);
                        $items = array_values(array_filter($items, function ($item) {
                            return trim($item['text'] ?? '') !== '';
                        }));
                        if (empty($items)) {
                            $items = [['text' => 'قوانین را می‌پذیرم', 'required' => true]];
                        }
                    @endphp
                    @foreach($items as $i => $item)
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="data[{{ $key }}][{{ $i }}]" value="1" {{ is_array($value) && !empty($value[$i]) ? 'checked' : '' }} style="margin-top: 0.25rem;">
                            <span>{{ $item['text'] ?? 'تأیید می‌کنم' }} @if(!empty($item['required']))<span style="color: #b91c1c;">*</span>@endif</span>
                        </label>
                    @endforeach
                @elseif($module->type === 'survey')
                    @php
                        $questions = $module->getConfig('questions', [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']]);
                        $questions = array_values(array_filter($questions, function ($q) {
                            return trim($q['text'] ?? '') !== '';
                        }));
                        if (empty($questions)) {
                            $questions = [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']];
                        }
                    @endphp
                    @foreach($questions as $q)
                        <label>{{ $q['text'] ?? 'سؤال' }}</label>
                        @if(($q['type'] ?? 'text') === 'nps')
                            <input type="number" name="data[{{ $key }}][{{ $q['id'] }}]" value="{{ is_array($value) ? ($value[$q['id']] ?? '') : '' }}" class="ds-input" min="0" max="10" placeholder="۰ تا ۱۰">
                        @else
                            <input type="text" name="data[{{ $key }}][{{ $q['id'] }}]" value="{{ is_array($value) ? ($value[$q['id']] ?? '') : '' }}" class="ds-input">
                        @endif
                    @endforeach
                @elseif($module->type === 'postal_address')
                    <label>{{ $module->getConfig('label', 'آدرس پستی') }}</label>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <input type="text" name="data[{{ $key }}][province]" value="{{ is_array($value) ? ($value['province'] ?? '') : '' }}" class="ds-input" placeholder="استان">
                        <input type="text" name="data[{{ $key }}][city]" value="{{ is_array($value) ? ($value['city'] ?? '') : '' }}" class="ds-input" placeholder="شهر">
                        <input type="text" name="data[{{ $key }}][postal_code]" value="{{ is_array($value) ? ($value['postal_code'] ?? '') : '' }}" class="ds-input" placeholder="کد پستی">
                        <input type="text" name="data[{{ $key }}][address]" value="{{ is_array($value) ? ($value['address'] ?? '') : '' }}" class="ds-input" placeholder="آدرس کامل">
                        <input type="text" name="data[{{ $key }}][receiver_name]" value="{{ is_array($value) ? ($value['receiver_name'] ?? '') : '' }}" class="ds-input" placeholder="نام گیرنده">
                        <input type="text" name="data[{{ $key }}][phone]" value="{{ is_array($value) ? ($value['phone'] ?? '') : '' }}" class="ds-input" placeholder="تلفن">
                    </div>
                    @if($module->getConfig('help'))
                        <p class="help">{{ $module->getConfig('help') }}</p>
                    @endif
                @elseif($module->type === 'custom_fields')
                    @php
                        $fieldType = $module->getConfig('type', 'text');
                        $placeholder = $module->getConfig('placeholder', '');
                        $fieldRequired = $module->getConfig('required', false);
                    @endphp
                    <label for="data_{{ $key }}">{{ $module->getConfig('label', 'مقدار') }} @if($fieldRequired)<span style="color: #b91c1c;">*</span>@endif</label>
                    @if($fieldType === 'textarea')
                        <textarea name="data[{{ $key }}]" id="data_{{ $key }}" rows="3" class="ds-textarea" placeholder="{{ $placeholder }}">{{ is_scalar($value) ? $value : '' }}</textarea>
                    @else
                        @php $htmlType = in_array($fieldType, ['email', 'number'], true) ? $fieldType : 'text'; @endphp
                        <input type="{{ $htmlType }}"
                               name="data[{{ $key }}]"
                               id="data_{{ $key }}"
                               value="{{ is_scalar($value) ? $value : '' }}"
                               class="ds-input"
                               placeholder="{{ $placeholder }}">
                    @endif
                    @if($module->getConfig('help'))
                        <p class="help">{{ $module->getConfig('help') }}</p>
                    @endif
                @else
                    <label>{{ $module->getConfig('label', 'مقدار') }}</label>
                    <input type="text" name="data[{{ $key }}]" value="{{ is_scalar($value) ? $value : '' }}" class="ds-input">
                @endif
                @error($fieldKey)
                    <p class="field-error">{{ $message }}</p>
                @enderror
            </div>
        @endforeach

        <div class="form-actions">
            <button type="submit" name="final" value="0" class="ds-btn ds-btn-outline">ذخیره پیش‌نویس</button>
            <button type="submit" name="final" value="1" class="ds-btn ds-btn-primary">ارسال نهایی</button>
        </div>
    </form>
</div>
@endsection
