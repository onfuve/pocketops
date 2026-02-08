@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'کارت و حساب برای چاپ فاکتور — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-6 h-6'])
                </span>
                کارت و حساب برای چاپ فاکتور
            </h1>
            <p class="page-subtitle">هر مورد می‌تواند شماره کارت، شبا و/یا شماره حساب داشته باشد. نام صاحب و بانک در چاپ فاکتور نمایش داده می‌شود.</p>
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
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">افزودن مورد جدید</h2>
        <form action="{{ route('settings.payment-options.store') }}" method="post" class="space-y-4">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">عنوان (مثلاً کارت بانکی ملت)</label>
                    <input type="text" name="label" required maxlength="100" value="{{ old('label') }}" placeholder="عنوان" class="input-ring w-full">
                    @error('label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">نام صاحب حساب/کارت</label>
                    <input type="text" name="holder_name" maxlength="100" value="{{ old('holder_name') }}" placeholder="نام صاحب حساب" class="input-ring w-full">
                    @error('holder_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">نام بانک</label>
                    <input type="text" name="bank_name" maxlength="100" value="{{ old('bank_name') }}" placeholder="مثلاً ملت، ملی، ..." class="input-ring w-full">
                    @error('bank_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره کارت</label>
                    <input type="text" name="card_number" maxlength="50" value="{{ old('card_number') }}" placeholder="۱۶ رقم" dir="ltr" class="input-ring w-full font-vazir">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره شبا (۲۶ کاراکتر)</label>
                    <input type="text" name="iban" maxlength="34" value="{{ old('iban') }}" placeholder="IR..." dir="ltr" class="input-ring w-full font-vazir">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره حساب</label>
                    <input type="text" name="account_number" maxlength="50" value="{{ old('account_number') }}" placeholder="شماره حساب" dir="ltr" class="input-ring w-full font-vazir">
                </div>
            </div>
            <p class="text-sm text-stone-500">حداقل یکی از فیلدهای بالا را پر کنید.</p>
            <div class="flex flex-wrap items-center gap-6 rounded-xl border-2 p-4" style="border-color: #e7e5e4; background: #fafaf9;">
                <span class="text-sm font-medium text-stone-700">در چاپ فاکتور نمایش داده شود:</span>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_card_number" value="1" {{ old('print_card_number', true) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شماره کارت</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_iban" value="1" {{ old('print_iban', true) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شبا</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_account_number" value="1" {{ old('print_account_number', true) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شماره حساب</span>
                </label>
            </div>
            @error('value')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <button type="submit" class="btn-primary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #047857; box-shadow: 0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1); transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='linear-gradient(135deg, #047857 0%, #065f46 100%)';this.style.boxShadow='0 4px 8px rgba(5,150,105,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #059669 0%, #047857 100%)';this.style.boxShadow='0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                <span>افزودن</span>
            </button>
        </form>
    </div>

    @if ($options->isEmpty())
        <div class="empty-state">
            <p class="mb-3" style="color: #57534e;">هنوز موردی ثبت نشده است.</p>
            <p class="text-sm">
                از فرم بالا یک کارت یا حساب اضافه کنید تا در چاپ فاکتورها نمایش داده شود.
            </p>
        </div>
    @else
        <ul class="space-y-3">
            @foreach ($options as $opt)
                <li class="card flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between" style="border-right: 4px solid #a7f3d0;">
                    <div class="min-w-0 flex-1">
                        <div class="font-semibold text-stone-800">{{ $opt->label }}</div>
                        @if ($opt->holder_name || $opt->bank_name)
                            <div class="mt-1 text-sm text-stone-500">
                                @if ($opt->holder_name)<span>صاحب: {{ $opt->holder_name }}</span>@endif
                                @if ($opt->holder_name && $opt->bank_name)<span class="mx-1">·</span>@endif
                                @if ($opt->bank_name)<span>بانک: {{ $opt->bank_name }}</span>@endif
                            </div>
                        @endif
                        <div class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-stone-600">
                            @if ($opt->card_number)
                                <span>کارت: <span dir="ltr" class="font-vazir">{{ FormatHelper::englishToPersian($opt->card_number) }}</span></span>
                            @endif
                            @if ($opt->iban)
                                <span>شبا: <span dir="ltr" class="font-vazir">{{ FormatHelper::englishToPersian($opt->iban) }}</span></span>
                            @endif
                            @if ($opt->account_number)
                                <span>حساب: <span dir="ltr" class="font-vazir">{{ FormatHelper::englishToPersian($opt->account_number) }}</span></span>
                            @endif
                            @if (!$opt->card_number && !$opt->iban && !$opt->account_number && $opt->value)
                                <span dir="ltr" class="font-vazir">{{ FormatHelper::englishToPersian($opt->value) }}</span>
                            @endif
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            @if ($opt->print_card_number && $opt->card_number)<span class="badge badge-primary">چاپ کارت</span>@endif
                            @if ($opt->print_iban && $opt->iban)<span class="badge badge-primary">چاپ شبا</span>@endif
                            @if ($opt->print_account_number && $opt->account_number)<span class="badge badge-primary">چاپ حساب</span>@endif
                        </div>
                    </div>
                    <div class="flex shrink-0 flex-wrap gap-2">
                        <a href="{{ route('settings.payment-options.edit', $opt) }}" class="btn-secondary text-sm py-2 min-h-[44px]" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                            @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                            <span class="hidden sm:inline">ویرایش</span>
                        </a>
                        <form action="{{ route('settings.payment-options.destroy', $opt) }}" method="post" class="inline" onsubmit="return confirm('این مورد حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-danger text-sm py-2 min-h-[44px]" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; border: 2px solid #fecaca; background: #fff; color: #b91c1c; font-size: 0.875rem; font-weight: 500; cursor: pointer; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.backgroundColor='#fef2f2';this.style.borderColor='#fca5a5';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.backgroundColor='#fff';this.style.borderColor='#fecaca';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                <span class="hidden sm:inline">حذف</span>
                            </button>
                        </form>
                    </div>
                </li>
            @endforeach
        </ul>
    @endif
@endsection
