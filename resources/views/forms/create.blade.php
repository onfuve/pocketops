@extends('layouts.app')

@section('title', 'فرم جدید — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #e0f2fe; color: #0369a1;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                فرم جدید
            </h1>
            <p class="ds-page-subtitle">عنوان و مدت زمان ویرایش بعد از ارسال را تنظیم کنید.</p>
        </div>
        <a href="{{ route('forms.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
    </div>

    <div class="ds-form-card">
        <form action="{{ route('forms.store') }}" method="post">
            @csrf
            <div style="margin-bottom: 1rem;">
                <label for="title" class="ds-label">عنوان فرم <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" class="ds-input" placeholder="مثلاً: ارسال رسید بانکی" required>
                @error('title')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div style="margin-bottom: 1rem;">
                <label for="edit_period_minutes" class="ds-label">مدت زمان مجاز ویرایش بعد از ارسال (دقیقه)</label>
                <input type="number" name="edit_period_minutes" id="edit_period_minutes" value="{{ old('edit_period_minutes', 15) }}" class="ds-input" min="0" max="10080" placeholder="15">
                <p style="font-size: 0.75rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">۰ = بعد از ارسال لینک غیرفعال می‌شود.</p>
                @error('edit_period_minutes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="ds-btn ds-btn-primary">ساخت فرم</button>
                <a href="{{ route('forms.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
