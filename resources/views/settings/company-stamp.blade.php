@extends('layouts.app')

@section('title', 'مهر / امضای فاکتور — تنظیمات شرکت — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-6 h-6'])
                </span>
                مهر / امضای فاکتور
            </h1>
            <p class="page-subtitle">یک تصویر شفاف (مهر شرکت یا امضا) روی نسخه چاپ فاکتور نمایش داده می‌شود.</p>
        </div>
        <a href="{{ route('settings.company') }}" class="btn-secondary w-full sm:w-auto justify-center" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none;">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به تنظیمات شرکت
        </a>
    </div>

    <div class="card-flat mb-6">
        @if (session('success'))
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color: #a7f3d0; background: #ecfdf5; color: #065f46;">
                {{ session('success') }}
            </div>
        @endif
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">تصویر مهر یا امضا</h2>
        <p class="mb-4 text-sm text-stone-500">فرمت PNG با پس‌زمینه شفاف برای مهر مناسب است. حداکثر ۲ مگابایت.</p>

        @if ($stampUrl ?? null)
            <div class="mb-6 flex flex-wrap items-start gap-4">
                <div class="rounded-lg border-2 p-2" style="border-color: #e7e5e4; background: #fafaf9;">
                    <img src="{{ $stampUrl }}" alt="مهر فعلی" class="max-h-32 max-w-[180px] object-contain" style="background: repeating-conic-gradient(#eee 0% 25%, #fff 0% 50%) 50% / 16px 16px;">
                </div>
                <form action="{{ route('settings.company.stamp.remove') }}" method="post" onsubmit="return confirm('مهر حذف شود؟');">
                    @csrf
                    <button type="submit" class="ds-btn ds-btn-danger">حذف مهر</button>
                </form>
            </div>
        @endif

        <form action="{{ route('settings.company.stamp.update') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">بارگذاری تصویر جدید</label>
                <input type="file" name="stamp" accept="image/png,image/jpeg,image/jpg,image/gif,image/webp" class="block w-full text-sm text-stone-600 file:mr-4 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700">
                @error('stamp')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="ds-btn ds-btn-primary">ذخیره مهر / امضا</button>
        </form>
    </div>
@endsection
