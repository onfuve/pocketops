@extends('layouts.app')

@section('title', 'صفحه فرود محصول — ' . config('app.name'))

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
                    @include('components._icons', ['name' => 'sell', 'class' => 'w-5 h-5'])
                </span>
                صفحه فرود محصول
            </h1>
            <p class="ds-page-subtitle">صفحات فرود اختصاصی برای هر محصول با لینک عمومی.</p>
        </div>
        <a href="{{ route('product-landing-pages.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            صفحه فرود جدید
        </a>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @if ($pages->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-subtle);">هنوز صفحه فرودی ایجاد نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem;"><a href="{{ route('product-landing-pages.create') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">اولین صفحه فرود را ایجاد کنید</a></p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($pages as $p)
                <div class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-weight: 600; font-size: 1rem; color: var(--ds-text);">{{ $p->product->name ?? '—' }}</div>
                        <div style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">
                            قالب: {{ $p->template === 'hero' ? 'قهرمان' : ($p->template === 'minimal' ? 'مینیمال' : ($p->template === 'card' ? 'کارت' : 'اسپلیت')) }}
                            @if ($p->code)
                                · کد: {{ $p->code }}
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <a href="{{ route('product-landing-pages.edit', $p) }}" class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;" onclick="event.stopPropagation();">
                            @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                            ویرایش
                        </a>
                        @if ($p->code)
                            <a href="{{ $p->public_url }}" target="_blank" rel="noopener" class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem;" onclick="event.stopPropagation();">مشاهده</a>
                            <a href="{{ route('product-landing-pages.links', $p) }}" class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem;" onclick="event.stopPropagation();">لینک</a>
                        @else
                            <span class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem; opacity: 0.7;">کد تولید کنید</span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $pages->links() }}
        </div>
    @endif
</div>
@endsection
