@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'کالاها و خدمات — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.ds-search-form input { flex: 1; min-width: 0; }
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
                کالاها و خدمات
            </h1>
            <p class="ds-page-subtitle">کاتالوگ کالاها و خدمات برای فاکتور و لیست قیمت.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('products.create') }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                کالا/خدمت جدید
            </a>
            <a href="{{ route('products.import') }}" class="ds-btn ds-btn-outline">
                @include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4'])
                ورود CSV
            </a>
        </div>
    </div>

    <div class="ds-search-row" style="margin-bottom: 1.5rem;">
        <form action="{{ route('products.index') }}" method="get" class="ds-search-form" style="display: flex; gap: 0.5rem; flex: 1; min-width: 0; max-width: 28rem;">
            <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="جستجو نام، توضیحات، برچسب…" class="ds-input">
            <button type="submit" class="ds-btn ds-btn-secondary">
                @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
                <span>جستجو</span>
            </button>
        </form>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @if ($products->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-subtle);">هنوز کالا یا خدمتی ثبت نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem;">
                <a href="{{ route('products.create') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">اولین کالا/خدمت را اضافه کنید</a>
                یا
                <a href="{{ route('products.import') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">از فایل CSV وارد کنید</a>.
            </p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($products as $product)
                <a href="{{ route('products.show', $product) }}" class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="min-width: 0; flex: 1; display: flex; align-items: center; gap: 1rem;">
                        @if ($product->photo_path)
                            <img src="{{ asset('storage/' . $product->photo_path) }}" alt="" style="width: 3rem; height: 3rem; object-fit: cover; border-radius: var(--ds-radius);">
                        @endif
                        <div>
                            <div style="font-weight: 600; font-size: 1rem; color: var(--ds-text);">{{ $product->name }}</div>
                            @if ($product->tags->isNotEmpty())
                                <div style="display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.25rem;">
                                    @foreach ($product->tags as $tag)
                                        <span style="font-size: 0.75rem; padding: 0.125rem 0.5rem; border-radius: 9999px; background: {{ $tag->color }}20; color: {{ $tag->color }};">{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        @if ($product->default_unit_price)
                            <span style="font-size: 0.875rem; font-weight: 600; color: var(--ds-text-subtle);">{{ FormatHelper::rial($product->default_unit_price) }}</span>
                        @endif
                        <span class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem;">ویرایش</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $products->withQueryString()->links() }}
        </div>
    @endif
</div>
@endsection
