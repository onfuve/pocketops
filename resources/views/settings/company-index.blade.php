@extends('layouts.app')

@section('title', 'تنظیمات شرکت — ' . config('app.name'))

@section('content')
    <div class="mb-6">
        <h1 class="page-title flex items-center gap-2">
            <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                @include('components._icons', ['name' => 'cog', 'class' => 'w-6 h-6'])
            </span>
            تنظیمات شرکت
        </h1>
        <p class="page-subtitle" style="font-size: 0.9375rem; color: #78716c;">آدرس فرستنده، کارت و حساب، کاربران</p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <a href="{{ route('settings.company.address') }}" class="card-flat block rounded-xl p-5 no-underline transition hover:border-sky-300 hover:shadow-md" style="border: 2px solid #e7e5e4; background: #fff; text-decoration: none; color: inherit;">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-stone-800">آدرس فرستنده</h2>
                    <p class="mt-1 text-sm text-stone-500">آدرس شرکت/دفتر برای چاپ برچسب آدرس</p>
                </div>
            </div>
        </a>

        <a href="{{ route('settings.payment-options') }}" class="card-flat block rounded-xl p-5 no-underline transition hover:border-sky-300 hover:shadow-md" style="border: 2px solid #e7e5e4; background: #fff; text-decoration: none; color: inherit;">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-6 h-6'])
                </span>
                <div class="min-w-0">
                    <h2 class="text-base font-semibold text-stone-800">کارت و حساب</h2>
                    <p class="mt-1 text-sm text-stone-500">شماره کارت، شبا و حساب برای چاپ فاکتور</p>
                </div>
            </div>
        </a>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('users.index') }}" class="card-flat block rounded-xl p-5 no-underline transition hover:border-sky-300 hover:shadow-md" style="border: 2px solid #e7e5e4; background: #fff; text-decoration: none; color: inherit;">
                <div class="flex items-start gap-4">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl" style="background-color: #fef3c7; color: #b45309;">
                        @include('components._icons', ['name' => 'users', 'class' => 'w-6 h-6'])
                    </span>
                    <div class="min-w-0">
                        <h2 class="text-base font-semibold text-stone-800">کاربران</h2>
                        <p class="mt-1 text-sm text-stone-500">مدیریت کاربران و دسترسی‌ها</p>
                    </div>
                </div>
            </a>
        @endif
    </div>
@endsection
