@extends('layouts.app')

@section('title', 'لیست قیمت جدید — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'plus', 'class' => 'w-5 h-5'])
                </span>
                لیست قیمت جدید
            </h1>
            <p class="ds-page-subtitle">اطلاعات اولیه را وارد کنید. پس از ذخیره، بخش‌ها و آیتم‌ها را اضافه می‌کنید.</p>
        </div>
        <a href="{{ route('price-lists.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>

    <form action="{{ route('price-lists.store') }}" method="post" class="ds-form-card" style="max-width: 32rem;">
        @csrf
        <h2 class="ds-form-card-title">اطلاعات اصلی</h2>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label for="name" class="ds-label">نام لیست قیمت <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $priceList->name ?? '') }}" required class="ds-input" placeholder="مثلاً لیست قیمت باتری همکار">
                @error('name')
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="title_text" class="ds-label">عنوان نمایشی (اختیاری)</label>
                <input type="text" name="title_text" id="title_text" value="{{ old('title_text', $priceList->title_text ?? '') }}" class="ds-input" placeholder="عنوانی که در صفحه عمومی نمایش داده می‌شود">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_prices" value="1" {{ old('show_prices', $priceList->show_prices ?? true) ? 'checked' : '' }}>
                    نمایش قیمت‌ها
                </label>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_photos" value="1" {{ old('show_photos', $priceList->show_photos ?? false) ? 'checked' : '' }}>
                    نمایش عکس کالاها
                </label>
            </div>
            <div>
                <label for="template" class="ds-label">قالب</label>
                <select name="template" id="template" class="ds-select">
                    <option value="simple" {{ old('template', $priceList->template ?? 'simple') === 'simple' ? 'selected' : '' }}>ساده (متنی)</option>
                    <option value="with_photos" {{ old('template', $priceList->template ?? '') === 'with_photos' ? 'selected' : '' }}>با عکس</option>
                    <option value="grid" {{ old('template', $priceList->template ?? '') === 'grid' ? 'selected' : '' }}>شبکه‌ای</option>
                </select>
            </div>
            <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $priceList->is_active ?? true) ? 'checked' : '' }}>
                فعال
            </label>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--ds-border);">
            <button type="submit" class="ds-btn ds-btn-primary">ذخیره و ادامه</button>
            <a href="{{ route('price-lists.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
        </div>
    </form>
</div>
@endsection
