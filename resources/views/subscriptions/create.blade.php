@extends('layouts.app')

@section('title', 'اشتراک جدید — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'calendar', 'class' => 'w-5 h-5'])
                </span>
                اشتراک جدید
            </h1>
            <p class="ds-page-subtitle">ثبت سرویس recurring برای یک مشتری. پس از ذخیره، رویداد انقضا و در صورت تنظیم، یادآوری X روز قبل در تقویم ایجاد می‌شود.</p>
        </div>
        <a href="{{ route('subscriptions.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به لیست
        </a>
    </div>
    @include('subscriptions._form', ['subscription' => $subscription, 'contact' => $contact ?? null, 'users' => $users ?? collect()])
</div>
@endsection
