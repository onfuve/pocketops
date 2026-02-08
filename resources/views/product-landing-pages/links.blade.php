@extends('layouts.app')

@section('title', 'لینک اشتراک — ' . ($productLandingPage->product->name ?? '') . ' — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: linear-gradient(135deg, #a78bfa, #7c3aed); color: #fff; border-color: #c4b5fd; }
.plp-links-card { max-width: 36rem; }
.plp-links-card input { font-family: monospace; font-size: 0.875rem; }
.plp-links-card .copy-btn { margin-top: 0.5rem; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'link', 'class' => 'w-5 h-5'])
                </span>
                لینک اشتراک
            </h1>
            <p class="ds-page-subtitle">{{ $productLandingPage->product->name ?? '—' }}</p>
        </div>
        <a href="{{ route('product-landing-pages.edit', $productLandingPage) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت
        </a>
    </div>

    <div class="ds-form-card plp-links-card" style="margin-top: 1.5rem;">
        <h2 class="ds-form-card-title">لینک عمومی</h2>
        <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-bottom: 1rem;">این لینک را با مشتریان به اشتراک بگذارید. برای مشاهده نیازی به ورود نیست.</p>
        <div>
            <label for="public-url" class="ds-label">آدرس کامل</label>
            <input type="text" id="public-url" value="{{ $productLandingPage->public_url }}" readonly class="ds-input" onclick="this.select();">
            <button type="button" class="ds-btn ds-btn-secondary copy-btn" onclick="navigator.clipboard.writeText(document.getElementById('public-url').value); this.textContent='کپی شد!'; setTimeout(function(){ this.textContent='کپی'; }.bind(this), 1500);">
                کپی
            </button>
        </div>
    </div>
</div>
@endsection
