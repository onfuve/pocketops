@extends('layouts.app')

@section('title', 'صفحه فرود جدید — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #fff; border-color: #c4b5fd; }
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
                صفحه فرود جدید
            </h1>
            <p class="ds-page-subtitle">یک محصول انتخاب کنید و تنظیمات صفحه فرود را مشخص کنید.</p>
        </div>
        <a href="{{ route('product-landing-pages.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>

    <div class="ds-form-card" style="max-width: 40rem;">
        @include('product-landing-pages._form', ['page' => new \App\Models\ProductLandingPage(), 'products' => $products])
    </div>
</div>
@endsection
