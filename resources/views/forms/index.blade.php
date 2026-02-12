@extends('layouts.app')

@section('title', 'فرم‌های جمع‌آوری — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                فرم‌های جمع‌آوری
            </h1>
            <p class="ds-page-subtitle">فرم‌های داینامیک با لینک یکتا؛ مشتری پر می‌کند، شما در صندوق ورودی می‌بینید.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('forms.inbox') }}" class="ds-btn ds-btn-outline">
                @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
                صندوق ورودی
            </a>
            <a href="{{ route('forms.create') }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                فرم جدید
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @if ($forms->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-subtle);">هنوز فرمی نساخته‌اید.</p>
            <p style="margin: 0; font-size: 0.875rem;">
                <a href="{{ route('forms.create') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">اولین فرم را بسازید</a> و ماژول‌ها (آپلود فایل، آدرس، رضایت، نظرسنجی و …) را اضافه کنید.
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($forms as $form)
                <a href="{{ route('forms.show', $form) }}" class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div>
                        <div style="font-weight: 600; font-size: 1rem; color: var(--ds-text);">{{ $form->title }}</div>
                        <div style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">
                            {{ $form->links_count }} لینک · {{ $form->submissions_count }} ارسال
                            · وضعیت:
                            @if($form->status === 'draft') پیش‌نویس
                            @elseif($form->status === 'active') فعال
                            @else بسته
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a href="{{ route('forms.edit', $form) }}" class="ds-btn ds-btn-ghost" style="padding: 0.5rem 0.75rem;">ویرایش</a>
                    </div>
                </a>
            @endforeach
        </div>
        <div style="margin-top: 1.5rem;">
            {{ $forms->links() }}
        </div>
    @endif
</div>
@endsection
