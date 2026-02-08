@extends('layouts.app')

@section('title', 'سرنخ جدید — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                </span>
                سرنخ جدید
            </h1>
            <p class="ds-page-subtitle">اطلاعات سرنخ را وارد کنید. کانال ورود و معرف (در صورت معرف بودن) را مشخص کنید.</p>
        </div>
        <a href="{{ route('leads.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به لیست
        </a>
    </div>
    @include('leads._form', ['lead' => $lead, 'leadChannels' => $leadChannels])
</div>
@endsection
