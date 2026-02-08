@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'فاکتورها — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
.inv-card { border-right-width: 4px; }
.inv-card.inv-sell { border-right-color: #059669; }
.inv-card.inv-buy { border-right-color: #0284c7; }
.inv-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: var(--ds-radius-sm); font-size: 0.75rem; font-weight: 600; }
.inv-badge-sell { background: #d1fae5; color: #047857; }
.inv-badge-buy { background: #dbeafe; color: #0369a1; }
.inv-badge-draft { background: #fef3c7; color: #b45309; }
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
                فاکتورها
            </h1>
            <p class="ds-page-subtitle">فاکتورهای فروش و خرید</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('invoices.create') }}" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'sell', 'class' => 'w-5 h-5'])
                فاکتور فروش
            </a>
            <a href="{{ route('invoices.create', ['type' => 'buy']) }}" class="ds-btn" style="background: #0284c7; color: #fff; border-color: #0369a1;">
                @include('components._icons', ['name' => 'buy', 'class' => 'w-5 h-5'])
                فاکتور خرید
            </a>
        </div>
    </div>

    {{-- Contact filter --}}
    @if (isset($contact))
        <div class="ds-alert-success" style="margin-bottom: 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <span class="ds-label" style="margin-bottom: 0;">فیلتر:</span>
                    <span style="font-weight: 600; color: var(--ds-primary-dark);">{{ $contact->name }}</span>
                </div>
                <a href="{{ route('invoices.index', request()->except('contact_id')) }}" style="font-size: 0.875rem; font-weight: 500; color: var(--ds-primary); text-decoration: none;">حذف فیلتر</a>
            </div>
        </div>
    @endif

    {{-- Type filter tabs --}}
    <div class="ds-filter-tabs">
        <a href="{{ route('invoices.index', request()->except('type')) }}" class="{{ !request('type') ? 'ds-filter-active' : '' }}" style="{{ !request('type') ? 'background: #292524; color: #fff;' : '' }}">همه</a>
        <a href="{{ route('invoices.index', array_merge(request()->except('type'), ['type' => 'sell'])) }}" class="{{ request('type') === 'sell' ? 'ds-filter-active' : '' }}" style="{{ request('type') === 'sell' ? 'background: var(--ds-primary); color: #fff;' : '' }}">فروش</a>
        <a href="{{ route('invoices.index', array_merge(request()->except('type'), ['type' => 'buy'])) }}" class="{{ request('type') === 'buy' ? 'ds-filter-active' : '' }}" style="{{ request('type') === 'buy' ? 'background: #0284c7; color: #fff;' : '' }}">خرید</a>
    </div>

    @if ($invoices->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 500; color: var(--ds-text-muted);">فاکتوری ثبت نشده است.</p>
            <a href="{{ route('invoices.create') }}" class="ds-btn ds-btn-primary">اولین فاکتور را ثبت کنید</a>
        </div>
    @else
        <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach ($invoices as $inv)
                <li>
                    <a href="{{ route('invoices.show', $inv) }}" class="ds-card inv-card {{ $inv->type === 'sell' ? 'inv-sell' : 'inv-buy' }}">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                            <div style="min-width: 0; flex: 1;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.375rem;">
                                    <span style="font-size: 1rem; font-weight: 600; color: #292524;">{{ $inv->contact->name ?? '—' }}</span>
                                    <span class="inv-badge {{ $inv->type === 'sell' ? 'inv-badge-sell' : 'inv-badge-buy' }}">
                                        {{ $inv->type === 'sell' ? 'فروش' : 'خرید' }}
                                    </span>
                                    @if ($inv->status === 'draft')
                                        <span class="inv-badge inv-badge-draft">پیش‌نویس</span>
                                    @endif
                                </div>
                                <div style="font-size: 0.875rem; color: var(--ds-text-subtle);">
                                    {{ FormatHelper::shamsi($inv->date) }} · {{ FormatHelper::rial($inv->total) }}
                                </div>
                                @if ($inv->status !== 'draft')
                                    @php $paid = $inv->totalPaid(); $remaining = (float)$inv->total - $paid; @endphp
                                    <div style="margin-top: 0.5rem; font-size: 0.8125rem; {{ $remaining <= 0 ? 'color: #047857;' : 'color: #b45309;' }}">
                                        پرداخت شده: {{ FormatHelper::rial($paid) }}
                                        @if ($remaining > 0)
                                            · باقیمانده: {{ FormatHelper::rial($remaining) }}
                                        @else
                                            · تسویه شده ✓
                                        @endif
                                    </div>
                                @endif
                            </div>
                            <span style="flex-shrink: 0; color: var(--ds-border-hover); display: inline-block; transform: rotate(180deg);">
                                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-5 h-5'])
                            </span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 1.5rem;">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
