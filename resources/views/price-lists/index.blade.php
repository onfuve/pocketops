@extends('layouts.app')

@section('title', 'لیست قیمت — ' . config('app.name'))

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
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                لیست قیمت
            </h1>
            <p class="ds-page-subtitle">لیست‌های قیمت برای مشتریان با لینک عمومی.</p>
        </div>
        <a href="{{ route('price-lists.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            لیست قیمت جدید
        </a>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @if ($priceLists->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-subtle);">هنوز لیست قیمتی ایجاد نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem;"><a href="{{ route('price-lists.create') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">اولین لیست قیمت را ایجاد کنید</a></p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
            @foreach ($priceLists as $pl)
                <a href="{{ route('price-lists.show', $pl) }}" class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <div style="min-width: 0; flex: 1;">
                        <div style="font-weight: 600; font-size: 1rem; color: var(--ds-text);">{{ $pl->name }}</div>
                        <div style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">
                            {{ $pl->sections->count() }} بخش · {{ $pl->sections->sum(fn($s) => $s->items->count()) }} آیتم
                            @if ($pl->code)
                                · کد: {{ $pl->code }}
                            @endif
                        </div>
                    </div>
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        @if ($pl->code)
                            <a href="{{ route('price-lists.links', $pl) }}" class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem;" onclick="event.stopPropagation();">لینک</a>
                        @endif
                        <span class="ds-btn ds-btn-outline" style="padding: 0.375rem 0.75rem;">مشاهده</span>
                    </div>
                </a>
            @endforeach
        </div>

        <div style="margin-top: 1.5rem;">
            {{ $priceLists->links() }}
        </div>
    @endif
</div>
@endsection
