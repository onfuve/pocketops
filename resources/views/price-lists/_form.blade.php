@php
    $sections = old('sections', []);
    if (empty($sections) && isset($priceList) && $priceList->exists) {
        $sections = $priceList->sections->map(fn ($s) => [
            'name' => $s->name,
            'items' => $s->items->map(fn ($i) => [
                'product_id' => $i->product_id,
                'custom_name' => $i->custom_name,
                'custom_description' => $i->custom_description,
                'unit_price' => $i->unit_price ? \App\Helpers\FormatHelper::numberFormat($i->unit_price) : '',
                'unit' => $i->unit,
                'badge' => $i->badge ?? '',
            ])->values()->all(),
        ])->values()->all();
    }
    if (empty($sections)) {
        $sections = [['name' => '', 'items' => []]];
    }
    $products = $products ?? collect();
@endphp
@push('styles')
<style>
.pl-form .section-block { border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1.25rem; margin-bottom: 1.25rem; background: linear-gradient(to bottom, var(--ds-bg-muted) 0%, var(--ds-bg) 100%); }
.pl-form .section-header { display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; }
.pl-form .section-header input { flex: 1; min-width: 0; }
.pl-form .item-row { display: grid; gap: 1rem; grid-template-columns: 1fr auto; align-items: start; margin-bottom: 0.75rem; padding: 1rem; background: var(--ds-bg); border-radius: var(--ds-radius); border: 1px solid var(--ds-border); box-shadow: 0 1px 2px rgba(0,0,0,0.04); }
@media (min-width: 768px) { .pl-form .item-row { grid-template-columns: 1fr 10rem 8rem auto; } }
.pl-form .item-row .item-name-wrap { min-width: 0; position: relative; }
.pl-form .item-row .item-price { min-width: 0; }
.pl-form .item-row .item-badge { min-width: 0; }
.pl-form .item-row .item-badge .ds-select { min-height: 32px; padding: 0.25rem 0.4rem; font-size: 0.75rem; max-width: 6.5rem; }
.pl-form .item-product-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 25; max-height: 11rem; overflow-y: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.pl-form .item-product-results a { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 0.875rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.pl-form .item-product-results a:last-child { border-bottom: none; }
.pl-form .item-product-results a:hover { background: var(--ds-bg-subtle); }
.pl-form .item-product-results a.already-added .item-name-part { text-decoration: line-through; color: var(--ds-text-subtle); }
.pl-form .item-product-results a.already-added .item-tick { color: #059669; font-weight: bold; flex-shrink: 0; }
.pl-form .add-section-btn, .pl-form .add-item-btn { font-size: 0.875rem; }
.pl-form .hidden { display: none !important; }
</style>
@endpush
<form id="price-list-form" action="{{ route('price-lists.update', $priceList) }}" method="post" class="pl-form">
    @csrf
    @method('PUT')

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اطلاعات اصلی</h2>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label for="name" class="ds-label">نام لیست قیمت <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $priceList->name) }}" required class="ds-input">
                @error('name')<p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title_text" class="ds-label">عنوان نمایشی (اختیاری)</label>
                <input type="text" name="title_text" id="title_text" value="{{ old('title_text', $priceList->title_text) }}" class="ds-input">
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_prices" value="1" {{ old('show_prices', $priceList->show_prices) ? 'checked' : '' }}>
                    نمایش قیمت‌ها
                </label>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_photos" value="1" {{ old('show_photos', $priceList->show_photos) ? 'checked' : '' }}>
                    نمایش عکس کالاها
                </label>
            </div>
            <div>
                <label for="price_format" class="ds-label">فرمت قیمت</label>
                <select name="price_format" id="price_format" class="ds-select">
                    <option value="rial" {{ old('price_format', $priceList->price_format ?? 'rial') === 'rial' ? 'selected' : '' }}>ریال</option>
                    <option value="toman" {{ old('price_format', $priceList->price_format ?? '') === 'toman' ? 'selected' : '' }}>تومان</option>
                    <option value="none" {{ old('price_format', $priceList->price_format ?? '') === 'none' ? 'selected' : '' }}>فقط عدد (بدون واحد)</option>
                </select>
                <p style="margin-top: 0.25rem; font-size: 0.75rem; color: var(--ds-text-subtle);">نمایش قیمت به صورت ریال، تومان یا فقط عدد</p>
            </div>
            <div>
                <label for="template" class="ds-label">قالب</label>
                <select name="template" id="template" class="ds-select">
                    <option value="simple" {{ old('template', $priceList->template) === 'simple' ? 'selected' : '' }}>ساده</option>
                    <option value="with_photos" {{ old('template', $priceList->template) === 'with_photos' ? 'selected' : '' }}>با عکس</option>
                    <option value="grid" {{ old('template', $priceList->template) === 'grid' ? 'selected' : '' }}>شبکه‌ای</option>
                </select>
            </div>
            <div>
                <label for="font_family" class="ds-label">فونت فارسی</label>
                <select name="font_family" id="font_family" class="ds-select">
                    @foreach (\App\Helpers\FontHelper::FONTS as $key => $opt)
                        <option value="{{ $key }}" {{ old('font_family', $priceList->font_family ?? 'vazirmatn') === $key ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $priceList->is_active) ? 'checked' : '' }}>
                فعال
            </label>
        </div>
    </div>

    <div class="ds-form-card" style="margin-top: 1.5rem;">
        <h2 class="ds-form-card-title">تنظیمات صفحه عمومی (CTA، تماس، شبکه‌ها)</h2>
        <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-bottom: 1rem;">این موارد در صفحه عمومی لیست قیمت نمایش داده می‌شوند. هر کدام را در صورت نیاز فعال کنید.</p>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_cta" value="1" {{ old('show_cta', $priceList->show_cta ?? false) ? 'checked' : '' }}>
                    نمایش دکمه فراخوان (CTA)
                </label>
                <div style="display: grid; gap: 0.75rem; margin-right: 1.5rem;">
                    <div>
                        <label for="cta_text" class="ds-label">متن دکمه</label>
                        <input type="text" name="cta_text" id="cta_text" value="{{ old('cta_text', $priceList->cta_text ?? 'ثبت سفارش') }}" class="ds-input" placeholder="ثبت سفارش">
                    </div>
                    <div>
                        <label for="cta_url" class="ds-label">آدرس لینک (URL)</label>
                        <input type="url" name="cta_url" id="cta_url" value="{{ old('cta_url', $priceList->cta_url) }}" class="ds-input" placeholder="https://...">
                    </div>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_notes" value="1" {{ old('show_notes', $priceList->show_notes ?? false) ? 'checked' : '' }}>
                    نمایش متن توضیحات / یادداشت
                </label>
                <div style="margin-right: 1.5rem;">
                    <textarea name="notes_text" class="ds-textarea" rows="3" placeholder="توضیحات یا شرایط فروش...">{{ old('notes_text', $priceList->notes_text) }}</textarea>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_social" value="1" {{ old('show_social', $priceList->show_social ?? false) ? 'checked' : '' }}>
                    نمایش لینک‌های شبکه‌های اجتماعی
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-right: 1.5rem;">
                    <div><label for="social_instagram" class="ds-label">اینستاگرام</label><input type="text" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $priceList->social_instagram) }}" class="ds-input" placeholder="@username"></div>
                    <div><label for="social_telegram" class="ds-label">تلگرام</label><input type="text" name="social_telegram" id="social_telegram" value="{{ old('social_telegram', $priceList->social_telegram) }}" class="ds-input" placeholder="@username"></div>
                    <div><label for="social_whatsapp" class="ds-label">واتساپ (شماره)</label><input type="text" name="social_whatsapp" id="social_whatsapp" value="{{ old('social_whatsapp', $priceList->social_whatsapp) }}" class="ds-input" placeholder="09123456789" dir="ltr"></div>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_address" value="1" {{ old('show_address', $priceList->show_address ?? false) ? 'checked' : '' }}>
                    نمایش آدرس
                </label>
                <div style="margin-right: 1.5rem;">
                    <textarea name="address_text" class="ds-textarea" rows="2" placeholder="آدرس کسب‌وکار">{{ old('address_text', $priceList->address_text) }}</textarea>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_contact" value="1" {{ old('show_contact', $priceList->show_contact ?? false) ? 'checked' : '' }}>
                    نمایش تماس با ما (تلفن / ایمیل)
                </label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; margin-right: 1.5rem;">
                    <div><label for="contact_phone" class="ds-label">تلفن</label><input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $priceList->contact_phone) }}" class="ds-input" placeholder="021-12345678" dir="ltr"></div>
                    <div><label for="contact_email" class="ds-label">ایمیل</label><input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $priceList->contact_email) }}" class="ds-input" placeholder="info@example.com" dir="ltr"></div>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_share_buttons" value="1" {{ old('show_share_buttons', $priceList->show_share_buttons ?? false) ? 'checked' : '' }}>
                    نمایش دکمه‌های اشتراک‌گذاری (کپی لینک، واتساپ، تلگرام)
                </label>
                <p style="margin: 0.25rem 0 0 1.5rem; font-size: 0.75rem; color: var(--ds-text-subtle);">بازدیدکننده می‌تواند این لیست را با دیگران به‌اشتراک بگذارد.</p>
            </div>
        </div>
    </div>

    <div class="ds-form-card" style="margin-top: 1.5rem;">
        <h2 class="ds-form-card-title">بخش‌ها و آیتم‌ها</h2>
        <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-bottom: 1rem;">هر بخش می‌تواند شامل کالاها از کاتالوگ یا آیتم‌های سفارشی باشد.</p>

        <div id="sections-container">
            @foreach ($sections as $sIdx => $sec)
            <div class="section-block" data-section-idx="{{ $sIdx }}">
                <div class="section-header">
                    <input type="text" name="sections[{{ $sIdx }}][name]" value="{{ $sec['name'] ?? '' }}" class="ds-input section-name" placeholder="نام بخش">
                    <button type="button" class="ds-btn ds-btn-danger remove-section">حذف بخش</button>
                </div>
                <div class="items-container" data-section-idx="{{ $sIdx }}">
                    @foreach ($sec['items'] ?? [] as $iIdx => $it)
                    <div class="item-row" data-item-idx="{{ $iIdx }}">
                        <div class="item-name-wrap">
                            <label class="ds-label" style="font-size: 0.75rem;">کالا</label>
                            <input type="hidden" name="sections[{{ $sIdx }}][items][{{ $iIdx }}][product_id]" value="{{ $it['product_id'] ?? '' }}" class="item-product-id">
                            <input type="text" name="sections[{{ $sIdx }}][items][{{ $iIdx }}][custom_name]" value="{{ $it['custom_name'] ?? ($products->firstWhere('id', $it['product_id'] ?? 0)?->name ?? '') }}" class="ds-input item-name-input" placeholder="جستجو یا تایپ نام کالا…" autocomplete="off">
                            <div class="item-product-results hidden"></div>
                        </div>
                        <div class="item-price">
                            <label class="ds-label" style="font-size: 0.75rem;">قیمت (ریال)</label>
                            <input type="text" name="sections[{{ $sIdx }}][items][{{ $iIdx }}][unit_price]" value="{{ $it['unit_price'] ?? '' }}" class="ds-input item-price-input" placeholder="۰" dir="ltr">
                        </div>
                        <div class="item-badge">
                            <label class="ds-label" style="font-size: 0.75rem;">برچسب</label>
                            <select name="sections[{{ $sIdx }}][items][{{ $iIdx }}][badge]" class="ds-select item-badge-select">
                                <option value="">—</option>
                                @foreach (\App\Models\PriceListItem::BADGES as $key => $label)
                                    <option value="{{ $key }}" {{ ($it['badge'] ?? '') === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <input type="hidden" name="sections[{{ $sIdx }}][items][{{ $iIdx }}][unit]" value="عدد" class="item-unit-input">
                        <div style="display: flex; align-items: flex-end;">
                            <button type="button" class="ds-btn ds-btn-danger remove-item">×</button>
                        </div>
                    </div>
                    @endforeach
                </div>
                <button type="button" class="ds-btn ds-btn-outline add-item-btn" style="margin-top: 0.75rem;">+ آیتم</button>
            </div>
            @endforeach
        </div>
        <button type="button" id="add-section" class="ds-btn ds-btn-secondary" style="margin-top: 1rem;" tabindex="-1">+ بخش جدید</button>
    </div>

    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem; padding-top: 1rem; border-top: 2px solid var(--ds-border);">
        <button type="submit" class="ds-btn ds-btn-primary">ذخیره</button>
        <a href="{{ route('price-lists.show', $priceList) }}" class="ds-btn ds-btn-outline">انصراف</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var sectionIdx = {{ count($sections) }};
    var sectionsContainer = document.getElementById('sections-container');
    if (!sectionsContainer) return;

    var persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    function formatCurrency(n) {
        var s = String(Math.round(n)).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
        return s.replace(/\d/g, function(d) { return persianDigits[+d]; });
    }
    function persianToEnglish(s) {
        var p = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        var e = ['0','1','2','3','4','5','6','7','8','9'];
        for (var i = 0; i < 10; i++) s = String(s).split(p[i]).join(e[i]);
        return s.replace(/[^\d.]/g, '');
    }
    function parseNum(s) { return parseInt(persianToEnglish(String(s)), 10) || 0; }
    function formatCurrencyInput(el) {
        var start = el.selectionStart, end = el.selectionEnd;
        var v = parseNum(el.value);
        el.value = formatCurrency(v);
        var len = el.value.length;
        el.setSelectionRange(Math.min(start, len), Math.min(end, len));
    }

    var badgeOptions = { '': '—', 'new': 'جدید', 'hot': 'جدید', 'special_offer': 'پیشنهاد ویژه', 'sale': 'تخفیف' };
    function renderItemRow(sIdx, iIdx, item) {
        item = item || { product_id: '', custom_name: '', unit_price: '', unit: 'عدد', badge: '' };
        var badgeSel = '<select name="sections[' + sIdx + '][items][' + iIdx + '][badge]" class="ds-select item-badge-select">';
        for (var k in badgeOptions) { badgeSel += '<option value="' + escapeHtml(k) + '"' + (item.badge === k ? ' selected' : '') + '>' + escapeHtml(badgeOptions[k]) + '</option>'; }
        badgeSel += '</select>';
        return '<div class="item-row" data-item-idx="' + iIdx + '">' +
            '<div class="item-name-wrap"><label class="ds-label" style="font-size: 0.75rem;">کالا</label>' +
            '<input type="hidden" name="sections[' + sIdx + '][items][' + iIdx + '][product_id]" value="' + escapeHtml(String(item.product_id||'')) + '" class="item-product-id">' +
            '<input type="text" name="sections[' + sIdx + '][items][' + iIdx + '][custom_name]" value="' + escapeHtml(item.custom_name || '') + '" class="ds-input item-name-input" placeholder="جستجو یا تایپ نام کالا…" autocomplete="off">' +
            '<div class="item-product-results hidden"></div></div>' +
            '<div class="item-price"><label class="ds-label" style="font-size: 0.75rem;">قیمت (ریال)</label>' +
            '<input type="text" name="sections[' + sIdx + '][items][' + iIdx + '][unit_price]" value="' + escapeHtml(item.unit_price || '') + '" class="ds-input item-price-input" placeholder="۰" dir="ltr"></div>' +
            '<div class="item-badge"><label class="ds-label" style="font-size: 0.75rem;">برچسب</label>' + badgeSel + '</div>' +
            '<input type="hidden" name="sections[' + sIdx + '][items][' + iIdx + '][unit]" value="عدد" class="item-unit-input">' +
            '<div style="display: flex; align-items: flex-end;"><button type="button" class="ds-btn ds-btn-danger remove-item">×</button></div></div>';
    }
    function escapeHtml(s) { return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

    function getUsedProductIdsInSection(sectionBlock, excludeRow) {
        var ids = [];
        (sectionBlock || {}).querySelectorAll('.item-row').forEach(function(r) {
            if (excludeRow && r === excludeRow) return;
            var inp = r.querySelector('.item-product-id');
            if (inp) {
                var v = (inp.value || '').trim();
                if (v) ids.push(v);
            }
        });
        return ids;
    }

    function reindexSections() {
        var blocks = sectionsContainer.querySelectorAll('.section-block');
        blocks.forEach(function (block, idx) {
            block.dataset.sectionIdx = idx;
            block.querySelector('.section-name').name = 'sections[' + idx + '][name]';
            var itemsCont = block.querySelector('.items-container');
            itemsCont.dataset.sectionIdx = idx;
            var rows = itemsCont.querySelectorAll('.item-row');
            rows.forEach(function (row, iIdx) {
                row.dataset.itemIdx = iIdx;
                var pid = row.querySelector('.item-product-id');
                var pname = row.querySelector('.item-name-input');
                var pprice = row.querySelector('.item-price-input');
                var pbadge = row.querySelector('.item-badge-select');
                var punit = row.querySelector('.item-unit-input');
                if (pid) pid.name = 'sections[' + idx + '][items][' + iIdx + '][product_id]';
                if (pname) pname.name = 'sections[' + idx + '][items][' + iIdx + '][custom_name]';
                if (pprice) pprice.name = 'sections[' + idx + '][items][' + iIdx + '][unit_price]';
                if (pbadge) pbadge.name = 'sections[' + idx + '][items][' + iIdx + '][badge]';
                if (punit) punit.name = 'sections[' + idx + '][items][' + iIdx + '][unit]';
            });
            block.querySelector('.add-item-btn').onclick = function () { addItem(block); };
        });
        sectionIdx = blocks.length;
    }

    function addItem(block) {
        if (!block) return;
        var sIdx = Array.from(sectionsContainer.querySelectorAll('.section-block')).indexOf(block);
        if (sIdx < 0) return;
        var itemsCont = block.querySelector('.items-container');
        var iIdx = itemsCont.querySelectorAll('.item-row').length;
        itemsCont.insertAdjacentHTML('beforeend', renderItemRow(sIdx, iIdx, {}));
        reindexSections();
        var lastRow = itemsCont.querySelector('.item-row:last-child');
        if (lastRow) {
            lastRow.querySelector('.remove-item').onclick = function () { lastRow.remove(); reindexSections(); };
            bindPriceFormat(lastRow);
        }
    }

    function bindPriceFormat(row) {
        var inp = row.querySelector('.item-price-input');
        if (!inp) return;
        inp.addEventListener('input', function () { formatCurrencyInput(this); });
        inp.addEventListener('blur', function () { formatCurrencyInput(this); });
    }

    var productDebounce = null;
    sectionsContainer.addEventListener('input', function (e) {
        if (e.target.matches('.item-name-input')) {
            var wrap = e.target.closest('.item-name-wrap');
            var results = wrap ? wrap.querySelector('.item-product-results') : null;
            var productIdInput = wrap ? wrap.querySelector('.item-product-id') : null;
            var row = e.target.closest('.item-row');
            var sectionBlock = row ? row.closest('.section-block') : null;
            if (!wrap || !results || !row) return;
            var q = e.target.value.trim();
            if (q.length < 1) {
                results.classList.add('hidden');
                if (productIdInput) productIdInput.value = '';
                return;
            }
            clearTimeout(productDebounce);
            productDebounce = setTimeout(function () {
                fetch('{{ route("products.search.api") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (r) { return r.json(); })
                    .then(function (list) {
                        var usedIds = getUsedProductIdsInSection(sectionBlock, row);
                        results.innerHTML = '';
                        if (list.length === 0) { results.classList.add('hidden'); return; }
                        list.forEach(function (p) {
                            var a = document.createElement('a');
                            a.href = '#';
                            var alreadyAdded = usedIds.indexOf(String(p.id)) >= 0;
                            a.innerHTML = (alreadyAdded ? '<span class="item-tick">✓</span>' : '') + '<span class="item-name-part">' + escapeHtml(p.name) + (p.default_unit_price ? ' — ' + formatCurrency(p.default_unit_price) : '') + '</span>';
                            if (alreadyAdded) a.classList.add('already-added');
                            a.dataset.id = p.id;
                            a.dataset.name = p.name;
                            a.dataset.price = p.default_unit_price || 0;
                            a.addEventListener('click', function (ev) {
                                ev.preventDefault();
                                var nameInp = wrap.querySelector('.item-name-input');
                                var priceInp = row.querySelector('.item-price-input');
                                if (nameInp) nameInp.value = p.name;
                                if (productIdInput) productIdInput.value = p.id;
                                if (priceInp && p.default_unit_price) {
                                    priceInp.value = formatCurrency(p.default_unit_price);
                                    formatCurrencyInput(priceInp);
                                }
                                results.classList.add('hidden');
                            });
                            results.appendChild(a);
                        });
                        results.classList.remove('hidden');
                    });
            }, 250);
        }
    });
    sectionsContainer.addEventListener('blur', function (e) {
        if (e.target.matches('.item-name-input')) {
            var wrap = e.target.closest('.item-name-wrap');
            var results = wrap ? wrap.querySelector('.item-product-results') : null;
            if (results) setTimeout(function () { results.classList.add('hidden'); }, 150);
        }
    }, true);

    sectionsContainer.querySelectorAll('.item-price-input').forEach(function(inp) { bindPriceFormat(inp.closest('.item-row')); });

    function addSection() {
        var html = '<div class="section-block" data-section-idx="' + sectionIdx + '">' +
            '<div class="section-header"><input type="text" name="sections[' + sectionIdx + '][name]" class="ds-input section-name" placeholder="نام بخش">' +
            '<button type="button" class="ds-btn ds-btn-danger remove-section">حذف بخش</button></div>' +
            '<div class="items-container" data-section-idx="' + sectionIdx + '">' +
            renderItemRow(sectionIdx, 0, {}) +
            '</div><button type="button" class="ds-btn ds-btn-outline add-item-btn" style="margin-top: 0.75rem;">+ آیتم</button></div>';
        sectionsContainer.insertAdjacentHTML('beforeend', html);
        reindexSections();
        var newBlock = sectionsContainer.querySelector('.section-block:last-child');
        newBlock.querySelector('.remove-section').onclick = function () {
            if (sectionsContainer.querySelectorAll('.section-block').length > 1) { newBlock.remove(); reindexSections(); }
        };
        newBlock.querySelector('.remove-item').onclick = function () { newBlock.querySelector('.item-row').remove(); reindexSections(); };
        newBlock.querySelector('.add-item-btn').onclick = function () { addItem(newBlock); };
        bindPriceFormat(newBlock.querySelector('.item-row'));
    }

    sectionsContainer.querySelectorAll('.remove-section').forEach(function (btn) {
        btn.onclick = function () {
            var block = btn.closest('.section-block');
            if (sectionsContainer.querySelectorAll('.section-block').length > 1) { block.remove(); reindexSections(); }
        };
    });
    sectionsContainer.querySelectorAll('.remove-item').forEach(function (btn) {
        btn.onclick = function () {
            var row = btn.closest('.item-row');
            var cont = row.closest('.items-container');
            if (cont.querySelectorAll('.item-row').length > 1) { row.remove(); reindexSections(); }
        };
    });
    sectionsContainer.querySelectorAll('.add-item-btn').forEach(function (btn) {
        btn.onclick = function () { addItem(btn.closest('.section-block')); };
    });
    document.getElementById('add-section').onclick = addSection;
})();
</script>
@endpush
