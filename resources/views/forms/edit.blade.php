@extends('layouts.app')

@section('title', 'ویرایش فرم — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #e0f2fe; color: #0369a1;">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
                </span>
                {{ $form->title ?: 'ویرایش فرم' }}
            </h1>
            <p class="ds-page-subtitle">ماژول‌ها را اضافه یا ویرایش کنید؛ سپس فرم را فعال و لینک بسازید.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('forms.show', $form) }}" class="ds-btn ds-btn-outline">مشاهده و لینک‌ها</a>
            <a href="{{ route('forms.index') }}" class="ds-btn ds-btn-outline">لیست فرم‌ها</a>
        </div>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ds-alert-success" style="margin-bottom: 1rem; background: #fef2f2; border-color: #fecaca; color: #b91c1c;">{{ session('error') }}</div>
    @endif

    {{-- Form settings --}}
    <div class="ds-form-card" style="margin-bottom: 1.5rem;">
        <h2 class="ds-form-card-title">تنظیمات فرم</h2>
        <form action="{{ route('forms.update', $form) }}" method="post" style="display: flex; flex-direction: column; gap: 1rem;">
            @csrf
            @method('PUT')
            <div>
                <label for="title" class="ds-label">عنوان</label>
                <input type="text" name="title" id="title" value="{{ old('title', $form->title) }}" class="ds-input" required>
            </div>
            <div>
                <label for="description" class="ds-label">توضیح (نمایش کنار QR در فاکتور)</label>
                <input type="text" name="description" id="description" value="{{ old('description', $form->description) }}" class="ds-input" placeholder="مثال: نظر شما درباره خدمات ما" maxlength="500">
                <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: var(--ds-text-subtle);">اگر این فرم به فاکتور پیوست شود، این متن زیر QR چاپ می‌شود. خالی = عنوان فرم.</p>
            </div>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <div>
                    <label for="status" class="ds-label">وضعیت</label>
                    <select name="status" id="status" class="ds-select">
                        <option value="draft" {{ old('status', $form->status) === 'draft' ? 'selected' : '' }}>پیش‌نویس</option>
                        <option value="active" {{ old('status', $form->status) === 'active' ? 'selected' : '' }}>فعال</option>
                        <option value="closed" {{ old('status', $form->status) === 'closed' ? 'selected' : '' }}>بسته</option>
                    </select>
                </div>
                <div>
                    <label for="edit_period_minutes" class="ds-label">دقیقه ویرایش بعد از ارسال</label>
                    <input type="number" name="edit_period_minutes" id="edit_period_minutes" value="{{ old('edit_period_minutes', $form->edit_period_minutes) }}" class="ds-input" min="0" max="10080">
                </div>
            </div>
            <button type="submit" class="ds-btn ds-btn-primary" style="align-self: flex-start;">ذخیره تنظیمات</button>
        </form>
    </div>

    {{-- Modules --}}
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">ماژول‌های فرم</h2>

        @forelse($form->modules as $module)
            <div class="ds-form-card module-block" style="margin-bottom: 1rem; padding: 1rem; border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg);" data-module-id="{{ $module->id }}">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <strong>{{ \App\Models\FormModule::typeLabels()[$module->type] ?? $module->type }}</strong>
                        @if($module->getConfig('label'))
                            <span style="color: var(--ds-text-subtle); font-size: 0.875rem;"> — {{ $module->getConfig('label') }}</span>
                        @endif
                    </div>
                    <div style="display: flex; gap: 0.5rem;">
                        <button type="button" class="ds-btn ds-btn-ghost module-toggle-edit" style="padding: 0.375rem 0.75rem;" data-target="{{ $module->id }}">ویرایش</button>
                        <form action="{{ route('forms.modules.destroy', [$form, $module]) }}" method="post" onsubmit="return confirm('این ماژول حذف شود؟');" style="display: inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ds-btn ds-btn-danger" style="padding: 0.375rem 0.75rem;">حذف</button>
                        </form>
                    </div>
                </div>
                <div class="module-edit-wrap" id="module-edit-{{ $module->id }}" style="display: none;">
                    @include('forms._module_edit', ['form' => $form, 'module' => $module])
                </div>
            </div>
        @empty
            <p style="color: var(--ds-text-subtle); margin-bottom: 1rem;">هنوز ماژولی اضافه نشده. از باکس زیر یکی را انتخاب کنید.</p>
        @endforelse

        {{-- Add module --}}
        <form action="{{ route('forms.modules.store', $form) }}" method="post" style="margin-top: 1rem; padding: 1rem; border: 2px dashed var(--ds-border); border-radius: var(--ds-radius-lg);">
            @csrf
            <label for="module_type" class="ds-label">افزودن ماژول</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                <select name="type" id="module_type" class="ds-select" style="min-width: 12rem;">
                    @foreach(\App\Models\FormModule::typeLabels() as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="ds-btn ds-btn-secondary">افزودن</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.querySelectorAll('.module-toggle-edit').forEach(function(btn) {
    btn.addEventListener('click', function() {
        var id = this.getAttribute('data-target');
        var wrap = document.getElementById('module-edit-' + id);
        if (wrap) {
            wrap.style.display = wrap.style.display === 'none' ? 'block' : 'none';
        }
    });
});
</script>
@endpush
@endsection
