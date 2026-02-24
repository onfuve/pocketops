@extends('layouts.app')

@section('title', 'ویرایش سؤال SERVQUAL — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-6 h-6'])
                </span>
                ویرایش سؤال
            </h1>
            <p class="page-subtitle">بعد: {{ $question->dimension->name_fa ?: $question->dimension->name }}</p>
        </div>
        <a href="{{ route('settings.servqual-question-bank.index') }}" class="btn-secondary w-full sm:w-auto justify-center" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به بانک سوالات
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card-flat rounded-xl p-5" style="border: 2px solid #e7e5e4; background: #fff;">
        <form action="{{ route('settings.servqual-question-bank.update', $question) }}" method="post" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">متن سؤال (انگلیسی) <span class="text-red-600">*</span></label>
                <input type="text" name="text" required maxlength="200" value="{{ old('text', $question->text) }}" placeholder="e.g. The company has up-to-date equipment" class="input-ring w-full" dir="ltr">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">متن سؤال (فارسی)</label>
                <input type="text" name="text_fa" maxlength="200" value="{{ old('text_fa', $question->text_fa) }}" placeholder="مثال: شرکت تجهیزات به‌روز دارد" class="input-ring w-full">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">وزن (۱–۱۰)</label>
                    <input type="number" name="weight" min="1" max="10" value="{{ old('weight', $question->weight) }}" class="input-ring w-full">
                </div>
                <div class="flex items-end pb-2">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="hidden" name="is_reverse_scored" value="0">
                        <input type="checkbox" name="is_reverse_scored" value="1" {{ old('is_reverse_scored', $question->is_reverse_scored) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                        <span class="text-sm font-medium text-stone-700">امتیاز معکوس</span>
                    </label>
                </div>
            </div>
            <p class="text-sm text-stone-500">در سوالات با امتیاز معکوس، پاسخ «کاملاً مخالف» به عنوان بالاترین امتیاز در نظر گرفته می‌شود.</p>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="ds-btn ds-btn-primary">ذخیره تغییرات</button>
                <a href="{{ route('settings.servqual-question-bank.index') }}" class="btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
@endsection
