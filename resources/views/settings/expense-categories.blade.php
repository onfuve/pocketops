@extends('layouts.app')

@section('title', 'دسته‌های هزینه — ' . config('app.name'))

@section('content')
<div class="ds-page" style="max-width: 52rem;">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #fff7ed; color: #c2410c; border-color: #fed7aa;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-5 h-5'])
                </span>
                دسته‌های هزینهٔ عملیاتی
            </h1>
            <p class="ds-page-subtitle">نام دسته‌ها در فرم ثبت هزینه و فیلتر لیست استفاده می‌شود. دسته‌ای که در هزینه‌ها استفاده شده باشد را نمی‌توان حذف کرد.</p>
        </div>
        <div class="flex flex-wrap gap-2 shrink-0">
            <a href="{{ route('settings.company') }}" class="ds-btn ds-btn-outline text-sm">تنظیمات شرکت</a>
            <a href="{{ route('expenses.index') }}" class="ds-btn ds-btn-secondary text-sm">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                هزینه‌ها
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="ds-alert-success mb-4 rounded-lg px-3 py-2 text-sm">{{ session('success') }}</div>
    @endif
    @if ($errors->has('delete'))
        <div class="mb-4 rounded-lg border px-3 py-2 text-sm" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">{{ $errors->first('delete') }}</div>
    @endif
    @if ($errors->any() && !$errors->has('delete'))
        <div class="mb-4 rounded-lg border px-3 py-2 text-sm" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">
            <ul class="list-disc list-inside m-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="ds-form-card mb-6">
        <h2 class="text-base font-semibold text-stone-800 m-0 mb-4 pb-3 border-b" style="border-color: var(--ds-border);">افزودن دسته</h2>
        <form action="{{ route('settings.expense-categories.store') }}" method="post" class="flex flex-wrap gap-3 items-end">
            @csrf
            <div class="flex-1 min-w-[12rem]">
                <label class="ds-label" for="exp-cat-name">نام دسته <span style="color:#b91c1c;">*</span></label>
                <input type="text" name="name" id="exp-cat-name" value="{{ old('name') }}" required maxlength="120"
                       class="ds-input w-full @error('name') border-red-500 @enderror" placeholder="مثلاً اجاره، حقوق، آب">
                @error('name')<p class="text-sm text-red-600 mt-1 m-0">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                افزودن
            </button>
        </form>
    </div>

    @if ($categories->isEmpty())
        <div class="ds-empty">دسته‌ای ثبت نشده.</div>
    @else
        <ul class="space-y-2 m-0 p-0 list-none">
            @foreach ($categories as $cat)
                <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border px-4 py-3" style="border-color: var(--ds-border); background: #fff;">
                    <div class="min-w-0">
                        <span class="font-medium text-stone-800">{{ $cat->name }}</span>
                        @if(in_array($cat->code, ['post', 'electricity', 'packaging', 'internet', 'other'], true))
                            <span class="text-xs text-stone-400 font-vazir mr-2">پیش‌فرض</span>
                        @endif
                    </div>
                    <form action="{{ route('settings.expense-categories.destroy', $cat) }}" method="post" class="shrink-0" onsubmit='return confirm(@json("آیا این دسته حذف شود؟"))'>
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="ds-btn ds-btn-outline text-sm" style="border-color: #fecaca; color: #b91c1c;">حذف</button>
                    </form>
                </li>
            @endforeach
        </ul>
    @endif
</div>
@endsection
