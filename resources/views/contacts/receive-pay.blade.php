@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'دریافت / پرداخت — ' . $contact->name)

@push('styles')
<style>
.receive-pay-page { font-family: 'Vazirmatn', sans-serif; max-width: 36rem; margin: 0 auto; }
.receive-pay-page .card { border: 2px solid #d6d3d1; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.receive-pay-page .card-title { border-bottom: 2px solid #e7e5e4; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #292524; }
.receive-pay-page label { display: block; font-weight: 600; color: #44403c; margin-bottom: 0.375rem; font-size: 0.875rem; }
.receive-pay-page input[type="text"], .receive-pay-page input[type="number"], .receive-pay-page select, .receive-pay-page textarea { width: 100%; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-family: 'Vazirmatn', sans-serif; font-size: 1rem; color: #292524; box-sizing: border-box; }
.receive-pay-page input:focus, .receive-pay-page select:focus, .receive-pay-page textarea:focus { outline: none; border-color: #059669; box-shadow: 0 0 0 3px rgba(5,150,105,0.2); }
.receive-pay-page .form-group { margin-bottom: 1.25rem; }
.receive-pay-page .text-muted { font-size: 0.8125rem; color: #78716c; margin-top: 0.25rem; }
.receive-pay-page .text-danger { color: #b91c1c; font-size: 0.875rem; margin-top: 0.25rem; }
.receive-pay-page .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0.625rem 1rem; border-radius: 0.75rem; font-family: 'Vazirmatn', sans-serif; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 2px solid transparent; cursor: pointer; transition: all 0.2s; }
.receive-pay-page .btn-primary { background: #059669; color: #fff !important; border-color: #047857; }
.receive-pay-page .btn-secondary { background: #fff; color: #44403c; border-color: #d6d3d1; }
.receive-pay-page .payment-method { display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 1rem; }
.receive-pay-page .payment-method input { display: none; }
.receive-pay-page .payment-method label { display: inline-flex; align-items: center; padding: 0.75rem 1.25rem; border: 2px solid #d6d3d1; border-radius: 0.75rem; cursor: pointer; margin: 0; font-weight: 600; transition: all 0.2s; }
.receive-pay-page .payment-method input:checked + label { border-color: #059669; background: #ecfdf5; color: #047857; }
.receive-pay-page .payment-method label:hover { border-color: #a7f3d0; }
.receive-pay-page .type-choice label[for="type_receive"] { border-color: #059669; }
.receive-pay-page .type-choice input:checked + label[for="type_receive"] { background: #ecfdf5; color: #047857; }
.receive-pay-page .type-choice label[for="type_pay"] { border-color: #0284c7; }
.receive-pay-page .type-choice input:checked + label[for="type_pay"] { background: #e0f2fe; color: #0369a1; }
.receive-pay-page .payment-panel { display: none; margin-top: 0.75rem; }
.receive-pay-page .payment-panel.active { display: block; }
.receive-pay-page .autocomplete-wrap { position: relative; }
.receive-pay-page .autocomplete-results { position: absolute; top: 100%; left: 0; right: 0; margin-top: 2px; background: #fff; border: 2px solid #d6d3d1; border-radius: 0.5rem; max-height: 280px; overflow-y: auto; z-index: 50; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.receive-pay-page .autocomplete-results li { padding: 0.625rem 0.75rem; cursor: pointer; border-bottom: 1px solid #e7e5e4; font-size: 0.9375rem; list-style: none; }
.receive-pay-page .autocomplete-results li:last-child { border-bottom: none; }
.receive-pay-page .autocomplete-results li:hover, .receive-pay-page .autocomplete-results li.selected { background: #ecfdf5; color: #047857; }
.receive-pay-page .chosen-contact { display: inline-flex; align-items: center; gap: 0.5rem; margin-top: 0.5rem; padding: 0.5rem 0.75rem; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 0.5rem; font-size: 0.875rem; }
.receive-pay-page .chosen-contact .clear-contact { color: #b91c1c; cursor: pointer; text-decoration: underline; }
.receive-pay-page .summary-row { display: flex; justify-content: space-between; align-items: center; padding: 0.5rem 0; border-bottom: 1px solid #e7e5e4; }
.receive-pay-page .summary-row:last-child { border-bottom: none; }
</style>
@endpush

@section('content')
<div class="receive-pay-page font-vazir">
    <div class="mb-6 flex flex-wrap items-center gap-3">
        <a href="{{ route('contacts.show', $contact) }}" class="btn btn-secondary">← بازگشت به مخاطب</a>
        <h1 class="text-xl font-bold text-stone-800 sm:text-2xl">دریافت / پرداخت</h1>
    </div>

    <div class="card" style="background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%); border-color: #a7f3d0;">
        <div class="card-title">مخاطب</div>
        <div class="summary-row">
            <span class="text-stone-600">نام</span>
            <span class="font-semibold text-stone-800">{{ $contact->name }}</span>
        </div>
        <div class="summary-row">
            <span class="text-stone-600">مانده فعلی</span>
            <span class="font-vazir font-semibold" style="{{ $contact->balance > 0 ? 'color:#047857;' : ($contact->balance < 0 ? 'color:#b45309;' : 'color:#78716c;') }}">{{ FormatHelper::rial(abs($contact->balance)) }} {{ $contact->balance > 0 ? '(بستانکار)' : ($contact->balance < 0 ? '(بدهکار)' : '') }}</span>
        </div>
    </div>

    <div class="card">
        <div class="card-title">جزئیات دریافت یا پرداخت</div>
        <form method="post" action="{{ route('contacts.receive-pay.submit', $contact) }}" id="receive-pay-form">
            @csrf
            <div class="form-group">
                <label>نوع <span style="color:#b91c1c;">*</span></label>
                <div class="payment-method type-choice">
                    <input type="radio" name="type" id="type_receive" value="receive" {{ old('type', 'receive') === 'receive' ? 'checked' : '' }}>
                    <label for="type_receive">دریافت از این مخاطب</label>
                    <input type="radio" name="type" id="type_pay" value="pay" {{ old('type') === 'pay' ? 'checked' : '' }}>
                    <label for="type_pay">پرداخت به این مخاطب</label>
                </div>
                <p class="text-muted">دریافت: این مخاطب به شما پرداخت کرده. پرداخت: شما به این مخاطب پرداخت کرده‌اید.</p>
            </div>
            <div class="form-group">
                <label>مبلغ (ریال) <span style="color:#b91c1c;">*</span></label>
                <input type="number" name="amount" id="amount" value="{{ old('amount') }}" min="1" required class="@error('amount') border-red-500 @enderror" dir="ltr" style="text-align:left;">
                @error('amount')<p class="text-danger">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label>تاریخ (شمسی) <span style="color:#b91c1c;">*</span></label>
                <div class="flex gap-2 flex-wrap items-center">
                    <input type="text" name="paid_at" id="paid_at" value="{{ old('paid_at', $defaultPaidAt) }}" placeholder="۱۴۰۳/۱۱/۱۳" required
                        class="flex-1 min-w-0 font-vazir @error('paid_at') border-red-500 @enderror" dir="rtl" style="font-family:'Vazirmatn',sans-serif;" inputmode="numeric" autocomplete="off">
                    <button type="button" id="paid_at_today" class="shrink-0 rounded-lg border-2 border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 min-h-[44px] transition" data-today="{{ $defaultPaidAt }}">امروز</button>
                </div>
                @error('paid_at')<p class="text-danger">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label>از طریق</label>
                <div class="payment-method">
                    <input type="radio" name="payment_type" id="pay_type_bank" value="bank" {{ old('payment_type', 'bank') === 'bank' ? 'checked' : '' }}>
                    <label for="pay_type_bank">حساب بانکی</label>
                    <input type="radio" name="payment_type" id="pay_type_contact" value="contact" {{ old('payment_type') === 'contact' ? 'checked' : '' }}>
                    <label for="pay_type_contact">از طریق مخاطب دیگر</label>
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
                    $oldCounterpartyId = old('counterparty_contact_id');
                    $oldCounterpartyName = $oldCounterpartyId ? (\App\Models\Contact::find($oldCounterpartyId)?->name ?? '') : '';
                @endphp
                <div id="panel-contact" class="payment-panel {{ old('payment_type') === 'contact' ? 'active' : '' }}">
                    <div class="autocomplete-wrap">
                        <input type="text" id="contact_search" placeholder="نام مخاطب را تایپ کنید (حداقل ۲ حرف)..." autocomplete="off" value="{{ $oldCounterpartyName }}">
                        <input type="hidden" name="counterparty_contact_id" id="counterparty_contact_id" value="{{ $oldCounterpartyId ?? '' }}">
                        <ul id="contact_results" class="autocomplete-results" style="display:none;"></ul>
                    </div>
                    <div id="chosen_contact_display" class="chosen-contact" style="{{ $oldCounterpartyId ? '' : 'display:none;' }}">
                        <span id="chosen_contact_name">{{ $oldCounterpartyName }}</span>
                        <button type="button" class="clear-contact" id="clear_contact">حذف</button>
                    </div>
                    <p class="text-muted">دریافت: این مخاطب به مخاطب انتخاب‌شده پرداخت کرده. پرداخت: شما از طریق مخاطب انتخاب‌شده به این مخاطب پرداخت کرده‌اید.</p>
                </div>
            </div>
            <div class="form-group">
                <label>یادداشت</label>
                <textarea name="notes" rows="2" placeholder="اختیاری">{{ old('notes') }}</textarea>
            </div>
            @error('payment_option_id')<p class="text-danger">{{ $message }}</p>@enderror
            @error('counterparty_contact_id')<p class="text-danger">{{ $message }}</p>@enderror
            <div class="flex flex-wrap items-center gap-3 mt-6">
                <button type="submit" class="btn btn-primary px-6 py-3" style="font-size:1rem;"><span style="color:#fff;">ثبت</span></button>
                <a href="{{ route('contacts.show', $contact) }}" class="btn btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function() {
    var form = document.getElementById('receive-pay-form');
    var payTypeBank = document.getElementById('pay_type_bank');
    var payTypeContact = document.getElementById('pay_type_contact');
    var panelBank = document.getElementById('panel-bank');
    var panelContact = document.getElementById('panel-contact');
    var bankSelect = document.getElementById('payment_option_id');
    var counterpartyInput = document.getElementById('counterparty_contact_id');
    var contactSearch = document.getElementById('contact_search');
    var contactResults = document.getElementById('contact_results');
    var chosenDisplay = document.getElementById('chosen_contact_display');
    var chosenName = document.getElementById('chosen_contact_name');
    var clearContactBtn = document.getElementById('clear_contact');
    var searchUrl = '{{ route("contacts.search.api") }}';
    var excludeId = {{ $contact->id }};
    var debounceTimer = null, selectedIndex = -1;

    function togglePanels() {
        if (payTypeBank.checked) {
            panelBank.classList.add('active');
            panelContact.classList.remove('active');
            counterpartyInput.value = '';
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
        counterpartyInput.value = '';
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
        var url = searchUrl + '?q=' + encodeURIComponent(q) + '&with_balance=1&exclude=' + excludeId;
        fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(list) {
                list = list.filter(function(c) { return c.id != excludeId; });
                contactResults.innerHTML = '';
                selectedIndex = -1;
                if (list.length === 0) {
                    contactResults.innerHTML = '<li class="no-results" style="padding:0.75rem;color:#78716c;">نتیجه‌ای یافت نشد</li>';
                } else {
                    list.forEach(function(c) {
                        var li = document.createElement('li');
                        li.setAttribute('data-id', c.id);
                        li.setAttribute('data-name', c.name);
                        var balanceText = (c.balance !== undefined) ? ' (مانده: ' + Number(c.balance).toLocaleString('fa-IR') + ')' : '';
                        li.textContent = c.name + balanceText;
                        li.addEventListener('click', function() {
                            counterpartyInput.value = c.id;
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
        if (counterpartyInput.value) {
            counterpartyInput.value = '';
            chosenDisplay.style.display = 'none';
        }
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function() { fetchContacts(q); }, 280);
    });
    contactSearch.addEventListener('focus', function() {
        if (this.value.trim().length >= 2 && contactResults.querySelector('li[data-id]')) contactResults.style.display = 'block';
    });
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.autocomplete-wrap')) contactResults.style.display = 'none';
    });

    form.addEventListener('submit', function(e) {
        var isBank = payTypeBank.checked;
        if (isBank && !bankSelect.value) {
            e.preventDefault();
            alert('حساب بانکی را انتخاب کنید.');
            return false;
        }
        if (!isBank && !counterpartyInput.value) {
            e.preventDefault();
            alert('مخاطب طرف معامله را جستجو و انتخاب کنید.');
            return false;
        }
        if (isBank) counterpartyInput.removeAttribute('name');
        else bankSelect.removeAttribute('name');
    });

    var paidAtInput = document.getElementById('paid_at');
    var paidAtTodayBtn = document.getElementById('paid_at_today');
    if (paidAtInput && paidAtTodayBtn && paidAtTodayBtn.dataset.today) {
        paidAtTodayBtn.addEventListener('click', function() {
            paidAtInput.value = this.dataset.today;
            paidAtInput.focus();
        });
    }

    if (counterpartyInput.value && chosenDisplay.style.display !== 'inline-flex') {
        chosenName.textContent = contactSearch.value || chosenName.textContent;
        if (chosenName.textContent) {
            chosenDisplay.style.display = 'inline-flex';
            contactSearch.style.display = 'none';
        }
    }
})();
</script>
@endpush
@endsection
