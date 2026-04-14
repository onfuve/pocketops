@extends('layouts.app')

@section('title', 'هزینه جدید — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #fff7ed; color: #c2410c; border-color: #fed7aa;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-5 h-5'])
                </span>
                ثبت هزینه عملیاتی
            </h1>
            <p class="ds-page-subtitle">هزینه‌های عملیاتی با دستهٔ قابل ویرایش، کارت/حساب و برچسب‌های قابل جستجو برای گزارش.</p>
        </div>
        <a href="{{ route('expenses.index') }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            لیست هزینه‌ها
        </a>
    </div>

    <div class="ds-form-card max-w-2xl">
        <form action="{{ route('expenses.store') }}" method="post">
            @csrf
            @include('expenses._form', ['expense' => $expense, 'tags' => $tags, 'paymentOptions' => $paymentOptions, 'defaultPaidAt' => $defaultPaidAt])
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">ذخیره</button>
                <a href="{{ route('expenses.index') }}" class="ds-btn ds-btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
