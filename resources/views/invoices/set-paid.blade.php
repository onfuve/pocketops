@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'ثبت پرداخت فاکتور')

@push('styles')
<style>
.invoice-set-paid { font-family: 'Vazirmatn', sans-serif; max-width: 36rem; margin: 0 auto; }
.invoice-set-paid .card { border: 2px solid #d6d3d1; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.invoice-set-paid .card-title { border-bottom: 2px solid #e7e5e4; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #292524; }
.invoice-set-paid label { display: block; font-weight: 600; color: #44403c; margin-bottom: 0.375rem; font-size: 0.875rem; }
.invoice-set-paid input[type="text"],
.invoice-set-paid input[type="number"],
.invoice-set-paid select,
.invoice-set-paid textarea { width: 100%; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-family: 'Vazirmatn', sans-serif; font-size: 1rem; color: #292524; box-sizing: border-box; }
.invoice-set-paid input:focus,
.invoice-set-paid select:focus,
.invoice-set-paid textarea:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.2); }
.invoice-set-paid .form-group { margin-bottom: 1.25rem; }
.invoice-set-paid .text-muted { font-size: 0.8125rem; color: #78716c; margin-top: 0.25rem; }
.invoice-set-paid .text-danger { color: #b91c1c; font-size: 0.875rem; margin-top: 0.25rem; }
.invoice-set-paid .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0.625rem 1rem; border-radius: 0.75rem; font-family: 'Vazirmatn', sans-serif; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 2px solid transparent; cursor: pointer; transition: all 0.2s; }
.invoice-set-paid .btn-primary { background: #059669; color: #fff !important; border-color: #047857; }
.invoice-set-paid .btn-primary:hover { background: #047857; color: #fff !important; }
.invoice-set-paid .btn-secondary { background: #fff; color: #44403c; border-color: #d6d3d1; }
.invoice-set-paid .btn-secondary:hover { background: #fafaf9; color: #292524; }
.invoice-set-paid .payment-method { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
.invoice-set-paid .payment-method input { display: none; }
.invoice-set-paid .payment-method label { display: inline-flex; align-items: center; padding: 0.75rem 1.25rem; border: 2px solid #d6d3d1; border-radius: 0.75rem; cursor: pointer; margin: 0; font-weight: 600; transition: all 0.2s; }
.invoice-set-paid .payment-method input:checked + label { border-color: #059669; background: #ecfdf5; color: #047857; }
.invoice-set-paid .payment-method label:hover { border-color: #a7f3d0; }
.invoice-set-paid .payment-panel { display: none; margin-top: 0.75rem; }
.invoice-set-paid .payment-panel.active { display: block; }
.invoice-set-paid .autocomplete-wrap { position: relative; }
.invoice-set-paid .autocomplete-results { position: absolute; top: 100%; left: 0; right: 0; margin-top: 2px; background: #fff; border: 2px solid #d6d3d1; border-radius: 0.5rem; max-height: 280px; overflow-y: auto; z-index: 50; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.invoice-set-paid .autocomplete-results li { padding: 0.625rem 0.75rem; cursor: pointer; border-bottom: 1px solid #e7e5e4; font-size: 0.9375rem; list-style: none; }
.invoice-set-paid .autocomplete-results li:last-child { border-bottom: none; }
.invoice-set-paid .autocomplete-results li:hover,
.invoice-set-paid .autocomplete-results li.selected { background: #ecfdf5; color: #047857; }
.invoice-set-paid .autocomplete-results .balance { font-size: 0.8125rem; color: #78716c; margin-right: 0.5rem; }
.invoice-set-paid .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e7e5e4; }
.invoice-set-paid .summary-row:last-child { border-bottom: none; }
.invoice-set-paid .summary-value { font-weight: 600; color: #292524; }
.invoice-set-paid .chosen-contact { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; font-size: 0.875rem; }
.invoice-set-paid .chosen-contact .clear-contact { color: #b91c1c; cursor: pointer; text-decoration: underline; }
</style>
@endpush

@section('content')
<div class="invoice-set-paid font-vazir">
    <div class="mb-6 flex flex-wrap items-center gap-3" style="gap: 0.75rem;">
        <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary" style="padding: 0.5rem 0.75rem;">← بازگشت به فاکتور</a>
        <h1 class="text-xl font-bold text-stone-800 sm:text-2xl" style="font-family:'Vazirmatn',sans-serif;">ثبت پرداخت</h1>
    </div>

    {{-- Summary --}}
    <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-color: #a7f3d0;">
        <div class="card-title" style="font-family:'Vazirmatn',sans-serif;">خلاصه فاکتور</div>
        <div class="summary-row">
            <span class="text-stone-600">مخاطب فاکتور</span>
            <span class="summary-value">{{ $invoice->contact->name }}</span>
        </div>
        <div class="summary-row">
            <span class="text-stone-600">مبلغ فاکتور</span>
            <span class="summary-value">{{ FormatHelper::rial($invoice->total) }}</span>
        </div>
        <div class="summary-row">
            <span class="text-stone-600">پرداخت شده</span>
            <span class="summary-value" style="color:#047857;">{{ FormatHelper::rial($invoice->totalPaid()) }}</span>
        </div>
        <div class="summary-row">
            <span class="text-stone-600">باقیمانده</span>
            <span class="summary-value" style="color:#b45309;">{{ FormatHelper::rial($remaining) }}</span>
        </div>
    </div>

    <div class="card">
        <div class="card-title" style="font-family:'Vazirmatn',sans-serif;">جزئیات پرداخت</div>
        <form method="post" action="{{ route('invoices.set-paid.submit', $invoice) }}" id="set-paid-form">
            @csrf
            <div class="form-group">
                <label>مبلغ (ریال) <span style="color:#b91c1c;">*</span></label>
                <input type="number" name="amount" id="amount" value="{{ old('amount', $remaining) }}" min="1" required
                    class="@error('amount') border-red-500 @enderror" dir="ltr" style="text-align:left;">
                @error('amount')<p class="text-danger">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label>تاریخ پرداخت (شمسی) <span style="color:#b91c1c;">*</span></label>
                <div class="flex gap-2 flex-wrap items-center">
                    <input type="text" name="paid_at" id="paid_at" value="{{ old('paid_at', $defaultPaidAt ?? FormatHelper::shamsi(now())) }}" placeholder="۱۴۰۳/۱۱/۱۳" required
                        class="flex-1 min-w-0 font-vazir @error('paid_at') border-red-500 @enderror" dir="rtl" style="font-family:'Vazirmatn',sans-serif;"
                        inputmode="numeric" autocomplete="off">
                    <button type="button" id="paid_at_today" class="shrink-0 rounded-lg border-2 border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 min-h-[44px] transition" data-today="{{ $defaultPaidAt ?? FormatHelper::shamsi(now()) }}">امروز</button>
                </div>
                @error('paid_at')<p class="text-danger">{{ $message }}</p>@enderror
                <p class="text-muted">فرمت: ۱۴۰۳/۱۱/۱۳ — می‌توانید با اعداد فارسی یا انگلیسی وارد کنید.</p>
            </div>

            <div class="form-group">
                <label>نوع پرداخت</label>
                <div class="payment-method">
                    <input type="radio" name="payment_type" id="pay_type_bank" value="bank" {{ old('payment_type', 'bank') === 'bank' ? 'checked' : '' }}>
                    <label for="pay_type_bank">پرداخت به حساب بانکی</label>
                    <input type="radio" name="payment_type" id="pay_type_contact" value="contact" {{ old('payment_type') === 'contact' ? 'checked' : '' }}>
                    <label for="pay_type_contact">پرداخت به/از طریق مخاطب</label>
                </div>

                <div id="panel-bank" class="payment-panel {{ old('payment_type', 'bank') === 'bank' ? 'active' : '' }}">
                    <select name="payment_option_id" id="payment_option_id">
                        <option value="">انتخاب حساب بانکی</option>
                        @foreach($paymentOptions as $opt)
                            <option value="{{ $opt->id }}" {{ old('payment_option_id') == $opt->id ? 'selected' : '' }}>{{ $opt->label ?: ($opt->holder_name ?? $opt->bank_name ?? '—') }} @if($opt->account_number)({{ $opt->account_number }})@endif</option>
                        @endforeach
                    </select>
                    @if($paymentOptions->isEmpty())
                        <p class="text-muted" style="margin-top: 0.5rem;">
                            هنوز حسابی تعریف نشده است.
                            <a href="{{ route('settings.payment-options') }}" style="font-weight: 600; color: #059669; text-decoration: none;">تنظیمات کارت و حساب</a>
                        </p>
                    @endif
                </div>

                @php
                    $oldContactId = old('contact_id');
                    $oldContactName = $oldContactId ? (\App\Models\Contact::find($oldContactId)?->name ?? '') : '';
                @endphp
                <div id="panel-contact" class="payment-panel {{ old('payment_type') === 'contact' ? 'active' : '' }}">
                    <div class="autocomplete-wrap">
                        <input type="text" id="contact_search" placeholder="نام مخاطب را تایپ کنید (حداقل ۲ حرف)..." autocomplete="off"
                            value="{{ $oldContactName }}">
                        <input type="hidden" name="contact_id" id="contact_id" value="{{ $oldContactId ?? '' }}">
                        <ul id="contact_results" class="autocomplete-results" style="display:none;"></ul>
                    </div>
                    <div id="chosen_contact_display" class="chosen-contact" style="{{ $oldContactId ? '' : 'display:none;' }}">
                        <span id="chosen_contact_name">{{ $oldContactName }}</span>
                        <button type="button" class="clear-contact" id="clear_contact">حذف</button>
                    </div>
                    <p class="text-muted">فروش: مشتری به این مخاطب پرداخت کرده. خرید: از طریق این مخاطب پرداخت شده.</p>
                </div>
            </div>

            <div class="form-group">
                <label>یادداشت</label>
                <textarea name="notes" rows="2" placeholder="اختیاری">{{ old('notes') }}</textarea>
            </div>
            @error('payment_option_id')<p class="text-danger">{{ $message }}</p>@enderror
            @error('contact_id')<p class="text-danger">{{ $message }}</p>@enderror

            <div class="flex flex-wrap items-center gap-3 mt-6" style="gap: 0.75rem;">
                <button type="submit" class="btn btn-primary px-6 py-3" style="font-size:1rem;"><span style="color:#fff;">ثبت پرداخت</span></button>
                <a href="{{ route('invoices.show', $invoice) }}" class="btn btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('set-paid-form');
    var payTypeBank = document.getElementById('pay_type_bank');
    var payTypeContact = document.getElementById('pay_type_contact');
    var panelBank = document.getElementById('panel-bank');
    var panelContact = document.getElementById('panel-contact');
    var bankSelect = document.getElementById('payment_option_id');
    var contactIdInput = document.getElementById('contact_id');
    var contactSearch = document.getElementById('contact_search');
    var contactResults = document.getElementById('contact_results');
    var chosenDisplay = document.getElementById('chosen_contact_display');
    var chosenName = document.getElementById('chosen_contact_name');
    var clearContactBtn = document.getElementById('clear_contact');
    var searchUrl = '{{ route("contacts.search.api") }}';
    var debounceTimer = null;
    var selectedIndex = -1;

    function togglePanels() {
        if (payTypeBank.checked) {
            panelBank.classList.add('active');
            panelContact.classList.remove('active');
            contactIdInput.value = '';
            contactSearch.value = '';
            chosenDisplay.style.display = 'none';
            contactResults.style.display = 'none';
        } else {
            panelBank.classList.remove('active');
            panelContact.classList.add('active');
            bankSelect.value = '';
        }
    }
    payTypeBank.addEventListener('change', togglePanels);
    payTypeContact.addEventListener('change', togglePanels);

    function showChosen(name) {
        chosenName.textContent = name;
        chosenDisplay.style.display = 'inline-flex';
        contactSearch.style.display = 'none';
    }
    function clearChosen() {
        contactIdInput.value = '';
        chosenName.textContent = '';
        chosenDisplay.style.display = 'none';
        contactSearch.style.display = '';
        contactSearch.value = '';
        contactSearch.focus();
    }
    clearContactBtn.addEventListener('click', clearChosen);

    function fetchContacts(q) {
        if (!q || q.length < 2) {
            contactResults.style.display = 'none';
            contactResults.innerHTML = '';
            return;
        }
        var url = searchUrl + '?q=' + encodeURIComponent(q) + '&with_balance=1';
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(list) {
                contactResults.innerHTML = '';
                selectedIndex = -1;
                if (list.length === 0) {
                    contactResults.innerHTML = '<li class="no-results" style="padding:0.75rem;color:#78716c;">نتیجه‌ای یافت نشد</li>';
                } else {
                    list.forEach(function(c, i) {
                        var li = document.createElement('li');
                        li.setAttribute('data-id', c.id);
                        li.setAttribute('data-name', c.name);
                        var balanceText = (c.balance !== undefined) ? ' (مانده: ' + Number(c.balance).toLocaleString('fa-IR') + ' ریال)' : '';
                        li.textContent = c.name + balanceText;
                        li.addEventListener('click', function() {
                            contactIdInput.value = c.id;
                            showChosen(c.name);
                            contactResults.style.display = 'none';
                            contactResults.innerHTML = '';
                        });
                        contactResults.appendChild(li);
                    });
                }
                contactResults.style.display = 'block';
            })
            .catch(function() {
                contactResults.innerHTML = '<li style="color:#b91c1c;">خطا در بارگذاری</li>';
                contactResults.style.display = 'block';
            });
    }

    contactSearch.addEventListener('input', function() {
        var q = this.value.trim();
        if (contactIdInput.value) {
            contactIdInput.value = '';
            chosenDisplay.style.display = 'none';
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { fetchContacts(q); }, 280);
    });
    contactSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && contactResults.querySelector('li')) contactResults.style.display = 'block';
    });
    contactSearch.addEventListener('keydown', function(e) {
        var items = contactResults.querySelectorAll('li[data-id]');
        if (e.key === 'ArrowDown') {
            e.preventDefault();
            selectedIndex = Math.min(selectedIndex + 1, items.length - 1);
            items.forEach(function(el, i) { el.classList.toggle('selected', i === selectedIndex); });
            if (items[selectedIndex]) items[selectedIndex].scrollIntoView({ block: 'nearest' });
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            selectedIndex = Math.max(selectedIndex - 1, -1);
            items.forEach(function(el, i) { el.classList.toggle('selected', i === selectedIndex); });
        } else if (e.key === 'Enter' && selectedIndex >= 0 && items[selectedIndex]) {
            e.preventDefault();
            items[selectedIndex].click();
        } else if (e.key === 'Escape') {
            contactResults.style.display = 'none';
        }
    });

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-wrap')) contactResults.style.display = 'none';
    });

    form.addEventListener('submit', function(e) {
        var isBank = payTypeBank.checked;
        var bankVal = bankSelect.value;
        var contactVal = contactIdInput.value;
        if (isBank && !bankVal) {
            e.preventDefault();
            alert('حساب بانکی را انتخاب کنید.');
            return false;
        }
        if (!isBank && !contactVal) {
            e.preventDefault();
            alert('مخاطب را جستجو و انتخاب کنید.');
            return false;
        }
        if (isBank) contactIdInput.removeAttribute('name');
        else bankSelect.removeAttribute('name');
    });

    if (contactIdInput.value && chosenDisplay.style.display !== 'inline-flex') {
        chosenName.textContent = contactSearch.value || chosenName.textContent;
        if (chosenName.textContent) {
            chosenDisplay.style.display = 'inline-flex';
            contactSearch.style.display = 'none';
        }
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
