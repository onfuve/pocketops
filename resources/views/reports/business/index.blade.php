@extends('layouts.app')

@section('title', 'گزارش‌های مالی — ' . config('app.name'))

@section('content')
@php use App\Helpers\FormatHelper; @endphp
<div class="ds-page" style="max-width: 48rem;">
    <header class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #047857; border-color: #a7f3d0;">
                    @include('components._icons', ['name' => 'chart-bar', 'class' => 'w-5 h-5'])
                </span>
                گزارش‌های مالی
            </h1>
            <p class="ds-page-subtitle">
                فیلتر تاریخ‌ها به‌صورت <strong>شمسی</strong> (مثلاً {{ FormatHelper::shamsi(now()) }}). در صورت خالی گذاشتن هر دو، بازهٔ <strong>امروز</strong> در نظر گرفته می‌شود.
                خروجی Excel فایل ‎.csv‎ با یونیکد است که با Excel باز می‌شود.
            </p>
        </div>
    </header>

    <div class="grid gap-4 sm:grid-cols-2" style="display: grid; gap: 1rem;">
        <a href="{{ route('reports.business.bank-account') }}" class="ds-form-card block no-underline transition hover:border-emerald-300 hover:shadow-md" style="text-decoration: none; color: inherit; border: 2px solid #e7e5e4; background: #fff; border-radius: 0.75rem; padding: 1.25rem;">
            <h2 class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                @include('components._icons', ['name' => 'credit-card', 'class' => 'w-5 h-5'])
                تراکنش‌های یک حساب بانکی
            </h2>
            <p class="text-sm" style="color: var(--ds-text-muted); margin: 0.5rem 0 0 0; line-height: 1.65;">
                پرداخت فاکتور و دریافت/پرداخت بدون فاکتور که به همان حساب ثبت شده‌اند، در بازهٔ تاریخ.
            </p>
        </a>
        <a href="{{ route('reports.business.all-transactions') }}" class="ds-form-card block no-underline transition hover:border-emerald-300 hover:shadow-md" style="text-decoration: none; color: inherit; border: 2px solid #e7e5e4; background: #fff; border-radius: 0.75rem; padding: 1.25rem;">
            <h2 class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                @include('components._icons', ['name' => 'clipboard-list', 'class' => 'w-5 h-5'])
                همه تراکنش‌های سیستم
            </h2>
            <p class="text-sm" style="color: var(--ds-text-muted); margin: 0.5rem 0 0 0; line-height: 1.65;">
                ترکیب پرداخت‌های فاکتور و تراکنش‌های مخاطب بر اساس <strong>تاریخ پرداخت</strong>.
            </p>
        </a>
        <a href="{{ route('reports.business.invoices') }}" class="ds-form-card block no-underline transition hover:border-emerald-300 hover:shadow-md" style="text-decoration: none; color: inherit; border: 2px solid #e7e5e4; background: #fff; border-radius: 0.75rem; padding: 1.25rem;">
            <h2 class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                فاکتورها (فروش / خرید)
            </h2>
            <p class="text-sm" style="color: var(--ds-text-muted); margin: 0.5rem 0 0 0; line-height: 1.65;">
                بر اساس <strong>تاریخ فاکتور</strong>؛ امکان انتخاب فقط فروش، فقط خرید یا هر دو.
            </p>
        </a>
        <a href="{{ route('reports.business.balances') }}" class="ds-form-card block no-underline transition hover:border-emerald-300 hover:shadow-md" style="text-decoration: none; color: inherit; border: 2px solid #e7e5e4; background: #fff; border-radius: 0.75rem; padding: 1.25rem;">
            <h2 class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem; font-size: 1rem;">
                @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                گزارش بدهکار / بستانکار
            </h2>
            <p class="text-sm" style="color: var(--ds-text-muted); margin: 0.5rem 0 0 0; line-height: 1.65;">
                ماندهٔ مخاطبین: مانده مثبت یعنی ما به مخاطب بدهکاریم؛ مانده منفی یعنی مخاطب به ما بدهکار است.
            </p>
        </a>
    </div>
</div>
@endsection
