@php $isBuy = $invoice->type === 'buy'; $docLabel = $isBuy ? 'رسید' : 'فاکتور'; @endphp
@extends('layouts.app')

@section('title')
ویرایش {{ $docLabel }} — {{ config('app.name') }}
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
                ویرایش {{ $isBuy ? 'رسید خرید' : 'فاکتور فروش' }}
            </h1>
            <p class="ds-page-subtitle">{{ $invoice->contact->name ?? '' }} · {{ $invoice->invoice_number ?: '#' . $invoice->id }}</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
            <form action="{{ route('invoices.mark-final', $invoice) }}" method="post" style="display: inline;" onsubmit="return confirm('{{ $docLabel }} نهایی شود؟ پس از نهایی شدن امکان ویرایش نخواهد بود.');">
                @csrf
                <button type="submit" class="ds-btn ds-btn-primary">نهایی کردن {{ $docLabel }}</button>
            </form>
            <a href="{{ route('invoices.show', $invoice) }}" class="ds-btn ds-btn-outline">بازگشت به {{ $docLabel }}</a>
        </div>
    </div>
    @include('invoices._form', ['invoice' => $invoice, 'contact' => $contact, 'paymentOptions' => $paymentOptions ?? collect(), 'selectedIds' => $selectedIds ?? [], 'paymentOptionFields' => $paymentOptionFields ?? []])
</div>
@endsection
