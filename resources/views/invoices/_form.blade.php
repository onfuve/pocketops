@php
    $isEdit = isset($invoice) && $invoice->exists;
    $invType = old('type', $invoice->type ?? 'sell');
    $isBuy = $invType === 'buy';
    $paymentOptions = $paymentOptions ?? collect();
    $selectedIds = $selectedIds ?? [];
    $paymentOptionFields = $paymentOptionFields ?? [];
    $items = old('items', $invoice->items ?? collect());
    if (is_array($items)) {
        $items = collect($items)->map(fn ($i) => is_array($i) ? (object) array_merge(['description'=>'','quantity'=>1,'unit_price'=>0,'amount'=>0,'sort'=>0], $i) : (object)['description'=>'','quantity'=>1,'unit_price'=>0,'amount'=>0,'sort'=>0]);
    }
    if ($items->isEmpty()) {
        $items = collect([(object)['description'=>'','quantity'=>1,'unit_price'=>0,'amount'=>0,'sort'=>0]]);
    }
    $contact = $contact ?? $invoice->contact ?? null;
    $dateShamsi = old('date') ? (str_contains(old('date'), '/') ? old('date') : \App\Helpers\FormatHelper::shamsi($invoice->date ?? now())) : \App\Helpers\FormatHelper::shamsi($invoice->date ?? now());
    $dueShamsi = old('due_date') ? (str_contains(old('due_date'), '/') ? old('due_date') : ($invoice->due_date ? \App\Helpers\FormatHelper::shamsi($invoice->due_date) : '')) : ($invoice->due_date ? \App\Helpers\FormatHelper::shamsi($invoice->due_date) : '');
    $shamsiToday = \App\Helpers\FormatHelper::shamsi(now());
    $baseDate = $invoice->date ?? now();
    $baseCarbon = $baseDate instanceof \DateTimeInterface ? \Carbon\Carbon::instance($baseDate) : \Carbon\Carbon::parse($baseDate);
    $duePresets = [
        '0' => \App\Helpers\FormatHelper::shamsi($baseCarbon),
        '3' => \App\Helpers\FormatHelper::shamsi($baseCarbon->copy()->addDays(3)),
        '7' => \App\Helpers\FormatHelper::shamsi($baseCarbon->copy()->addDays(7)),
        '30' => \App\Helpers\FormatHelper::shamsi($baseCarbon->copy()->addDays(30)),
    ];
@endphp
@push('styles')
<style>
.inv-form .form-grid-2 { display: grid; gap: 1.5rem; grid-template-columns: 1fr; }
@media (min-width: 640px) { .inv-form .form-grid-2 { grid-template-columns: repeat(2, 1fr); } }
.inv-form .col-span-2 { grid-column: 1 / -1; }
.inv-form .date-row { display: flex; gap: 0.75rem; }
.inv-form .date-row .ds-input { flex: 1; min-width: 0; }
.inv-form .due-presets { display: flex; flex-wrap: wrap; gap: 0.75rem; }
.inv-form .due-preset { padding: 0.5rem 0.875rem; border-radius: var(--ds-radius-sm); font-size: 0.875rem; font-weight: 500; border: 2px solid var(--ds-border); background: var(--ds-bg); color: var(--ds-text-muted); cursor: pointer; font-family: var(--ds-font); transition: all 0.2s; }
.inv-form .due-preset:hover { border-color: var(--ds-border-hover); background: var(--ds-bg-subtle); color: var(--ds-text); }
.inv-form .due-preset.active { border-color: var(--ds-text); background: var(--ds-bg-subtle); color: var(--ds-text); }
.inv-form .item-grid { display: grid; gap: 1rem; grid-template-columns: 1fr; }
@media (min-width: 640px) { .inv-form .item-grid { grid-template-columns: repeat(12, 1fr); } }
.inv-form .item-row { padding: 1.25rem; border-radius: var(--ds-radius-lg); border: 2px solid var(--ds-border); background: var(--ds-bg-muted); margin-bottom: 1rem; }
.inv-form .item-row .item-grid { display: grid; gap: 1rem; grid-template-columns: 1fr; }
@media (min-width: 640px) { .inv-form .item-row .item-grid { grid-template-columns: repeat(12, 1fr); } }
.inv-form .item-row .item-grid > div:first-child { grid-column: 1 / -1; }
.inv-form .item-row .sm-col-4 { grid-column: 1 / -1; }
.inv-form .item-row .sm-col-3 { grid-column: 1 / -1; }
@media (min-width: 640px) { .inv-form .item-row .sm-col-4 { grid-column: span 4; } .inv-form .item-row .sm-col-3 { grid-column: span 3; } }
.inv-form .dropdown-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; max-height: 12rem; overflow: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.inv-form .dropdown-results a { display: block; padding: 0.75rem 1rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.inv-form .dropdown-results a:last-child { border-bottom: none; }
.inv-form .dropdown-results a:hover { background: var(--ds-bg-subtle); }
.inv-form .hidden { display: none !important; }
</style>
@endpush
<form action="{{ $isEdit ? route('invoices.update', $invoice) : route('invoices.store') }}" method="post" class="inv-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <input type="hidden" name="type" value="{{ old('type', $invoice->type ?? 'sell') }}">

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">{{ $isBuy ? 'فروشنده' : 'مشتری' }} و تاریخ</h2>
        <div class="form-grid-2">
            <div class="col-span-2" style="position: relative;">
                <label for="contact_search" class="ds-label">{{ $isBuy ? 'فروشنده' : 'مشتری' }} <span style="color: #b91c1c;">*</span></label>
                <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id', $contact->id ?? '') }}">
                <input type="text" id="contact_search" value="{{ old('contact_name', $contact->name ?? '') }}"
                       class="ds-input" placeholder="{{ $isBuy ? 'جستجو نام فروشنده…' : 'جستجو نام مشتری…' }}" autocomplete="off">
                <div id="contact_results" class="dropdown-results hidden"></div>
            </div>
            <div>
                <label for="date" class="ds-label">تاریخ</label>
                <div class="date-row">
                    <input type="text" name="date" id="date" value="{{ $dateShamsi }}"
                           class="ds-input" placeholder="۱۴۰۳/۱۱/۱۴" autocomplete="off">
                    <button type="button" id="date-today" class="ds-btn ds-btn-secondary" data-today="{{ $shamsiToday }}">امروز</button>
                </div>
            </div>
            <div>
                <label class="ds-label">سررسید</label>
                @php
                    $normDue = \App\Helpers\FormatHelper::persianToEnglish($dueShamsi);
                    $dueActive = null;
                    if ($normDue === \App\Helpers\FormatHelper::persianToEnglish($duePresets['0'])) $dueActive = '0';
                    elseif ($normDue === \App\Helpers\FormatHelper::persianToEnglish($duePresets['3'])) $dueActive = '3';
                    elseif ($normDue === \App\Helpers\FormatHelper::persianToEnglish($duePresets['7'])) $dueActive = '7';
                    elseif ($normDue === \App\Helpers\FormatHelper::persianToEnglish($duePresets['30'])) $dueActive = '30';
                @endphp
                <div class="due-presets">
                    <button type="button" class="due-preset {{ $dueActive === '0' ? 'active' : '' }}" data-due="{{ $duePresets['0'] }}">نقدی</button>
                    <button type="button" class="due-preset {{ $dueActive === '3' ? 'active' : '' }}" data-due="{{ $duePresets['3'] }}">۳ روز</button>
                    <button type="button" class="due-preset {{ $dueActive === '7' ? 'active' : '' }}" data-due="{{ $duePresets['7'] }}">هفته</button>
                    <button type="button" class="due-preset {{ $dueActive === '30' ? 'active' : '' }}" data-due="{{ $duePresets['30'] }}">ماه</button>
                    <button type="button" class="due-preset due-other {{ !$dueActive && $dueShamsi !== '' ? 'active' : '' }}" data-due="">سایر</button>
                </div>
                <div style="margin-top: 1rem;">
                    <label for="due_date" class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">تاریخ سررسید</label>
                    <input type="text" name="due_date" id="due_date" value="{{ $dueShamsi }}"
                           class="ds-input" placeholder="۱۴۰۳/۱۱/۱۴ یا خالی" autocomplete="off">
                </div>
            </div>
            <div class="col-span-2">
                <label for="invoice_number" class="ds-label">{{ $isBuy ? 'شماره رسید' : 'شماره فاکتور' }}</label>
                <input type="text" name="invoice_number" id="invoice_number" value="{{ old('invoice_number', $invoice->invoice_number ?? '') }}"
                       class="ds-input" placeholder="خودکار در صورت خالی بودن">
            </div>
            @isset($users)
            <div class="col-span-2">
                <label for="assigned_to_id" class="ds-label">واگذاری به (اختیاری)</label>
                <select name="assigned_to_id" id="assigned_to_id" class="ds-select">
                    <option value="">— واگذار نشده —</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to_id', $invoice->assigned_to_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                <p style="margin-top: 0.25rem; font-size: 0.75rem; color: var(--ds-text-subtle);">با واگذاری، این عضو تیم نیز فاکتور را می‌بیند.</p>
            </div>
            @endisset
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اقلام</h2>
        <div id="items-container">
            @foreach ($items as $idx => $row)
                <div class="item-row">
                    <div class="item-grid">
                        <div class="item-desc">
                            <label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">شرح کالا یا خدمت</label>
                            <input type="text" name="items[{{ $idx }}][description]" value="{{ old("items.{$idx}.description", $row->description ?? '') }}"
                                   class="ds-input item-desc" placeholder="شرح کالا یا خدمت">
                        </div>
                        <div class="sm-col-4">
                            <label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">تعداد</label>
                            <input type="number" step="0.01" min="0" name="items[{{ $idx }}][quantity]" value="{{ old("items.{$idx}.quantity", $row->quantity ?? 1) }}"
                                   class="ds-input item-qty" dir="ltr">
                        </div>
                        <div class="sm-col-4">
                            <label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">قیمت واحد (ریال)</label>
                            <input type="number" min="0" name="items[{{ $idx }}][unit_price]" value="{{ old("items.{$idx}.unit_price", $row->unit_price ?? 0) }}"
                                   class="ds-input item-price" dir="ltr">
                        </div>
                        <div class="sm-col-3" style="display: flex; align-items: flex-end; gap: 0.75rem;">
                            <div style="flex: 1; min-width: 0;">
                                <label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">مبلغ (ریال)</label>
                                <input type="number" min="0" name="items[{{ $idx }}][amount]" value="{{ old("items.{$idx}.amount", $row->amount ?? 0) }}"
                                       class="ds-input item-amount" dir="ltr">
                            </div>
                            <button type="button" class="remove-item ds-btn ds-btn-danger" style="width: 2.5rem; height: 2.5rem; padding: 0; min-height: 2.5rem; display: inline-flex; align-items: center; justify-content: center;" aria-label="حذف ردیف">×</button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-item" class="ds-btn ds-btn-dashed" style="margin-top: 1.5rem; width: 100%;">
            + افزودن ردیف
        </button>
    </div>

    <div class="ds-form-card" style="background: var(--ds-bg-muted);">
        <h2 class="ds-form-card-title">جمع و پرداخت</h2>
        <div style="margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between; padding: 1rem 1.25rem; border-radius: var(--ds-radius-lg); background: var(--ds-bg); box-shadow: var(--ds-shadow);">
            <span style="font-size: 0.875rem; font-weight: 500; color: var(--ds-text-muted);">جمع کل (ریال)</span>
            <span id="form-subtotal" style="font-size: 1.125rem; font-weight: 700; color: var(--ds-text);">۰</span>
        </div>
        <div class="form-grid-2" style="grid-template-columns: 1fr 1fr; gap: 1.5rem;">
            <div>
                <label for="discount" class="ds-label">تخفیف (ریال)</label>
                <input type="number" min="0" name="discount" id="discount" value="{{ old('discount', $invoice->discount ?? 0) }}"
                       class="ds-input" dir="ltr">
            </div>
            <div style="padding: 1rem 1.25rem; border-radius: var(--ds-radius-lg); background: var(--ds-bg); box-shadow: var(--ds-shadow);">
                <p style="margin: 0 0 0.25rem 0; font-size: 0.75rem; font-weight: 500; color: var(--ds-text-subtle);">مبلغ قابل پرداخت (ریال)</p>
                <p id="form-total" style="font-size: 1.25rem; font-weight: 700; color: var(--ds-text); margin: 0;">۰</p>
            </div>
            <div class="col-span-2">
                <label for="notes" class="ds-label">یادداشت</label>
                <textarea name="notes" id="notes" rows="3" class="ds-textarea"
                          placeholder="اختیاری">{{ old('notes', $invoice->notes ?? '') }}</textarea>
            </div>
        </div>
        @if (!$isBuy)
        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid var(--ds-border);">
            <h3 class="ds-label" style="margin-bottom: 0.75rem; font-size: 0.875rem;">حساب و کارت (نمایش در چاپ فاکتور)</h3>
            <p style="margin: 0 0 1rem 0; font-size: 0.75rem; color: var(--ds-text-subtle);">حساب‌ها یا کارت‌هایی که در چاپ نمایش داده می‌شوند را انتخاب کنید. برای هر مورد می‌توانید جداگانه شماره کارت، شبا و شماره حساب را در چاپ لحاظ کنید.</p>
            @if ($paymentOptions->isEmpty())
                <p style="font-size: 0.875rem; color: var(--ds-text-subtle);">هنوز حسابی تعریف نشده. از <a href="{{ route('settings.payment-options') }}" style="color: var(--ds-primary); font-weight: 500;">تنظیمات کارت و حساب</a> اضافه کنید.</p>
            @else
                <div style="display: flex; flex-direction: column; gap: 1rem;">
                    @foreach ($paymentOptions as $opt)
                        @php
                            $selected = in_array($opt->id, old('payment_option_ids', $selectedIds));
                            $fields = $paymentOptionFields[$opt->id ?? ''] ?? [];
                            $printCard = $fields['print_card_number'] ?? $opt->print_card_number ?? true;
                            $printIban = $fields['print_iban'] ?? $opt->print_iban ?? true;
                            $printAccount = $fields['print_account_number'] ?? $opt->print_account_number ?? true;
                        @endphp
                        <div style="padding: 1rem; border-radius: var(--ds-radius-lg); border: 2px solid var(--ds-border); background: rgba(250,250,249,0.5);">
                            <label style="display: inline-flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem; cursor: pointer;">
                                <input type="checkbox" name="payment_option_ids[]" value="{{ $opt->id }}" {{ $selected ? 'checked' : '' }} class="payment-opt-include" data-opt-id="{{ $opt->id }}" style="accent-color: var(--ds-primary);">
                                <span style="font-size: 0.875rem; font-weight: 500; color: var(--ds-text-muted);">{{ $opt->label }}</span>
                                @if ($opt->holder_name)<span style="font-size: 0.75rem; color: var(--ds-text-subtle);">— {{ $opt->holder_name }}</span>@endif
                                @if ($opt->bank_name)<span style="font-size: 0.75rem; color: var(--ds-text-subtle);">· {{ $opt->bank_name }}</span>@endif
                            </label>
                            <div class="payment-opt-fields" style="margin-right: 1.5rem; display: flex; flex-wrap: wrap; gap: 1rem; font-size: 0.875rem; {{ $selected ? '' : 'opacity: 0.6; pointer-events: none;' }}" data-opt-id="{{ $opt->id }}">
                                <label style="display: inline-flex; align-items: center; gap: 0.375rem; cursor: pointer;">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_card_number]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_card_number]" value="1" {{ $printCard ? 'checked' : '' }} style="accent-color: var(--ds-primary);">
                                    <span>شماره کارت</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.375rem; cursor: pointer;">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_iban]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_iban]" value="1" {{ $printIban ? 'checked' : '' }} style="accent-color: var(--ds-primary);">
                                    <span>شبا</span>
                                </label>
                                <label style="display: inline-flex; align-items: center; gap: 0.375rem; cursor: pointer;">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_account_number]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_account_number]" value="1" {{ $printAccount ? 'checked' : '' }} style="accent-color: var(--ds-primary);">
                                    <span>شماره حساب</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <script>
                document.querySelectorAll('.payment-opt-include').forEach(function(cb){
                    cb.addEventListener('change', function(){
                        var id = this.getAttribute('data-opt-id');
                        var block = document.querySelector('.payment-opt-fields[data-opt-id="'+id+'"]');
                        if (block) {
                            block.classList.toggle('opacity-60', !this.checked);
                            block.classList.toggle('pointer-events-none', !this.checked);
                        }
                    });
                });
                </script>
            @endif
        </div>
        @endif
    </div>

    @isset($tags)
    @include('components._tag-section', ['tags' => $tags, 'entity' => $invoice ?? null])
    @endisset

    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 1rem; padding-top: 1.5rem; border-top: 2px solid var(--ds-border);">
        <button type="submit" class="ds-btn ds-btn-primary">
            {{ $isEdit ? 'ذخیره تغییرات' : ($isBuy ? 'ذخیره رسید' : 'ذخیره فاکتور') }}
        </button>
        <a href="{{ $isEdit ? route('invoices.show', $invoice) : route('invoices.index') }}" class="ds-btn ds-btn-outline">
            انصراف
        </a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var itemIndex = {{ $items->count() }};
    var container = document.getElementById('items-container');

    var persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    function toPersianNum(n) {
        return String(Math.round(n)).replace(/\d/g, function(d) { return persianDigits[+d]; });
    }
    function updateAmount(row) {
        var qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        var price = parseInt(row.querySelector('.item-price').value, 10) || 0;
        var amount = Math.round(qty * price);
        row.querySelector('.item-amount').value = amount;
        updateFormTotals();
    }
    function updateFormTotals() {
        var subtotal = 0;
        container.querySelectorAll('.item-row').forEach(function (row) {
            var amt = parseInt(row.querySelector('.item-amount').value, 10) || 0;
            subtotal += amt;
        });
        var discount = parseInt(document.getElementById('discount').value, 10) || 0;
        var total = Math.max(0, subtotal - discount);
        document.getElementById('form-subtotal').textContent = toPersianNum(subtotal);
        document.getElementById('form-total').textContent = toPersianNum(total);
    }

    container.querySelectorAll('.item-row').forEach(function (row) {
        updateAmount(row);
        row.querySelectorAll('.item-qty, .item-price, .item-amount').forEach(function (el) {
            el.addEventListener('input', function () { updateAmount(row); });
        });
        row.querySelector('.remove-item').addEventListener('click', function () {
            if (container.querySelectorAll('.item-row').length > 1) { row.remove(); updateFormTotals(); }
        });
    });
    document.getElementById('discount').addEventListener('input', updateFormTotals);

    document.getElementById('add-item').addEventListener('click', function () {
        var row = document.createElement('div');
        row.className = 'item-row';
        row.innerHTML =
            '<div class="item-grid">' +
            '<div class="item-desc"><label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">شرح کالا یا خدمت</label><input type="text" name="items[' + itemIndex + '][description]" class="ds-input item-desc" placeholder="شرح کالا یا خدمت"></div>' +
            '<div class="sm-col-4"><label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">تعداد</label><input type="number" step="0.01" min="0" name="items[' + itemIndex + '][quantity]" value="1" class="ds-input item-qty" dir="ltr"></div>' +
            '<div class="sm-col-4"><label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">قیمت واحد (ریال)</label><input type="number" min="0" name="items[' + itemIndex + '][unit_price]" value="0" class="ds-input item-price" dir="ltr"></div>' +
            '<div class="sm-col-3" style="display: flex; align-items: flex-end; gap: 0.75rem;"><div style="flex: 1; min-width: 0;"><label class="ds-label" style="font-size: 0.75rem; color: var(--ds-text-subtle);">مبلغ (ریال)</label><input type="number" min="0" name="items[' + itemIndex + '][amount]" value="0" class="ds-input item-amount" dir="ltr"></div><button type="button" class="remove-item ds-btn ds-btn-danger" style="width: 2.5rem; height: 2.5rem; padding: 0; min-height: 2.5rem; display: inline-flex; align-items: center; justify-content: center;" aria-label="حذف ردیف">×</button></div>' +
            '</div>';
        container.appendChild(row);
        updateAmount(row);
        row.querySelector('.item-qty').addEventListener('input', function () { updateAmount(row); });
        row.querySelector('.item-price').addEventListener('input', function () { updateAmount(row); });
        row.querySelector('.item-amount').addEventListener('input', updateFormTotals);
        row.querySelector('.remove-item').addEventListener('click', function () {
            if (container.querySelectorAll('.item-row').length > 1) { row.remove(); updateFormTotals(); }
        });
        itemIndex++;
        updateFormTotals();
    });

    var contactInput = document.getElementById('contact_id');
    var contactSearch = document.getElementById('contact_search');
    var contactResults = document.getElementById('contact_results');
    var debounce = null;
    contactSearch.addEventListener('input', function () {
        if (contactSearch.value.trim().length < 1) {
            contactInput.value = '';
            contactResults.classList.add('hidden');
            return;
        }
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            var q = encodeURIComponent(contactSearch.value.trim());
            fetch('{{ route("contacts.search.api") }}?q=' + q, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    contactResults.innerHTML = '';
                    if (list.length === 0) { contactResults.classList.add('hidden'); return; }
                    list.forEach(function (c) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = c.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            contactInput.value = c.id;
                            contactSearch.value = c.name;
                            contactResults.classList.add('hidden');
                        });
                        contactResults.appendChild(a);
                    });
                    contactResults.classList.remove('hidden');
                });
        }, 200);
    });
    contactSearch.addEventListener('blur', function () { setTimeout(function () { contactResults.classList.add('hidden'); }, 150); });

    var dateInput = document.getElementById('date');
    var dateTodayBtn = document.getElementById('date-today');
    if (dateTodayBtn) {
        dateTodayBtn.addEventListener('click', function () {
            dateInput.value = this.dataset.today;
        });
    }

    var dueInput = document.getElementById('due_date');
    // Due date presets
    var duePresets = document.querySelectorAll('.due-preset');
    duePresets.forEach(function (btn) {
        btn.addEventListener('click', function () {
            duePresets.forEach(function (b) { b.classList.remove('active'); });
            if (this.classList.contains('due-other')) {
                dueInput.focus();
                return;
            }
            this.classList.add('active');
            dueInput.value = this.dataset.due || '';
        });
    });
})();
</script>
@endpush
