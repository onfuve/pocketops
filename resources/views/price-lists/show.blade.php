@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', $priceList->name . ' — لیست قیمت — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1d4ed8; border-color: #93c5fd; }
.pl-item-badge { border-radius: 9999px; font-weight: 800; font-size: 0.65rem; padding: 0.25rem 0.5rem; letter-spacing: 0.02em; text-transform: uppercase; box-shadow: 0 1px 3px rgba(0,0,0,0.15); }
.pl-item-badge--new { background: linear-gradient(135deg, #3b82f6, #1d4ed8); color: #fff; }
.pl-item-badge--hot { background: linear-gradient(135deg, #06b6d4, #0891b2); color: #fff; }
.pl-item-badge--special_offer { background: linear-gradient(135deg, #10b981, #059669); color: #fff; }
.pl-item-badge--sale { background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header" style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
        <div>
            <h1 class="ds-page-title" style="margin: 0 0 0.25rem 0;">{{ $priceList->name }}</h1>
            <p class="ds-page-subtitle" style="margin: 0;">
                @if ($priceList->code)
                    کد: {{ $priceList->code }}
                    @if ($priceList->is_active)
                        · <a href="{{ $priceList->public_url }}" target="_blank" rel="noopener" style="font-weight: 600; color: var(--ds-primary);">مشاهده صفحه عمومی</a>
                    @endif
                @else
                    کد منحصربه‌فرد تولید نشده
                @endif
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
            <a href="{{ route('price-lists.edit', $priceList) }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                ویرایش
            </a>
            <form action="{{ route('price-lists.duplicate', $priceList) }}" method="post" style="display: inline;">
                @csrf
                <button type="submit" class="ds-btn ds-btn-secondary">کپی</button>
            </form>
            @if (!$priceList->code)
                <form action="{{ route('price-lists.generate-code', $priceList) }}" method="post" style="display: inline;">
                    @csrf
                    <button type="submit" class="ds-btn ds-btn-primary">تولید کد</button>
                </form>
            @else
                <a href="{{ route('price-lists.links', $priceList) }}" class="ds-btn ds-btn-outline">لینک اشتراک</a>
            @endif
            <form action="{{ route('price-lists.destroy', $priceList) }}" method="post" style="display: inline;" onsubmit="return confirm('این لیست قیمت حذف شود؟');">
                @csrf
                @method('DELETE')
                <button type="submit" class="ds-btn ds-btn-danger">
                    @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                    حذف
                </button>
            </form>
            <a href="{{ route('price-lists.index') }}" class="ds-btn ds-btn-outline">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                بازگشت
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="ds-alert-warning" style="margin-bottom: 1rem;">{{ session('warning') }}</div>
    @endif

    <div class="ds-form-card" style="margin-top: 1.5rem;">
        <h2 class="ds-form-card-title">تنظیمات</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(12rem, 1fr)); gap: 1rem;">
            <div><span style="color: var(--ds-text-subtle); font-size: 0.875rem;">عنوان نمایشی</span><br>{{ $priceList->title_text ?: '—' }}</div>
            <div><span style="color: var(--ds-text-subtle); font-size: 0.875rem;">نمایش قیمت</span><br>{{ $priceList->show_prices ? 'بله' : 'خیر' }}</div>
            <div><span style="color: var(--ds-text-subtle); font-size: 0.875rem;">نمایش عکس</span><br>{{ $priceList->show_photos ? 'بله' : 'خیر' }}</div>
            <div><span style="color: var(--ds-text-subtle); font-size: 0.875rem;">قالب</span><br>
                @if ($priceList->template === 'with_photos') با عکس
                @elseif ($priceList->template === 'grid') شبکه‌ای
                @else ساده
                @endif
            </div>
        </div>
    </div>

    <div class="ds-form-card" style="margin-top: 1.5rem;">
        <h2 class="ds-form-card-title">بخش‌ها و آیتم‌ها</h2>
        @forelse ($priceList->sections as $section)
            <div style="margin-bottom: 1.5rem;">
                <h3 style="font-size: 1.125rem; font-weight: 600; margin: 0 0 0.75rem 0; color: var(--ds-text);">{{ $section->name }}</h3>
                <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach ($section->items as $item)
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem; background: var(--ds-bg-muted); border-radius: var(--ds-radius); border: 1px solid var(--ds-border);">
                            <div>
                                <div style="font-weight: 500; display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                    {{ $item->display_name }}
                                    @if ($item->badge_label)
                                        <span class="ds-badge pl-item-badge pl-item-badge--{{ $item->badge }}">{{ $item->badge_label }}</span>
                                    @endif
                                </div>
                                @if ($item->display_description)
                                    <div style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">{{ Str::limit($item->display_description, 80) }}</div>
                                @endif
                            </div>
                            @if ($priceList->show_prices && $item->effective_price !== null)
                                <div style="font-weight: 600; font-size: 0.875rem;">{{ FormatHelper::priceForList($item->effective_price, $priceList->price_format ?? 'rial') }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <p style="color: var(--ds-text-subtle); margin: 0;">هنوز بخشی اضافه نشده. برای افزودن بخش‌ها و آیتم‌ها، ویرایش کنید.</p>
        @endforelse
    </div>
</div>
@endsection
