@php $isBuy = ($invoice->type ?? old('type', 'sell')) === 'buy'; @endphp
@extends('layouts.app')

@section('title')
{{ $isBuy ? 'رسید خرید جدید' : 'فاکتور فروش جدید' }} — {{ config('app.name') }}
@endsection

@push('styles')
<style>
.ds-page .ds-page-title-icon.inv-buy { background: #e0f2fe; color: #0369a1; border-color: #bae6fd; }
.ds-page .ds-page-title-icon.inv-sell { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon {{ $isBuy ? 'inv-buy' : 'inv-sell' }}">
                    @include('components._icons', ['name' => $isBuy ? 'buy' : 'sell', 'class' => 'w-5 h-5'])
                </span>
                {{ $isBuy ? 'رسید خرید جدید' : 'فاکتور فروش جدید' }}
            </h1>
            <p class="ds-page-subtitle">{{ $isBuy ? 'ثبت رسید خرید از فروشنده' : 'ثبت فاکتور فروش برای مشتری' }}</p>
        </div>
        <a href="{{ route('invoices.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            لیست فاکتورها
        </a>
    </div>
    @include('invoices._form', ['invoice' => $invoice, 'contact' => $contact, 'paymentOptions' => $paymentOptions ?? collect(), 'selectedIds' => $selectedIds ?? [], 'paymentOptionFields' => $paymentOptionFields ?? [], 'formLinks' => $formLinks ?? collect()])
</div>
@endsection
