@php
    use App\Helpers\FormatHelper;
    $isEdit = isset($subscription) && $subscription->exists;
    $contact = $subscription->contact ?? $contact ?? null;
    $startShamsi = old('start_date') ?: ($subscription->start_date ? FormatHelper::shamsi($subscription->start_date) : '');
    $expiryShamsi = old('expiry_date') ?: ($subscription->expiry_date ? FormatHelper::shamsi($subscription->expiry_date) : '');
    $shamsiToday = FormatHelper::shamsi(now());
    $categories = config('subscription.categories', []);
    $billingCycles = config('subscription.billing_cycles', []);
    $paymentStatuses = config('subscription.payment_statuses', []);
    $reminderOptions = config('subscription.reminder_days_before_options', []);
@endphp
@push('styles')
<style>
.sub-form .form-grid-2 { display: grid; gap: 1.25rem; grid-template-columns: 1fr; }
@media (min-width: 640px) { .sub-form .form-grid-2 { grid-template-columns: repeat(2, 1fr); } }
.sub-form .col-span-2 { grid-column: 1 / -1; }
.sub-form .dropdown-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; max-height: 12rem; overflow: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.sub-form .dropdown-results a { display: block; padding: 0.75rem 1rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.sub-form .dropdown-results a:last-child { border-bottom: none; }
.sub-form .dropdown-results a:hover { background: var(--ds-bg-subtle); }
.sub-form .hidden { display: none !important; }
.sub-form .date-row { display: flex; gap: 0.5rem; align-items: center; }
.sub-form .date-row .ds-input { flex: 1; min-width: 0; }
</style>
@endpush
<form action="{{ $isEdit ? route('subscriptions.update', $subscription) : route('subscriptions.store') }}" method="post" class="sub-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

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

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">مشتری و سرویس</h2>
        <div class="form-grid-2">
            <div class="col-span-2" style="position: relative;">
                <label for="contact_search" class="ds-label">مشتری <span style="color: #b91c1c;">*</span></label>
                <input type="hidden" name="contact_id" id="contact_id" value="{{ old('contact_id', $contact->id ?? '') }}">
                <input type="text" id="contact_search" value="{{ old('contact_name', $contact->name ?? '') }}"
                       class="ds-input {{ $errors->has('contact_id') ? 'border-red-500' : '' }}" placeholder="جستجو نام مشتری…" autocomplete="off">
                <div id="contact_results" class="dropdown-results hidden"></div>
                @error('contact_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <label for="service_name" class="ds-label">نام سرویس <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="service_name" id="service_name" value="{{ old('service_name', $subscription->service_name ?? '') }}"
                       class="ds-input" placeholder="مثال: Cloud VPS، VPN Plan، Google Workspace" required>
                @error('service_name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="category" class="ds-label">دسته‌بندی <span style="color: #b91c1c;">*</span></label>
                <select name="category" id="category" class="ds-select" required>
                    @foreach ($categories as $key => $label)
                        <option value="{{ $key }}" {{ old('category', $subscription->category ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="billing_cycle" class="ds-label">دوره پرداخت <span style="color: #b91c1c;">*</span></label>
                <select name="billing_cycle" id="billing_cycle" class="ds-select" required>
                    @foreach ($billingCycles as $key => $label)
                        <option value="{{ $key }}" {{ old('billing_cycle', $subscription->billing_cycle ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('billing_cycle')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <label for="description" class="ds-label">توضیحات</label>
                <textarea name="description" id="description" rows="2" class="ds-textarea" placeholder="شرح سرویس">{{ old('description', $subscription->description ?? '') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">تاریخ و مبلغ</h2>
        <div class="form-grid-2">
            <div>
                <label for="start_date" class="ds-label">تاریخ شروع <span style="color: #b91c1c;">*</span></label>
                <div class="date-row">
                    <input type="text" name="start_date" id="start_date" value="{{ $startShamsi }}"
                           class="ds-input {{ $errors->has('start_date') ? 'border-red-500' : '' }}" placeholder="۱۴۰۳/۱۱/۱۵" autocomplete="off">
                    <button type="button" id="start-today" class="ds-btn ds-btn-secondary" data-today="{{ $shamsiToday }}">امروز</button>
                </div>
                @error('start_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="expiry_date" class="ds-label">تاریخ انقضا <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="expiry_date" id="expiry_date" value="{{ $expiryShamsi }}"
                       class="ds-input {{ $errors->has('expiry_date') ? 'border-red-500' : '' }}" placeholder="۱۴۰۴/۱۱/۱۵" autocomplete="off">
                @error('expiry_date')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="price" class="ds-label">مبلغ (ریال) <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="price" id="price" value="{{ old('price', $subscription->price ?? '') }}"
                       class="ds-input" placeholder="۰" dir="ltr" required>
                @error('price')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="cost" class="ds-label">هزینه (ریال) — اختیاری</label>
                <input type="text" name="cost" id="cost" value="{{ old('cost', $subscription->cost !== null ? $subscription->cost : '') }}"
                       class="ds-input" placeholder="خالی = بدون محاسبه سود" dir="ltr">
                @error('cost')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="payment_status" class="ds-label">وضعیت پرداخت <span style="color: #b91c1c;">*</span></label>
                <select name="payment_status" id="payment_status" class="ds-select" required>
                    @foreach ($paymentStatuses as $key => $label)
                        <option value="{{ $key }}" {{ old('payment_status', $subscription->payment_status ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('payment_status')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="ds-label">تمدید خودکار</label>
                <div class="flex items-center gap-2" style="min-height: 44px;">
                    <input type="checkbox" name="auto_renewal" id="auto_renewal" value="1" {{ old('auto_renewal', $subscription->auto_renewal ?? false) ? 'checked' : '' }} class="ds-input">
                    <label for="auto_renewal" class="text-sm">بله</label>
                </div>
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">یادآوری و تکمیلی</h2>
        <div class="form-grid-2">
            <div>
                <label for="reminder_days_before" class="ds-label">یادآوری قبل از انقضا</label>
                <select name="reminder_days_before" id="reminder_days_before" class="ds-select">
                    @foreach ($reminderOptions as $key => $label)
                        <option value="{{ $key ?? '' }}" {{ (string) old('reminder_days_before', $subscription->reminder_days_before ?? '') === (string) $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="assigned_to_id" class="ds-label">مسئول پیگیری</label>
                <select name="assigned_to_id" id="assigned_to_id" class="ds-select">
                    <option value="">—</option>
                    @foreach ($users ?? [] as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to_id', $subscription->assigned_to_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2">
                <label for="supplier" class="ds-label">تأمین‌کننده / فروشنده</label>
                <input type="text" name="supplier" id="supplier" value="{{ old('supplier', $subscription->supplier ?? '') }}"
                       class="ds-input" placeholder="نام شرکت یا پنل">
                @error('supplier')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <label for="account_credentials" class="ds-label">اطلاعات ورود (محفوظ)</label>
                <input type="password" name="account_credentials" id="account_credentials" value=""
                       class="ds-input" placeholder="{{ $isEdit ? 'خالی بگذارید تا تغییر نکرده باشد' : 'نام کاربری / رمز / لینک' }}" autocomplete="off">
                <p class="mt-1 text-xs text-stone-500">ذخیره رمزنگاری‌شده. در ویرایش برای حفظ مقدار فعلی خالی بگذارید.</p>
                @error('account_credentials')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="col-span-2">
                <label for="notes" class="ds-label">یادداشت</label>
                <textarea name="notes" id="notes" rows="3" class="ds-textarea" placeholder="یادداشت داخلی">{{ old('notes', $subscription->notes ?? '') }}</textarea>
                @error('notes')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    <div class="form-grid-2" style="margin-top: 1.5rem;">
        <div class="col-span-2 flex flex-wrap items-center gap-3">
            <button type="submit" class="ds-btn ds-btn-primary">
                {{ $isEdit ? 'ذخیره تغییرات' : 'ثبت اشتراک' }}
            </button>
            <a href="{{ $isEdit ? route('subscriptions.show', $subscription) : route('subscriptions.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
        </div>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var contactInput = document.getElementById('contact_id');
    var contactSearch = document.getElementById('contact_search');
    var contactResults = document.getElementById('contact_results');
    var debounce = null;
    if (contactSearch && contactResults) {
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
    }
    var startToday = document.getElementById('start-today');
    if (startToday) {
        startToday.addEventListener('click', function () {
            document.getElementById('start_date').value = this.dataset.today;
        });
    }
})();
</script>
@endpush
