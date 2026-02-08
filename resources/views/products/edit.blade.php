@extends('layouts.app')

@section('title', 'ویرایش ' . $product->name . ' — ' . config('app.name'))

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
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
                </span>
                ویرایش {{ $product->name }}
            </h1>
            <p class="ds-page-subtitle">اطلاعات کالا/خدمت را به‌روزرسانی کنید.</p>
        </div>
        <a href="{{ route('products.show', $product) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>
    @include('products._form', ['product' => $product, 'tags' => $tags])
</div>
@endsection
