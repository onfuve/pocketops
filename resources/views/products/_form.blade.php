@php
    $isEdit = isset($product) && $product->exists;
@endphp
<form action="{{ $isEdit ? route('products.update', $product) : route('products.store') }}" method="post" enctype="multipart/form-data">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="ds-form-card">
        <h2 class="ds-form-card-title">اطلاعات اصلی</h2>
        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
            <div>
                <label for="name" class="ds-label">نام کالا یا خدمت <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required class="ds-input" placeholder="مثلاً قطعه X">
                @error('name')
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description" class="ds-label">توضیحات</label>
                <textarea name="description" id="description" rows="3" class="ds-textarea" placeholder="اختیاری">{{ old('description', $product->description ?? '') }}</textarea>
                @error('description')
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                @enderror
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label for="code_global" class="ds-label">کد جهانی</label>
                    <input type="text" name="code_global" id="code_global" value="{{ old('code_global', $product->code_global ?? '') }}" class="ds-input" placeholder="بارکد یا شناسه جهانی (اختیاری)" dir="ltr">
                    @error('code_global')
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="code_internal" class="ds-label">کد داخلی</label>
                    <input type="text" name="code_internal" id="code_internal" value="{{ old('code_internal', $product->code_internal ?? '') }}" class="ds-input" placeholder="کد داخلی / SKU (اختیاری)" dir="ltr">
                    @error('code_internal')
                        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            <div>
                <label for="photo" class="ds-label">عکس</label>
                <p style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin: 0 0 0.5rem;">این عکس در لیست قیمت و صفحات فرود استفاده می‌شود.</p>
                <div style="margin-bottom: 0.75rem; padding: 0.75rem 1rem; background: #eff6ff; border-radius: 0.5rem; border-right: 3px solid #3b82f6;">
                    <div style="font-size: 0.75rem; color: #1e40af; line-height: 1.5;">
                        <strong>💡 توصیه:</strong> نسبت 1:1 (مربع)، اندازه 400×400 تا 800×800 پیکسل، فرمت JPEG/PNG، حجم کمتر از 300KB
                        <br><a href="/docs/IMAGE_GUIDE_FA.md" target="_blank" style="color: #3b82f6; text-decoration: underline;">راهنمای کامل →</a>
                    </div>
                </div>
                @if ($isEdit && $product->photo_path)
                    <div style="margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.75rem;">
                        <img src="{{ asset('storage/' . $product->photo_path) }}" alt="" style="width: 5rem; height: 5rem; object-fit: cover; border-radius: var(--ds-radius);">
                        <label style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--ds-text-subtle); cursor: pointer;">
                            <input type="checkbox" name="remove_photo" value="1">
                            حذف عکس
                        </label>
                    </div>
                @endif
                <input type="file" name="photo" id="photo" accept="image/*" class="ds-input">
                @error('photo')
                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #b91c1c;">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="ds-label">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                    فعال
                </label>
            </div>
        </div>
    </div>

    @isset($tags)
        @include('components._tag-section', ['tags' => $tags, 'entity' => $product ?? null, 'accentColor' => '#059669'])
    @endisset

    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 1.5rem;">
        <button type="submit" class="ds-btn ds-btn-primary">
            {{ $isEdit ? 'ذخیره تغییرات' : 'ذخیره' }}
        </button>
        <a href="{{ $isEdit ? route('products.show', $product) : route('products.index') }}" class="ds-btn ds-btn-outline">انصراف</a>
    </div>
</form>
