@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'سند هزینه / پاداش برای فاکتور — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => $invoice->type === \App\Models\Invoice::TYPE_BUY ? 'buy' : 'sell', 'class' => 'w-5 h-5'])
                </span>
                سند هزینه / پاداش برای {{ $invoice->type === \App\Models\Invoice::TYPE_BUY ? 'رسید خرید' : 'فاکتور فروش' }}
            </h1>
            <p class="ds-page-subtitle">
                فاکتور شماره {{ $invoice->invoice_number ?: $invoice->id }} — {{ $invoice->contact->name }} — مبلغ {{ FormatHelper::rial($invoice->total) }}
            </p>
        </div>
        <a href="{{ route('invoices.show', $invoice) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            بازگشت به فاکتور
        </a>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">ثبت هزینه / پاداش مرتبط با این فاکتور</h2>

        <form action="{{ route('invoices.cost-reward.store', $invoice) }}" method="post" class="space-y-5 max-w-xl">
            @csrf

            @if ($errors->any())
                <div class="mb-4 flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm" role="alert" style="border-color: #fecaca; background-color: #fef2f2; color: #b91c1c;">
                    @include('components._icons', ['name' => 'x', 'class' => 'w-5 h-5 shrink-0'])
                    <div>
                        <strong>خطا در ثبت اطلاعات:</strong>
                        <ul class="mt-1 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            <div>
                <label class="ds-label">مخاطب دریافت‌کننده هزینه / پاداش <span style="color:#b91c1c;">*</span></label>
                <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id') }}">
                <input type="text" id="contact_search" value="{{ old('contact_name') }}"
                       class="ds-input @error('contact_id') border-red-500 @enderror" placeholder="جستجو نام مخاطب" autocomplete="off">
                <div id="contact_results" class="dropdown-results" style="display:none;"></div>
                @error('contact_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="ds-label">مبلغ اصلی (ریال) <span style="color:#b91c1c;">*</span></label>
                    <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="1"
                           class="ds-input @error('amount') border-red-500 @enderror" dir="ltr" style="text-align:left;">
                    @error('amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="ds-label">کارمزد انتقال (ریال)</label>
                    <input type="number" name="fee_amount" id="fee_amount" value="{{ old('fee_amount') }}" min="1"
                           class="ds-input @error('fee_amount') border-red-500 @enderror" dir="ltr" style="text-align:left;">
                    <p class="mt-1 text-xs text-stone-500">در صورت وارد کردن، یک سند جداگانه برای کارمزد با همین مشخصات ایجاد می‌شود.</p>
                    @error('fee_amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label class="ds-label">تاریخ (شمسی) <span style="color:#b91c1c;">*</span></label>
                <div class="flex gap-2 flex-wrap items-center">
                    <input type="text" name="paid_at" id="paid_at" value="{{ old('paid_at', $defaultPaidAt) }}" placeholder="۱۴۰۳/۱۱/۱۳"
                           class="ds-input flex-1 min-w-0 @error('paid_at') border-red-500 @enderror"
                           dir="rtl" style="font-family:'Vazirmatn',sans-serif;" inputmode="numeric" autocomplete="off">
                    <button type="button" id="paid_at_today" class="ds-btn ds-btn-secondary" data-today="{{ $defaultPaidAt }}">امروز</button>
                </div>
                @error('paid_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ds-label">از طریق</label>
                <select name="payment_option_id" id="payment_option_id" class="ds-select">
                    <option value="">انتخاب حساب بانکی</option>
                    @foreach($paymentOptions as $opt)
                        <option value="{{ $opt->id }}" {{ old('payment_option_id') == $opt->id ? 'selected' : '' }}>
                            {{ $opt->label ?: ($opt->holder_name ?? $opt->bank_name ?? '—') }}
                            @if($opt->account_number) ({{ $opt->account_number }}) @endif
                        </option>
                    @endforeach
                </select>
                @if($paymentOptions->isEmpty())
                    <p class="mt-1 text-xs text-stone-500">
                        هنوز حسابی تعریف نشده است.
                        <a href="{{ route('settings.payment-options') }}" class="font-semibold" style="color:#059669;">تنظیمات کارت و حساب</a>
                    </p>
                @endif
                @error('payment_option_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="ds-label">یادداشت</label>
                <textarea name="notes" rows="2" class="ds-textarea" placeholder="مثال: هزینه حمل یا پاداش مرتبط با این فاکتور">{{ old('notes') }}</textarea>
            </div>

            @isset($tags)
                @include('components._tag-section', ['tags' => $tags, 'entity' => null, 'embedded' => true])
            @endisset

            <div class="pt-4 border-t mt-4 flex flex-wrap items-center gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">ثبت سند هزینه / پاداش</button>
                <a href="{{ route('invoices.show', $invoice) }}" class="ds-btn ds-btn-outline">انصراف</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var contactInput = document.getElementById('contact_id');
    var contactSearch = document.getElementById('contact_search');
    var contactResults = document.getElementById('contact_results');
    var debounce = null;

    function hideResultsSoon() {
        setTimeout(function () { contactResults.style.display = 'none'; }, 150);
    }

    function searchContacts(q) {
        if (!q || q.trim().length < 1) {
            contactResults.style.display = 'none';
            contactResults.innerHTML = '';
            return;
        }
        fetch('{{ route("contacts.search.api") }}?q=' + encodeURIComponent(q.trim()), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function(r) { return r.json(); })
            .then(function(list) {
                contactResults.innerHTML = '';
                if (!list.length) {
                    contactResults.innerHTML = '<a href="#" class="block px-3 py-2 text-sm text-stone-500">نتیجه‌ای یافت نشد</a>';
                } else {
                    list.forEach(function(c) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = c.name;
                        a.className = 'block px-3 py-2 text-sm hover:bg-stone-100';
                        a.addEventListener('click', function(e) {
                            e.preventDefault();
                            contactInput.value = c.id;
                            contactSearch.value = c.name;
                            contactResults.style.display = 'none';
                        });
                        contactResults.appendChild(a);
                    });
                }
                contactResults.style.display = 'block';
            })
            .catch(function() {
                contactResults.innerHTML = '<a href="#" class="block px-3 py-2 text-sm text-red-600">خطا در بارگذاری</a>';
                contactResults.style.display = 'block';
            });
    }

    if (contactSearch) {
        contactSearch.addEventListener('input', function() {
            clearTimeout(debounce);
            contactInput.value = '';
            debounce = setTimeout(function() {
                searchContacts(contactSearch.value);
            }, 220);
        });
        contactSearch.addEventListener('focus', function() {
            if (contactResults.innerHTML.trim() !== '') {
                contactResults.style.display = 'block';
            }
        });
        contactSearch.addEventListener('blur', hideResultsSoon);
    }

    var paidAtInput = document.getElementById('paid_at');
    var paidAtTodayBtn = document.getElementById('paid_at_today');
    if (paidAtInput && paidAtTodayBtn && paidAtTodayBtn.dataset.today) {
        paidAtTodayBtn.addEventListener('click', function() {
            paidAtInput.value = this.dataset.today;
            paidAtInput.focus();
        });
    }
})();
</script>
@endpush
@endsection

