@extends('layouts.app')

@section('title', 'ورود کالا/خدمت از CSV — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'file-import', 'class' => 'w-5 h-5'])
                </span>
                ورود کالا/خدمت از CSV
            </h1>
            <p class="ds-page-subtitle">فایل CSV با حداقل یک ستون نام (نام، item name، name یا نام کالا) آپلود کنید.</p>
        </div>
        <a href="{{ route('products.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>

    @if (session('error'))
        <div class="ds-alert-success" style="margin-bottom: 1rem; background: #fef2f2; border-color: #fecaca; color: #b91c1c;">{{ session('error') }}</div>
    @endif

    <div class="ds-form-card" style="max-width: 36rem;">
        <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin: 0 0 1rem;">ستون‌های مجاز: <strong>نام</strong>، <strong>name</strong>، <strong>item name</strong>، <strong>item_name</strong>، یا <strong>نام کالا</strong>. سایر ستون‌ها نادیده گرفته می‌شوند.</p>
        <form action="{{ route('products.import.store') }}" method="post" enctype="multipart/form-data" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            <div>
                <label for="file" class="ds-label">فایل CSV</label>
                <input type="file" name="file" id="file" class="ds-input" accept=".csv,.txt" required>
                @error('file')
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                @enderror
            </div>
            <div style="display: flex; gap: 0.75rem;">
                <button type="submit" class="ds-btn ds-btn-primary">ورود فایل</button>
                <a href="{{ route('products.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
