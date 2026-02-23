@extends('layouts.app')

@section('title', 'ویرایش اشتراک — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1d4ed8; border-color: #bfdbfe; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'calendar', 'class' => 'w-5 h-5'])
                </span>
                ویرایش اشتراک
            </h1>
            <p class="ds-page-subtitle">{{ $subscription->service_name }} — {{ $subscription->contact?->name }}</p>
        </div>
        <a href="{{ route('subscriptions.show', $subscription) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به جزئیات
        </a>
    </div>
    @include('subscriptions._form', ['subscription' => $subscription, 'users' => $users ?? collect()])
</div>
@endsection
