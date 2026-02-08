@php
    $isEdit = isset($contact) && $contact->exists;
    $contactPhones = old('phones', $contact->contactPhones ?? collect());
    if (is_array($contactPhones)) {
        $contactPhones = collect($contactPhones)->map(fn ($p) => is_array($p) ? (object)$p : (object)['phone' => $p, 'label' => '']);
    }
    if ($contactPhones->isEmpty()) {
        $contactPhones = collect([(object)['phone' => '', 'label' => '']]);
    }
@endphp
@push('styles')
<style>
.contact-form .form-grid { display: grid; gap: 1.25rem; }
.contact-form .form-grid-2 { grid-template-columns: 1fr; }
.contact-form .form-grid-3 { grid-template-columns: 1fr; }
@media (min-width: 640px) {
  .contact-form .form-grid-2 { grid-template-columns: repeat(2, 1fr); }
  .contact-form .form-grid-3 { grid-template-columns: repeat(3, 1fr); }
}
.contact-form .form-span-full { grid-column: 1 / -1; }
.contact-form .form-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; padding-top: 1.5rem; margin-top: 1rem; border-top: 2px solid var(--ds-border); }
.contact-form .phone-row { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; }
.contact-form .phone-row input[placeholder="۰۹۱۲۳۴۵۶۷۸۹"] { flex: 1; min-width: 8rem; }
.contact-form .phone-row input[placeholder="موبایل/اداره"] { width: 100%; min-width: 0; }
@media (min-width: 480px) { .contact-form .phone-row input[placeholder="موبایل/اداره"] { width: 8rem; flex: none; } }
.contact-form .dropdown-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; max-height: 12rem; overflow: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.contact-form .dropdown-results a { display: block; padding: 0.75rem 1rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.contact-form .dropdown-results a:last-child { border-bottom: none; }
.contact-form .dropdown-results a:hover { background: var(--ds-bg-subtle); }
.contact-form .hidden { display: none !important; }
</style>
@endpush
<form action="{{ $isEdit ? route('contacts.update', $contact) : route('contacts.store') }}" method="post" class="contact-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اطلاعات اصلی</h2>
        <div class="form-grid form-grid-2">
            {{-- Name: full width, prominent --}}
            <div class="form-span-full">
                <label for="name" class="ds-label">نام <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $contact->name ?? '') }}" required
                       class="ds-input" placeholder="نام یا نام شرکت" style="min-height: 44px; font-size: 1rem;">
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Phones: full width --}}
            <div class="form-span-full">
                <label class="ds-label">شماره تلفن‌ها</label>
                <div id="phones-container" style="display: flex; flex-direction: column; gap: 0.5rem;">
                    @foreach ($contactPhones as $idx => $ph)
                        <div class="phone-row">
                            <input type="text" name="phones[{{ $idx }}][phone]" value="{{ old("phones.{$idx}.phone", $ph->phone ?? '') }}"
                                   class="ds-input" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" style="min-height: 44px;">
                            <input type="text" name="phones[{{ $idx }}][label]" value="{{ old("phones.{$idx}.label", $ph->label ?? '') }}"
                                   class="ds-input" placeholder="موبایل/اداره" style="min-height: 44px;">
                            <button type="button" class="remove-phone ds-btn ds-btn-danger" aria-label="حذف ردیف" style="min-width: 44px;">×</button>
                        </div>
                    @endforeach
                </div>
                <button type="button" id="add-phone" class="ds-btn ds-btn-dashed" style="margin-top: 0.5rem;">
                    + افزودن شماره
                </button>
            </div>

            {{-- Address: full width --}}
            <div class="form-span-full">
                <label for="address" class="ds-label">آدرس</label>
                <textarea name="address" id="address" rows="3" class="ds-textarea"
                          placeholder="آدرس پستی">{{ old('address', $contact->address ?? '') }}</textarea>
                @error('address')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- City | Website --}}
            <div>
                <label for="city" class="ds-label">شهر</label>
                <input type="text" name="city" id="city" value="{{ old('city', $contact->city ?? '') }}"
                       class="ds-input" placeholder="محل زندگی یا کار" style="min-height: 44px;">
            </div>
            <div>
                <label for="website" class="ds-label">وب‌سایت</label>
                <input type="url" name="website" id="website" value="{{ old('website', $contact->website ?? '') }}"
                       class="ds-input" placeholder="https://..." dir="ltr" style="min-height: 44px;">
                @error('website')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Social: 3 cols --}}
            <div>
                <label for="instagram" class="ds-label">اینستاگرام</label>
                <input type="text" name="instagram" id="instagram" value="{{ old('instagram', $contact->instagram ?? '') }}"
                       class="ds-input" placeholder="@username" dir="ltr" style="min-height: 44px;">
            </div>
            <div>
                <label for="telegram" class="ds-label">تلگرام</label>
                <input type="text" name="telegram" id="telegram" value="{{ old('telegram', $contact->telegram ?? '') }}"
                       class="ds-input" placeholder="@username" dir="ltr" style="min-height: 44px;">
            </div>
            <div>
                <label for="whatsapp" class="ds-label">واتساپ</label>
                <input type="text" name="whatsapp" id="whatsapp" value="{{ old('whatsapp', $contact->whatsapp ?? '') }}"
                       class="ds-input" placeholder="شماره" dir="ltr" style="min-height: 44px;">
            </div>

            {{-- Referrer --}}
            <div class="form-span-full">
                <label for="referrer_name" class="ds-label">معرف (شخص یا شرکت)</label>
                <input type="text" name="referrer_name" id="referrer_name" value="{{ old('referrer_name', $contact->referrer_name ?? '') }}"
                       class="ds-input" placeholder="نام معرف" style="min-height: 44px;">
            </div>

            {{-- Linked contact --}}
            <div class="form-span-full" style="position: relative;">
                <label for="linked_contact_search" class="ds-label">مخاطب مرتبط (شرکت / فروشگاه)</label>
                <input type="hidden" name="linked_contact_id" id="linked_contact_id" value="{{ old('linked_contact_id', $contact->linked_contact_id ?? '') }}">
                <input type="text" id="linked_contact_search" value="{{ old('linked_contact_name', $contact->linkedContact->name ?? '') }}"
                       class="ds-input" placeholder="جستجو نام مخاطب…" autocomplete="off" style="min-height: 44px;">
                <p style="margin-top: 0.25rem; font-size: 0.75rem; color: var(--ds-text-subtle);">مخاطبی از همین دفترچه را انتخاب کنید.</p>
                <div id="linked_contact_results" class="dropdown-results hidden"></div>
                @error('linked_contact_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- is_hamkar --}}
            <div class="form-span-full">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; min-height: 44px;">
                    <input type="checkbox" name="is_hamkar" value="1" {{ old('is_hamkar', $contact->is_hamkar ?? false) ? 'checked' : '' }}
                           style="width: 1.25rem; height: 1.25rem; accent-color: var(--ds-primary);">
                    <span style="font-size: 0.875rem; font-weight: 500; color: var(--ds-text-muted);">همکار (لینک به مخاطب شرکت/فروشگاه)</span>
                </label>
            </div>

            {{-- Notes --}}
            <div class="form-span-full">
                <label for="notes" class="ds-label">یادداشت</label>
                <textarea name="notes" id="notes" rows="3" class="ds-textarea"
                          placeholder="یادداشت‌ها">{{ old('notes', $contact->notes ?? '') }}</textarea>
            </div>

        </div>
    </div>

    @isset($tags)
    @include('components._tag-section', ['tags' => $tags, 'entity' => $contact ?? null])
    @endisset

    <div class="form-actions">
        <button type="submit" class="ds-btn ds-btn-primary">
            {{ $isEdit ? 'ذخیره تغییرات' : 'ذخیره مخاطب' }}
        </button>
        <a href="{{ route('contacts.index') }}" class="ds-btn ds-btn-outline">
            انصراف
        </a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var phoneIndex = {{ $contactPhones->count() }};
    document.getElementById('add-phone').addEventListener('click', function () {
        var container = document.getElementById('phones-container');
        var row = document.createElement('div');
        row.className = 'phone-row';
        row.innerHTML =
            '<input type="text" name="phones[' + phoneIndex + '][phone]" class="ds-input" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" style="min-height:44px">' +
            '<input type="text" name="phones[' + phoneIndex + '][label]" class="ds-input" placeholder="موبایل/اداره" style="min-height:44px">' +
            '<button type="button" class="remove-phone ds-btn ds-btn-danger" aria-label="حذف ردیف" style="min-width:44px">×</button>';
        container.appendChild(row);
        row.querySelector('.remove-phone').addEventListener('click', function () { row.remove(); });
        phoneIndex++;
    });
    document.getElementById('phones-container').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-phone')) e.target.closest('.phone-row')?.remove();
    });

    var linkedInput = document.getElementById('linked_contact_id');
    var linkedSearch = document.getElementById('linked_contact_search');
    var linkedResults = document.getElementById('linked_contact_results');
    var excludeId = {{ $isEdit ? (int)$contact->id : 'null' }};
    var debounce = null;
    linkedSearch.addEventListener('input', function () {
        if (linkedSearch.value.trim().length < 1) {
            linkedInput.value = '';
            linkedResults.classList.add('hidden');
            return;
        }
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            var q = encodeURIComponent(linkedSearch.value.trim());
            var url = '{{ route("contacts.search.api") }}?q=' + q + (excludeId ? '&exclude=' + excludeId : '');
            fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    linkedResults.innerHTML = '';
                    if (list.length === 0) {
                        linkedResults.classList.add('hidden');
                        return;
                    }
                    list.forEach(function (c) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = c.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            linkedInput.value = c.id;
                            linkedSearch.value = c.name;
                            linkedResults.classList.add('hidden');
                        });
                        linkedResults.appendChild(a);
                    });
                    linkedResults.classList.remove('hidden');
                });
        }, 200);
    });
    linkedSearch.addEventListener('blur', function () {
        setTimeout(function () { linkedResults.classList.add('hidden'); }, 150);
    });
    document.addEventListener('click', function (e) {
        if (!linkedSearch.contains(e.target) && !linkedResults.contains(e.target)) linkedResults.classList.add('hidden');
    });
})();
</script>
@endpush
