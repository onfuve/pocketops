@extends('layouts.app')

@section('title', 'بانک سوالات SERVQUAL — تنظیمات — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-6 h-6'])
                </span>
                بانک سوالات SERVQUAL
            </h1>
            <p class="page-subtitle">بررسی و ویرایش متن سوالات نظرسنجی میکرو (زبان و فرهنگ). هر سؤال در فرم لینک‌شده با فاکتور به صورت تصادفی نمایش داده می‌شود.</p>
        </div>
        <a href="{{ route('settings.company') }}" class="btn-secondary w-full sm:w-auto justify-center" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به تنظیمات شرکت
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color: #a7f3d0; background: #ecfdf5; color: #065f46;">
            {{ session('success') }}
        </div>
    @endif

    <div class="space-y-6">
        @foreach ($dimensions as $dimension)
            <div class="card-flat rounded-xl p-5" style="border: 2px solid #e7e5e4; background: #fff;">
                <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">
                    {{ $dimension->name_fa ?: $dimension->name }}
                </h2>
                <ul class="space-y-3">
                    @foreach ($dimension->questions as $q)
                        <li class="flex flex-wrap items-start justify-between gap-3 rounded-lg border p-3" style="border-color: #e7e5e4; background: #fafaf9;">
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-stone-800">{{ $q->text_fa ?: $q->text }}</p>
                                @if ($q->text_fa && $q->text_fa !== $q->text)
                                    <p class="mt-1 text-xs text-stone-500" dir="ltr">{{ $q->text }}</p>
                                @endif
                                <p class="mt-1 text-xs text-stone-400">
                                    وزن: {{ $q->weight }}
                                    @if ($q->is_reverse_scored)
                                        <span class="text-amber-600">— امتیاز معکوس</span>
                                    @endif
                                </p>
                            </div>
                            <a href="{{ route('settings.servqual-question-bank.edit', $q) }}" class="btn-secondary shrink-0" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.8125rem; font-weight: 500; text-decoration: none;">
                                ویرایش
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>
@endsection
