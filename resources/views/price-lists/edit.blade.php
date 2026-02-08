@extends('layouts.app')

@section('title', 'ویرایش ' . $priceList->name . ' — ' . config('app.name'))

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
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
                </span>
                ویرایش {{ $priceList->name }}
            </h1>
            <p class="ds-page-subtitle">بخش‌ها و آیتم‌های لیست قیمت را مدیریت کنید.</p>
        </div>
        <a href="{{ route('price-lists.show', $priceList) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @include('price-lists._form', ['priceList' => $priceList, 'products' => $products])
</div>
@endsection
