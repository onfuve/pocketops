@extends('layouts.app')

@section('title', 'آدرس فرستنده — تنظیمات شرکت — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #dbeafe; color: #0369a1;">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-6 h-6'])
                </span>
                آدرس فرستنده
            </h1>
            <p class="page-subtitle">آدرس فرستنده در برچسب آدرس (چاپ) استفاده می‌شود.</p>
        </div>
        <a href="{{ route('settings.company') }}" class="btn-secondary w-full sm:w-auto justify-center" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            <span>بازگشت به تنظیمات شرکت</span>
        </a>
    </div>

    <div class="card-flat mb-6">
        @if (session('success'))
            <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color: #a7f3d0; background: #ecfdf5; color: #065f46;">
                {{ session('success') }}
            </div>
        @endif
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">آدرس فرستنده</h2>
        <p class="mb-4 text-sm text-stone-500">این آدرس هنگام چاپ برچسب آدرس مخاطبین، به عنوان «آدرس فرستنده» (یعنی آدرس شما/شرکت) قابل نمایش است. می‌توانید در صفحه چاپ برچسب انتخاب کنید که آدرس فرستنده نمایش داده شود یا نه.</p>
        <form action="{{ route('settings.company.update') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">آدرس فرستنده (شرکت / فروشگاه / دفتر)</label>
                <textarea name="sender_address" rows="4" placeholder="مثال: تهران، خیابان ولیعصر، کوچه X، پلاک Y" class="input-ring w-full" style="font-family: 'Vazirmatn', sans-serif;">{{ old('sender_address', $senderAddress ?? '') }}</textarea>
                @error('sender_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn-primary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #047857; box-shadow: 0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1); transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='linear-gradient(135deg, #047857 0%, #065f46 100%)';this.style.boxShadow='0 4px 8px rgba(5,150,105,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #059669 0%, #047857 100%)';this.style.boxShadow='0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                <span>ذخیره</span>
            </button>
        </form>
    </div>
@endsection
