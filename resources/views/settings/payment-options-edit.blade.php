@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'ویرایش کارت/حساب — ' . config('app.name'))

@push('styles')
<style>
.payment-edit-preview { border: 2px solid #e7e5e4; border-radius: 0.75rem; padding: 1rem; background: #fafaf9; margin-top: 1rem; }
.payment-edit-preview h4 { font-size: 0.875rem; font-weight: 600; color: #78716c; margin: 0 0 0.75rem; }
.payment-edit-preview .preview-line { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e7e5e4; font-size: 0.875rem; }
.payment-edit-preview .preview-line:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-6 h-6'])
                </span>
                ویرایش: {{ $option->label }}
            </h1>
            <p class="page-subtitle">تغییر عنوان، صاحب حساب، بانک و شماره‌ها</p>
        </div>
        <a href="{{ route('settings.payment-options') }}" class="btn-secondary w-full sm:w-auto justify-center" style="display: inline-flex; align-items: center; justify-content: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            <span>بازگشت</span>
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-4 rounded-xl border px-4 py-3 text-sm" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">
            <ul class="list-disc list-inside space-y-1">
                @foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="card-flat font-vazir">
        <form action="{{ route('settings.payment-options.update', $option) }}" method="post" id="edit-form" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1 block text-sm font-medium text-stone-700">عنوان <span class="text-red-600">*</span></label>
                <input type="text" name="label" required maxlength="100" value="{{ old('label', $option->label) }}" placeholder="مثلاً کارت بانکی ملت" class="input-ring w-full">
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">نام صاحب حساب/کارت</label>
                    <input type="text" name="holder_name" maxlength="100" value="{{ old('holder_name', $option->holder_name) }}" placeholder="نام صاحب حساب" class="input-ring w-full">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">نام بانک</label>
                    <input type="text" name="bank_name" maxlength="100" value="{{ old('bank_name', $option->bank_name) }}" placeholder="مثلاً ملت، ملی" class="input-ring w-full">
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره کارت</label>
                    <input type="text" name="card_number" id="card_number" maxlength="50" value="{{ old('card_number', $option->card_number) }}" placeholder="۱۶ رقم" dir="ltr" class="input-ring w-full font-vazir">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره شبا (۲۶ کاراکتر)</label>
                    <input type="text" name="iban" id="iban" maxlength="34" value="{{ old('iban', $option->iban) }}" placeholder="IR..." dir="ltr" class="input-ring w-full font-vazir">
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-stone-700">شماره حساب</label>
                    <input type="text" name="account_number" id="account_number" maxlength="50" value="{{ old('account_number', $option->account_number) }}" placeholder="شماره حساب" dir="ltr" class="input-ring w-full font-vazir">
                </div>
            </div>
            <p class="text-sm text-stone-500">حداقل یکی از فیلدهای بالا را پر کنید.</p>
            <div class="flex flex-wrap items-center gap-6 rounded-xl border-2 p-4" style="border-color: #e7e5e4; background: #fafaf9;">
                <span class="text-sm font-medium text-stone-700">در چاپ فاکتور نمایش داده شود:</span>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_card_number" id="print_card_number" value="1" {{ old('print_card_number', $option->print_card_number) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شماره کارت</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_iban" id="print_iban" value="1" {{ old('print_iban', $option->print_iban) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شبا</span>
                </label>
                <label class="inline-flex items-center gap-2 cursor-pointer min-h-[44px]">
                    <input type="checkbox" name="print_account_number" id="print_account_number" value="1" {{ old('print_account_number', $option->print_account_number) ? 'checked' : '' }} class="rounded border-stone-300 text-stone-700 focus:ring-stone-500">
                    <span>شماره حساب</span>
                </label>
            </div>
            <div class="payment-edit-preview">
                <h4>پیش‌نمایش در چاپ فاکتور</h4>
                <div id="preview_lines">
                    <p class="text-stone-500 text-sm">حداقل یکی از فیلدها را پر کنید و چک‌باکس مربوطه را فعال کنید.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3 pt-2">
                <button type="submit" class="btn-primary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #047857; box-shadow: 0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1); transition: all 0.2s; cursor: pointer;" onmouseover="this.style.background='linear-gradient(135deg, #047857 0%, #065f46 100%)';this.style.boxShadow='0 4px 8px rgba(5,150,105,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #059669 0%, #047857 100%)';this.style.boxShadow='0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                    <span style="color:#fff;">ذخیره تغییرات</span>
                </button>
                <a href="{{ route('settings.payment-options') }}" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                    انصراف
                </a>
            </div>
        </form>
    </div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('edit-form');
    var cardNum = document.getElementById('card_number');
    var iban = document.getElementById('iban');
    var accountNum = document.getElementById('account_number');
    var printCard = document.getElementById('print_card_number');
    var printIban = document.getElementById('print_iban');
    var printAccount = document.getElementById('print_account_number');
    var holderName = form.querySelector('input[name="holder_name"]');
    var bankName = form.querySelector('input[name="bank_name"]');
    var previewLines = document.getElementById('preview_lines');

    function updatePreview() {
        var lines = [];
        if (holderName && holderName.value.trim()) {
            lines.push({label: 'صاحب حساب/کارت', value: holderName.value.trim()});
        }
        if (bankName && bankName.value.trim()) {
            lines.push({label: 'بانک', value: bankName.value.trim()});
        }
        if (printCard.checked && cardNum.value.trim()) {
            lines.push({label: 'شماره کارت', value: cardNum.value.trim()});
        }
        if (printIban.checked && iban.value.trim()) {
            lines.push({label: 'شبا', value: iban.value.trim()});
        }
        if (printAccount.checked && accountNum.value.trim()) {
            lines.push({label: 'شماره حساب', value: accountNum.value.trim()});
        }
        if (lines.length === 0) {
            previewLines.innerHTML = '<p class="text-stone-500 text-sm">حداقل یکی از فیلدها را پر کنید و چک‌باکس مربوطه را فعال کنید.</p>';
        } else {
            previewLines.innerHTML = lines.map(function(l) {
                return '<div class="preview-line"><span>' + l.label + '</span><span dir="ltr">' + l.value + '</span></div>';
            }).join('');
        }
    }

    [cardNum, iban, accountNum, printCard, printIban, printAccount].forEach(function(el) {
        if (el) { el.addEventListener('input', updatePreview); el.addEventListener('change', updatePreview); }
    });
    if (holderName) holderName.addEventListener('input', updatePreview);
    if (bankName) bankName.addEventListener('input', updatePreview);
    updatePreview();
})();
</script>
@endpush
@endsection
