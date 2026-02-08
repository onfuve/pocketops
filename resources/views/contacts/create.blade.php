@extends('layouts.app')

@section('title', 'مخاطب جدید — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                مخاطب جدید
            </h1>
            <p class="ds-page-subtitle">اطلاعات مخاطب را وارد کنید. شماره تلفن‌ها، آدرس و برچسب‌ها را تنظیم کنید.</p>
        </div>
        <a href="{{ route('contacts.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به لیست
        </a>
    </div>
    @include('contacts._form', ['contact' => $contact])
</div>
@endsection
