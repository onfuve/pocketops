@extends('layouts.app')

@section('title', 'ویرایش صفحه فرود — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #fff; border-color: #c4b5fd; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header" style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
        <div>
            <h1 class="ds-page-title" style="margin: 0 0 0.25rem 0;">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
                </span>
                ویرایش صفحه فرود — {{ $productLandingPage->product->name ?? '—' }}
            </h1>
            <p class="ds-page-subtitle" style="margin: 0;">
                @if ($productLandingPage->code)
                    کد: {{ $productLandingPage->code }}
                    @if ($productLandingPage->is_active)
                        · <a href="{{ $productLandingPage->public_url }}" target="_blank" rel="noopener" style="font-weight: 600; color: var(--ds-primary);">مشاهده صفحه عمومی</a>
                    @endif
                    · <a href="{{ route('product-landing-pages.links', $productLandingPage) }}" style="font-weight: 600; color: var(--ds-primary);">لینک اشتراک</a>
                @else
                    کد منحصربه‌فرد تولید نشده
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            @if (!$productLandingPage->code)
                <form action="{{ route('product-landing-pages.generate-code', $productLandingPage) }}" method="post" style="display: inline;">
                    @csrf
                    <button type="submit" class="ds-btn ds-btn-primary">تولید کد</button>
                </form>
            @endif
            <form action="{{ route('product-landing-pages.destroy', $productLandingPage) }}" method="post" style="display: inline;" onsubmit="return confirm('این صفحه فرود حذف شود؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ds-btn ds-btn-danger">حذف</button>
            </form>
            <a href="{{ route('product-landing-pages.index') }}" class="ds-btn ds-btn-outline">بازگشت</a>
        </div>
    </div>

    <div class="ds-form-card" style="max-width: 40rem; margin-top: 1.5rem;">
        @include('product-landing-pages._form', ['page' => $productLandingPage, 'products' => $products])
    </div>
</div>
@endsection
