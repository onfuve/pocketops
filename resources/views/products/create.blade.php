@extends('layouts.app')

@section('title', 'کالا/خدمت جدید — ' . config('app.name'))

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
                    @include('components._icons', ['name' => 'sell', 'class' => 'w-5 h-5'])
                </span>
                کالا/خدمت جدید
            </h1>
            <p class="ds-page-subtitle">کالا یا خدمتی که ارائه می‌دهید را ثبت کنید.</p>
        </div>
        <a href="{{ route('products.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به لیست
        </a>
    </div>
    @include('products._form', ['product' => $product, 'tags' => $tags])
</div>
@endsection
