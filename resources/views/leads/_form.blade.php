@php
    use App\Helpers\FormatHelper;
    use App\Models\Lead;
    $isEdit = isset($lead) && $lead->exists;
    $leadDateShamsi = old('lead_date') ? (str_contains(old('lead_date'), '/') ? old('lead_date') : FormatHelper::shamsi($lead->lead_date ?? now())) : ($lead->lead_date ? FormatHelper::shamsi($lead->lead_date) : FormatHelper::shamsi(now()));
@endphp
@push('styles')
<style>
.lead-form .form-grid { display: grid; gap: 1.25rem; }
.lead-form .span-2 { grid-column: span 2; }
.lead-form .span-full { grid-column: 1 / -1; }
@media (min-width: 640px) { .lead-form .form-grid-2 { grid-template-columns: repeat(2, 1fr); } }
@media (min-width: 1024px) { .lead-form .form-grid-3 { grid-template-columns: repeat(3, 1fr); } .lead-form .form-grid-4 { grid-template-columns: repeat(4, 1fr); } }
.lead-form .form-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; padding-top: 1.5rem; margin-top: 0.5rem; border-top: 2px solid var(--ds-border); }
.lead-form .date-row { display: flex; gap: 0.5rem; }
.lead-form .date-row input { flex: 1; min-width: 0; }
.lead-form .hidden { display: none !important; }
.lead-form .dropdown-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; max-height: 12rem; overflow: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.lead-form .dropdown-results a { display: block; padding: 0.75rem 1rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.lead-form .dropdown-results a:last-child { border-bottom: none; }
.lead-form .dropdown-results a:hover { background: var(--ds-bg-subtle); }
</style>
@endpush
<form action="{{ $isEdit ? route('leads.update', $lead) : route('leads.store') }}" method="post" class="lead-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    {{-- Block 1: اصلی --}}
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اطلاعات اصلی</h2>
        <div class="form-grid form-grid-3">
            <div class="span-2" style="position: relative;">
                <label for="name" class="ds-label">نام طرف مقابل یا نام شرکت <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $lead->name ?? '') }}" required autocomplete="off"
                       class="ds-input" placeholder="شروع به تایپ کنید — از لیست مخاطبین پیشنهاد می‌شود">
                <div id="name_contact_results" class="dropdown-results hidden"></div>
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="phone" class="ds-label">تلفن</label>
                <input type="text" name="phone" id="phone" value="{{ old('phone', $lead->phone ?? '') }}"
                       class="ds-input" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr">
            </div>
            <div>
                <label for="company" class="ds-label">شرکت</label>
                <input type="text" name="company" id="company" value="{{ old('company', $lead->company ?? '') }}"
                       class="ds-input" placeholder="اختیاری">
            </div>
            <div class="span-full">
                <label for="email" class="ds-label">ایمیل</label>
                <input type="email" name="email" id="email" value="{{ old('email', $lead->email ?? '') }}"
                       class="ds-input" placeholder="example@domain.com" dir="ltr">
            </div>
        </div>
    </div>

    {{-- Block 2: مرحله و تاریخ --}}
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">مرحله و ارزش</h2>
        <div class="form-grid form-grid-4">
            <div>
                <label for="status" class="ds-label">مرحله</label>
                <select name="status" id="status" class="ds-select">
                    @foreach (Lead::statusLabels() as $value => $label)
                        <option value="{{ $value }}" {{ old('status', $lead->status ?? Lead::STATUS_NEW) === $value ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="lead_date" class="ds-label">تاریخ سرنخ</label>
                <div class="date-row">
                    <input type="text" name="lead_date" id="lead_date" value="{{ $leadDateShamsi }}"
                           class="ds-input" placeholder="امروز" autocomplete="off">
                    <button type="button" id="lead_date_today" class="ds-btn ds-btn-secondary" data-today="{{ FormatHelper::shamsi(now()) }}">امروز</button>
                </div>
            </div>
            <div>
                <label for="value" class="ds-label">ارزش (ریال)</label>
                <input type="number" name="value" id="value" value="{{ old('value', $lead->value ?? '') }}" min="0"
                       class="ds-input" placeholder="اختیاری" dir="ltr">
            </div>
            @isset($leadChannels)
            @php
                $selectedChannelId = old('lead_channel_id', $lead->lead_channel_id);
                $selectedChannel = $leadChannels->firstWhere('id', $selectedChannelId);
                $showReferrerBlock = $selectedChannel && $selectedChannel->is_referral;
            @endphp
            <div>
                <label for="lead_channel_id" class="ds-label">کانال ورود</label>
                <select name="lead_channel_id" id="lead_channel_id" class="ds-select">
                    <option value="">— انتخاب کنید —</option>
                    @foreach ($leadChannels as $ch)
                        <option value="{{ $ch->id }}" data-is-referral="{{ $ch->is_referral ? '1' : '0' }}" {{ old('lead_channel_id', $lead->lead_channel_id ?? '') == $ch->id ? 'selected' : '' }}>{{ $ch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div id="referrer_block" class="span-full {{ $showReferrerBlock ? '' : 'hidden' }}" style="position: relative;">
                <label for="referrer_display" class="ds-label">معرف (مخاطب) <span style="color: #b91c1c;">*</span></label>
                <input type="text" id="referrer_display" autocomplete="off" value="{{ old('referrer_display', $lead->referrerContact?->name ?? '') }}"
                       class="ds-input" placeholder="جستجو نام مخاطب معرف…">
                <input type="hidden" name="referrer_contact_id" id="referrer_contact_id" value="{{ old('referrer_contact_id', $lead->referrer_contact_id ?? '') }}">
                <div id="referrer_results" class="dropdown-results hidden"></div>
                @error('referrer_contact_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endisset
            @isset($users)
            <div class="span-full">
                <label for="assigned_to_id" class="ds-label">واگذاری به (اختیاری)</label>
                <select name="assigned_to_id" id="assigned_to_id" class="ds-select">
                    <option value="">— واگذار نشده —</option>
                    @foreach ($users as $u)
                        <option value="{{ $u->id }}" {{ old('assigned_to_id', $lead->assigned_to_id ?? '') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
                <p style="margin-top: 0.25rem; font-size: 0.75rem; color: #78716c;">با واگذاری، این عضو تیم نیز سرنخ را می‌بیند.</p>
            </div>
            @endisset
            <div class="span-full">
                <label for="source" class="ds-label">منبع / توضیح (اختیاری)</label>
                <input type="text" name="source" id="source" value="{{ old('source', $lead->source ?? '') }}"
                       class="ds-input" placeholder="توضیح تکمیلی منبع…">
            </div>
        </div>
    </div>

    {{-- Block 3: جزئیات --}}
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">جزئیات / یادداشت</h2>
        <textarea name="details" id="details" rows="4" class="ds-textarea" placeholder="نیازها، توضیحات، یادداشت…">{{ old('details', $lead->details ?? '') }}</textarea>
    </div>

    @isset($tags)
    @include('components._tag-section', ['tags' => $tags, 'entity' => $lead ?? null, 'accentColor' => '#059669'])
    @endisset

    <div class="form-actions">
        <button type="submit" class="ds-btn ds-btn-primary">
            {{ $isEdit ? 'ذخیره تغییرات' : 'ذخیره سرنخ' }}
        </button>
        @if (!$isEdit)
            <button type="submit" name="add_another" value="1" class="ds-btn ds-btn-ghost">
                ذخیره و سرنخ بعدی
            </button>
        @endif
        <a href="{{ $isEdit ? route('leads.show', $lead) : route('leads.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('lead_date_today');
    var input = document.getElementById('lead_date');
    if (btn && input) {
        btn.addEventListener('click', function () {
            input.value = this.getAttribute('data-today') || '';
        });
    }

    var channelSelect = document.getElementById('lead_channel_id');
    var referrerBlock = document.getElementById('referrer_block');
    var referrerDisplay = document.getElementById('referrer_display');
    var referrerIdInput = document.getElementById('referrer_contact_id');
    var referrerResults = document.getElementById('referrer_results');
    if (channelSelect && referrerBlock) {
        function toggleReferrer() {
            var opt = channelSelect.options[channelSelect.selectedIndex];
            var isReferral = opt && opt.getAttribute('data-is-referral') === '1';
            if (isReferral) {
                referrerBlock.classList.remove('hidden');
            } else {
                referrerBlock.classList.add('hidden');
                if (referrerIdInput) referrerIdInput.value = '';
                if (referrerDisplay) referrerDisplay.value = '';
            }
        }
        channelSelect.addEventListener('change', toggleReferrer);
        toggleReferrer();
    }

    var referrerDebounce = null;
    if (referrerDisplay && referrerIdInput && referrerResults) {
        referrerDisplay.addEventListener('input', function () {
            var q = referrerDisplay.value.trim();
            if (q.length < 1) {
                referrerResults.classList.add('hidden');
                referrerResults.innerHTML = '';
                referrerIdInput.value = '';
                return;
            }
            clearTimeout(referrerDebounce);
            referrerDebounce = setTimeout(function () {
                fetch('{{ route("contacts.search.api") }}?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        referrerResults.innerHTML = '';
                        if (list.length === 0) {
                            referrerResults.classList.add('hidden');
                            return;
                        }
                        list.forEach(function (c) {
                            var a = document.createElement('a');
                            a.href = '#';
                            a.textContent = c.name;
                            a.addEventListener('click', function (e) {
                                e.preventDefault();
                                referrerDisplay.value = c.name;
                                referrerIdInput.value = c.id;
                                referrerResults.classList.add('hidden');
                                referrerResults.innerHTML = '';
                            });
                            referrerResults.appendChild(a);
                        });
                        referrerResults.classList.remove('hidden');
                    });
            }, 250);
        });
        referrerDisplay.addEventListener('blur', function () {
            setTimeout(function () { referrerResults.classList.add('hidden'); }, 200);
        });
    }

    var nameInput = document.getElementById('name');
    var nameResults = document.getElementById('name_contact_results');
    var phoneInput = document.getElementById('phone');
    var debounce = null;
    if (nameInput && nameResults) {
        nameInput.addEventListener('input', function () {
            var q = nameInput.value.trim();
            if (q.length < 1) {
                nameResults.classList.add('hidden');
                nameResults.innerHTML = '';
                return;
            }
            clearTimeout(debounce);
            debounce = setTimeout(function () {
                fetch('{{ route("contacts.search.api") }}?q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        nameResults.innerHTML = '';
                        if (list.length === 0) {
                            nameResults.classList.add('hidden');
                            return;
                        }
                        list.forEach(function (c) {
                            var a = document.createElement('a');
                            a.href = '#';
                            a.textContent = c.name;
                            a.addEventListener('click', function (e) {
                                e.preventDefault();
                                nameInput.value = c.name;
                                nameResults.classList.add('hidden');
                                nameResults.innerHTML = '';
                                if (phoneInput) {
                                    fetch('{{ url("/api/contacts") }}/' + c.id, {
                                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                                    })
                                        .then(function (r) { return r.json(); })
                                        .then(function (data) {
                                            if (data.first_phone) {
                                                phoneInput.value = data.first_phone;
                                            }
                                        });
                                }
                            });
                            nameResults.appendChild(a);
                        });
                        nameResults.classList.remove('hidden');
                    });
            }, 250);
        });
        nameInput.addEventListener('blur', function () {
            setTimeout(function () { nameResults.classList.add('hidden'); }, 200);
        });
    }
})();
</script>
@endpush
