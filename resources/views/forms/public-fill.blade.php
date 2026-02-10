@extends('layouts.app-public')

@section('title', $form->title . ' — ' . config('app.name'))

@push('styles')
<style>
.form-public { max-width: 36rem; margin: 0 auto; padding: 0 1rem; }
.form-public h1 { font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--ds-text); }
.form-module { margin-bottom: 1.5rem; padding: 1.25rem; border-radius: var(--ds-radius-lg); border: 2px solid var(--ds-border); background: var(--ds-bg); }
.form-module label { display: block; font-weight: 500; margin-bottom: 0.5rem; font-size: 0.875rem; }
.form-module .help { font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem; }
.form-public .ds-btn { margin-top: 1rem; }
</style>
@endpush

@section('content')
<div class="form-public">
    <h1>{{ $form->title }}</h1>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: var(--ds-radius); background: #fef2f2; color: #b91c1c; font-size: 0.875rem;">{{ session('error') }}</div>
    @endif

    <form action="{{ route('forms.public.submit', $link->code) }}" method="post" enctype="multipart/form-data">
        @csrf
        @php $data = $submission->data ?? []; @endphp

        @foreach($form->modules as $module)
            @php $key = 'm' . $module->id; $value = $data[$key] ?? null; @endphp
            <div class="form-module">
                @if($module->type === 'custom_text')
                    <div style="white-space: pre-wrap; color: var(--ds-text-muted); font-size: 0.9375rem;">{!! nl2br(e($module->getConfig('content', ''))) !!}</div>
                @elseif($module->type === 'file_upload')
                    <label for="data_{{ $key }}">{{ $module->getConfig('label', 'فایل') }} @if($module->getConfig('required'))<span style="color: #b91c1c;">*</span>@endif</label>
                    <input type="file" name="data[{{ $key }}]" id="data_{{ $key }}" class="ds-input" accept="{{ $module->getConfig('accept', 'image/*,.pdf') }}">
                    @if($module->getConfig('help'))
                        <p class="help">{{ $module->getConfig('help') }}</p>
                    @endif
                    @if(is_array($value) && !empty($value['attachment_id']))
                        <p class="help" style="margin-top: 0.5rem;">فایل قبلی آپلود شده. با انتخاب فایل جدید جایگزین می‌شود.</p>
                    @endif
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
                @elseif($module->type === 'consent')
                    @php $items = $module->getConfig('items', [['text' => 'قوانین را می‌پذیرم', 'required' => true]]); @endphp
                    @foreach($items as $i => $item)
                        <label style="display: flex; align-items: flex-start; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="data[{{ $key }}][{{ $i }}]" value="1" {{ is_array($value) && !empty($value[$i]) ? 'checked' : '' }} style="margin-top: 0.25rem;">
                            <span>{{ $item['text'] ?? 'تأیید می‌کنم' }} @if(!empty($item['required']))<span style="color: #b91c1c;">*</span>@endif</span>
                        </label>
                    @endforeach
                @elseif($module->type === 'survey')
                    @php $questions = $module->getConfig('questions', [['id' => 'q1', 'text' => 'نظر شما؟', 'type' => 'text']]); @endphp
                    @foreach($questions as $q)
                        <label>{{ $q['text'] ?? 'سؤال' }}</label>
                        @if(($q['type'] ?? 'text') === 'nps')
                            <input type="number" name="data[{{ $key }}][{{ $q['id'] }}]" value="{{ is_array($value) ? ($value[$q['id']] ?? '') : '' }}" class="ds-input" min="0" max="10" placeholder="۰ تا ۱۰">
                        @else
                            <input type="text" name="data[{{ $key }}][{{ $q['id'] }}]" value="{{ is_array($value) ? ($value[$q['id']] ?? '') : '' }}" class="ds-input">
                        @endif
                    @endforeach
                @else
                    <label>{{ $module->getConfig('label', 'مقدار') }}</label>
                    <input type="text" name="data[{{ $key }}]" value="{{ is_scalar($value) ? $value : '' }}" class="ds-input">
                @endif
            </div>
        @endforeach

        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem;">
            <button type="submit" name="final" value="0" class="ds-btn ds-btn-outline">ذخیره پیش‌نویس</button>
            <button type="submit" name="final" value="1" class="ds-btn ds-btn-primary">ارسال نهایی</button>
        </div>
    </form>
</div>
@endsection
