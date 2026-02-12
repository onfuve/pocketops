@php use Illuminate\Support\Facades\Storage; @endphp
@push('styles')
<style>
.plp-form .product-autocomplete-wrap { position: relative; }
.plp-form .product-autocomplete-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 25; max-height: 12rem; overflow-y: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.plp-form .product-autocomplete-results a { display: flex; align-items: center; gap: 0.5rem; padding: 0.625rem 0.875rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); }
.plp-form .product-autocomplete-results a:last-child { border-bottom: none; }
.plp-form .product-autocomplete-results a:hover { background: var(--ds-bg-subtle); }
.plp-form .product-autocomplete-results.hidden { display: none !important; }
.plp-form .ds-form-card { margin-bottom: 1.25rem; }
.plp-form .form-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
@media (max-width: 640px) { .plp-form .form-grid-2 { grid-template-columns: 1fr; } }
.plp-form .section-label { font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--ds-text-subtle); margin-bottom: 0.75rem; }
</style>
@endpush

<form class="plp-form" method="post" action="{{ isset($page) && $page->exists ? route('product-landing-pages.update', $page) : route('product-landing-pages.store') }}" id="plp-form" enctype="multipart/form-data">
    @csrf
    @if (isset($page) && $page->exists) @method('PUT') @endif

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">محصول و محتوا</h2>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div class="product-autocomplete-wrap">
                <label for="product_search" class="ds-label">محصول <span style="color: #b91c1c;">*</span></label>
                <input type="hidden" name="product_id" id="product_id" value="{{ old('product_id', $page->product_id ?? '') }}" required>
                <input type="text" id="product_search" class="ds-input" placeholder="جستجو یا تایپ نام محصول…" autocomplete="off" value="{{ ($pid = old('product_id', $page->product_id ?? '')) ? ($products->firstWhere('id', $pid)?->name ?? ($page->product->name ?? '')) : '' }}">
                <div class="product-autocomplete-results hidden" id="product-results"></div>
                @error('product_id')<p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="headline" class="ds-label">عنوان (headline)</label>
                <input type="text" name="headline" id="headline" value="{{ old('headline', $page->headline ?? '') }}" class="ds-input" placeholder="خالی = نام محصول">
            </div>
            <div>
                <label for="subheadline" class="ds-label">زیرعنوان</label>
                <input type="text" name="subheadline" id="subheadline" value="{{ old('subheadline', $page->subheadline ?? '') }}" class="ds-input" placeholder="متن کوتاه زیر عنوان">
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">تصاویر</h2>
        <p class="section-label">تصاویر باکیفیت تأثیر زیادی روی صفحه فرود دارند. تصویر اصلی برای نمایش در بالای صفحه، و تصاویر اضافه برای گالری استفاده می‌شوند.</p>
        <div style="margin-bottom: 1rem; padding: 0.875rem 1rem; background: #eff6ff; border-radius: 0.5rem; border-right: 3px solid #3b82f6;">
            <div style="font-size: 0.8125rem; color: #1e40af; line-height: 1.6;">
                <strong>💡 راهنمای تصویر:</strong>
                <ul style="margin: 0.5rem 0 0 1.25rem; padding: 0;">
                    <li><strong>Hero/Minimal/Card:</strong> نسبت 1:1 (مربع)، اندازه 800×800 تا 1200×1200 پیکسل</li>
                    <li><strong>Split:</strong> نسبت 3:4 یا 4:3، اندازه 900×1200 یا 1200×900 پیکسل</li>
                    <li><strong>فرمت:</strong> JPEG یا PNG، حجم کمتر از 500KB</li>
                    <li><a href="/docs/IMAGE_GUIDE_FA.md" target="_blank" style="color: #3b82f6; text-decoration: underline;">راهنمای کامل تصاویر →</a></li>
                </ul>
            </div>
        </div>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label for="photo" class="ds-label">تصویر اصلی (hero)</label>
                <p style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin: 0 0 0.5rem;">اگر خالی بگذارید از تصویر محصول استفاده می‌شود.</p>
                @if (isset($page) && $page->photo_path)
                    <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.75rem;">
                        <img src="{{ Storage::url($page->photo_path) }}" alt="" style="width: 6rem; height: 6rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--ds-border);">
                        <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" name="remove_photo" value="1">
                            حذف تصویر اصلی
                        </label>
                    </div>
                @endif
                <input type="file" name="photo" id="photo" class="ds-input" accept="image/*">
            </div>
            @if (isset($page) && $page->exists)
            <div>
                <label for="photos" class="ds-label">تصاویر اضافه (گالری)</label>
                <p style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin: 0 0 0.5rem;">می‌توانید چند تصویر انتخاب کنید.</p>
                @php $gallery = $page->photos ?? []; @endphp
                @if (!empty($gallery))
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 0.75rem;">
                        @foreach ($gallery as $path)
                            <div style="position: relative; display: inline-block;">
                                <img src="{{ Storage::url($path) }}" alt="" style="width: 5rem; height: 5rem; object-fit: cover; border-radius: 0.5rem; border: 1px solid var(--ds-border); display: block;">
                                <label style="position: absolute; top: -0.35rem; right: -0.35rem; background: #ef4444; color: #fff; width: 1.25rem; height: 1.25rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.875rem; cursor: pointer; line-height: 1; border: 2px solid #fff;">
                                    <input type="checkbox" name="remove_photos[]" value="{{ $path }}" style="position: absolute; opacity: 0; width: 0; height: 0;">×
                                </label>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input type="file" name="photos[]" id="photos" class="ds-input" accept="image/*" multiple>
            </div>
            @endif
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">قالب و ظاهر</h2>
        <div class="form-grid-2" style="gap: 1.25rem;">
            <div>
                <label for="template" class="ds-label">قالب</label>
                <select name="template" id="template" class="ds-select">
                    <option value="hero" {{ old('template', $page->template ?? 'hero') === 'hero' ? 'selected' : '' }}>قهرمان (Hero)</option>
                    <option value="minimal" {{ old('template', $page->template ?? '') === 'minimal' ? 'selected' : '' }}>مینیمال</option>
                    <option value="card" {{ old('template', $page->template ?? '') === 'card' ? 'selected' : '' }}>کارت</option>
                    <option value="split" {{ old('template', $page->template ?? '') === 'split' ? 'selected' : '' }}>اسپلیت</option>
                </select>
            </div>
            <div>
                <label for="primary_color" class="ds-label">رنگ اصلی (hex)</label>
                <input type="text" name="primary_color" id="primary_color" value="{{ old('primary_color', $page->primary_color ?? '#7c3aed') }}" class="ds-input" placeholder="#7c3aed" dir="ltr">
            </div>
            <div>
                <label for="font_family" class="ds-label">فونت فارسی</label>
                <select name="font_family" id="font_family" class="ds-select">
                    @foreach (\App\Helpers\FontHelper::FONTS as $key => $opt)
                        <option value="{{ $key }}" {{ old('font_family', $page->font_family ?? 'vazirmatn') === $key ? 'selected' : '' }}>{{ $opt['label'] }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">قیمت</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                <input type="checkbox" name="show_price" value="1" {{ old('show_price', $page->show_price ?? true) ? 'checked' : '' }}>
                نمایش قیمت
            </label>
            <div>
                <label for="price" class="ds-label">قیمت (اختیاری)</label>
                <input type="text" name="price" id="price" value="{{ old('price', $page->price ?? '') }}" class="ds-input" placeholder="{{ ($page->product && $page->product->default_unit_price) ? number_format((int) $page->product->default_unit_price) . ' (قیمت محصول)' : 'خالی = قیمت محصول' }}" dir="ltr" inputmode="numeric">
                @error('price')<p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="price_format" class="ds-label">فرمت قیمت</label>
                <select name="price_format" id="price_format" class="ds-select">
                    <option value="rial" {{ old('price_format', $page->price_format ?? 'rial') === 'rial' ? 'selected' : '' }}>ریال</option>
                    <option value="toman" {{ old('price_format', $page->price_format ?? '') === 'toman' ? 'selected' : '' }}>تومان</option>
                    <option value="none" {{ old('price_format', $page->price_format ?? '') === 'none' ? 'selected' : '' }}>فقط عدد</option>
                </select>
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">دکمه فراخوان (CTA)</h2>
        <div style="display: flex; flex-direction: column; gap: 1rem;">
            <div class="form-grid-2">
                <div>
                    <label for="cta_type" class="ds-label">نوع</label>
                    <select name="cta_type" id="cta_type" class="ds-select">
                        <option value="link" {{ old('cta_type', $page->cta_type ?? 'link') === 'link' ? 'selected' : '' }}>لینک سفارشی</option>
                        <option value="purchase" {{ old('cta_type', $page->cta_type ?? '') === 'purchase' ? 'selected' : '' }}>خرید / سفارش</option>
                        <option value="call" {{ old('cta_type', $page->cta_type ?? '') === 'call' ? 'selected' : '' }}>تماس تلفنی</option>
                        <option value="whatsapp" {{ old('cta_type', $page->cta_type ?? '') === 'whatsapp' ? 'selected' : '' }}>واتساپ</option>
                    </select>
                </div>
                <div>
                    <label for="cta_button_text" class="ds-label">متن دکمه</label>
                    <input type="text" name="cta_button_text" id="cta_button_text" value="{{ old('cta_button_text', $page->cta_button_text ?? 'سفارش دهید') }}" class="ds-input" placeholder="سفارش دهید">
                </div>
            </div>
            <div>
                <label for="cta_url" class="ds-label">آدرس (URL یا شماره تلفن)</label>
                <input type="text" name="cta_url" id="cta_url" value="{{ old('cta_url', $page->cta_url ?? '') }}" class="ds-input" placeholder="https://... یا ۰۹۱۲۳۴۵۶۷۸۹" dir="ltr">
            </div>
        </div>
    </div>

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">تنظیمات صفحه عمومی</h2>
        <p class="section-label">CTA، یادداشت، تماس، شبکه‌ها و اشتراک‌گذاری</p>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_notes" value="1" {{ old('show_notes', $page->show_notes ?? false) ? 'checked' : '' }}>
                    نمایش متن یادداشت / توضیحات
                </label>
                <textarea name="notes_text" class="ds-textarea" rows="2" placeholder="توضیحات یا شرایط فروش...">{{ old('notes_text', $page->notes_text ?? '') }}</textarea>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_social" value="1" {{ old('show_social', $page->show_social ?? false) ? 'checked' : '' }}>
                    نمایش شبکه‌های اجتماعی
                </label>
                <div class="form-grid-2" style="margin-right: 1.5rem;">
                    <div><label for="social_instagram" class="ds-label" style="font-size: 0.75rem;">اینستاگرام</label><input type="text" name="social_instagram" id="social_instagram" value="{{ old('social_instagram', $page->social_instagram ?? '') }}" class="ds-input" placeholder="@username"></div>
                    <div><label for="social_telegram" class="ds-label" style="font-size: 0.75rem;">تلگرام</label><input type="text" name="social_telegram" id="social_telegram" value="{{ old('social_telegram', $page->social_telegram ?? '') }}" class="ds-input" placeholder="@username"></div>
                    <div><label for="social_whatsapp" class="ds-label" style="font-size: 0.75rem;">واتساپ</label><input type="text" name="social_whatsapp" id="social_whatsapp" value="{{ old('social_whatsapp', $page->social_whatsapp ?? '') }}" class="ds-input" placeholder="09123456789" dir="ltr"></div>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_address" value="1" {{ old('show_address', $page->show_address ?? false) ? 'checked' : '' }}>
                    نمایش آدرس
                </label>
                <textarea name="address_text" class="ds-textarea" rows="2" placeholder="آدرس کسب‌وکار">{{ old('address_text', $page->address_text ?? '') }}</textarea>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-bottom: 0.5rem;">
                    <input type="checkbox" name="show_contact" value="1" {{ old('show_contact', $page->show_contact ?? false) ? 'checked' : '' }}>
                    نمایش تماس (تلفن / ایمیل)
                </label>
                <div class="form-grid-2" style="margin-right: 1.5rem;">
                    <div><label for="contact_phone" class="ds-label" style="font-size: 0.75rem;">تلفن</label><input type="text" name="contact_phone" id="contact_phone" value="{{ old('contact_phone', $page->contact_phone ?? '') }}" class="ds-input" placeholder="021-12345678" dir="ltr"></div>
                    <div><label for="contact_email" class="ds-label" style="font-size: 0.75rem;">ایمیل</label><input type="email" name="contact_email" id="contact_email" value="{{ old('contact_email', $page->contact_email ?? '') }}" class="ds-input" placeholder="info@example.com" dir="ltr"></div>
                </div>
            </div>
            <div>
                <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                    <input type="checkbox" name="show_share_buttons" value="1" {{ old('show_share_buttons', $page->show_share_buttons ?? false) ? 'checked' : '' }}>
                    نمایش دکمه‌های اشتراک‌گذاری (کپی لینک، واتساپ، تلگرام)
                </label>
            </div>
        </div>
    </div>

    @if (isset($page) && $page->exists)
    <div class="ds-form-card">
        <label class="ds-label" style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $page->is_active ?? true) ? 'checked' : '' }}>
            فعال
        </label>
    </div>
    @endif

    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; padding-top: 1rem; border-top: 2px solid var(--ds-border);">
        <button type="submit" class="ds-btn ds-btn-primary">ذخیره</button>
        <a href="{{ route('product-landing-pages.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
    </div>
</form>

@push('scripts')
<script>
(function () {
    var productSearch = document.getElementById('product_search');
    var productId = document.getElementById('product_id');
    var productResults = document.getElementById('product-results');
    if (!productSearch || !productId || !productResults) return;

    var productDebounce = null;
    productSearch.addEventListener('input', function () {
        var q = this.value.trim();
        if (q.length < 1) {
            productResults.classList.add('hidden');
            productId.value = '';
            return;
        }
        clearTimeout(productDebounce);
        productDebounce = setTimeout(function () {
            fetch('{{ route("products.search.api") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    productResults.innerHTML = '';
                    if (list.length === 0) { productResults.classList.add('hidden'); return; }
                    list.forEach(function (p) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = p.name + (p.default_unit_price ? ' — ' + p.default_unit_price : '');
                        a.dataset.id = p.id;
                        a.dataset.name = p.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            productId.value = p.id;
                            productSearch.value = p.name;
                            productResults.classList.add('hidden');
                        });
                        productResults.appendChild(a);
                    });
                    productResults.classList.remove('hidden');
                });
        }, 250);
    });
    productSearch.addEventListener('blur', function () {
        setTimeout(function () { productResults.classList.add('hidden'); }, 150);
    });
})();
</script>
@endpush
