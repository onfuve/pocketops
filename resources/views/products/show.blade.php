@extends('layouts.app')

@section('title', $product->name . ' — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.product-photo { width: 8rem; height: 8rem; object-fit: cover; border-radius: var(--ds-radius-lg); border: 2px solid var(--ds-border); }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header" style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            @if ($product->photo_path)
                <img src="{{ asset('storage/' . $product->photo_path) }}" alt="" class="product-photo">
            @endif
            <div>
                <h1 class="ds-page-title" style="margin: 0 0 0.25rem 0;">{{ $product->name }}</h1>
                <p class="ds-page-subtitle" style="margin: 0;">
                    @if ($product->code_global || $product->code_internal)
                        @if ($product->code_global) کد جهانی: {{ $product->code_global }}@endif
                        @if ($product->code_global && $product->code_internal) · @endif
                        @if ($product->code_internal) کد داخلی: {{ $product->code_internal }}@endif
                    @else
                        —
                    @endif
                </p>
            </div>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('products.edit', $product) }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                ویرایش
            </a>
            <form action="{{ route('products.destroy', $product) }}" method="post" style="display: inline;" onsubmit="return confirm('این کالا/خدمت حذف شود؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ds-btn ds-btn-danger">
                    @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                    حذف
                </button>
            </form>
            <a href="{{ route('products.index') }}" class="ds-btn ds-btn-outline">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                لیست
            </a>
        </div>
    </div>

    @if ($product->description)
        <div class="ds-form-card" style="margin-bottom: 1.5rem;">
            <h2 class="ds-form-card-title">توضیحات</h2>
            <p style="margin: 0; white-space: pre-wrap;">{{ $product->description }}</p>
        </div>
    @endif

    @if ($product->tags->isNotEmpty())
        <div class="ds-form-card">
            <h2 class="ds-form-card-title">برچسب‌ها</h2>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach ($product->tags as $tag)
                    <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 500; background: {{ $tag->color }}20; color: {{ $tag->color }};">
                        <span style="width: 0.5rem; height: 0.5rem; border-radius: 50%; background: {{ $tag->color }};"></span>
                        {{ $tag->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
