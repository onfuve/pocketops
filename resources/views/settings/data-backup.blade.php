@extends('layouts.app')

@section('title', 'پشتیبان و بازیابی داده — ' . config('app.name'))

@section('content')
<div class="ds-page" style="max-width: 44rem;">
    <header class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #047857; border-color: #a7f3d0;">
                    @include('components._icons', ['name' => 'database', 'class' => 'w-5 h-5'])
                </span>
                پشتیبان و بازیابی داده
            </h1>
            <p class="ds-page-subtitle">
                انتقال امن بین سرورها: خروجی JSON از تمام جداول کاری؛ بازیابی جایگزین کامل دادهٔ فعلی است.
            </p>
            <p class="ds-page-subtitle" style="margin-top: 0.35rem; font-size: 0.8125rem; color: var(--ds-text-faint);">
                موتور فعلی: <strong>{{ $driverLabel }}</strong>
                — در دسترس: <strong>{{ number_format($summary['table_count']) }}</strong> جدول،
                حدود <strong>{{ number_format($summary['row_count']) }}</strong> ردیف (بدون migration و کش).
            </p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('settings.instance-sync') }}" class="ds-btn ds-btn-secondary" style="white-space: nowrap;">
                @include('components._icons', ['name' => 'link', 'class' => 'w-4 h-4'])
                همگام‌سازی نصب‌ها
            </a>
            <a href="{{ route('settings.company') }}" class="ds-btn ds-btn-secondary" style="white-space: nowrap;">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                تنظیمات شرکت
            </a>
        </div>
    </header>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1.25rem;" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ds-alert-error" style="margin-bottom: 1.25rem;" role="alert">{{ session('error') }}</div>
    @endif

    {{-- Export --}}
    <section class="ds-form-card" style="margin-bottom: 1.25rem; border-color: var(--ds-primary-border); background: linear-gradient(to bottom, #f0fdf9 0%, #fff 4rem);">
        <h2 class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem; color: var(--ds-primary-dark);">
            @include('components._icons', ['name' => 'download', 'class' => 'w-5 h-5'])
            خروجی پشتیبان (دانلود)
        </h2>
        <ol style="margin: 0 0 1rem 0; padding-inline-start: 1.25rem; font-size: 0.875rem; color: var(--ds-text-muted); line-height: 1.7;">
            <li>روی دکمهٔ زیر بزنید؛ یک فایل ‎.json‎ با برچسب زمانی در نام فایل می‌گیرید.</li>
            <li>برای <strong>فایل‌های رسانه</strong> (مهر شرکت، پیوست‌ها، تصاویر محصول) پوشهٔ
                <code style="font-size: 0.8125rem; background: var(--ds-bg-subtle); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">storage/app/public</code>
                را هم کپی کنید و در مقصد <code style="font-size: 0.8125rem; background: var(--ds-bg-subtle); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">php artisan storage:link</code> بزنید.</li>
            <li>در مقصد ابتدا همان نسخهٔ کد و <code style="font-size: 0.8125rem;">php artisan migrate</code> را اجرا کنید، بعد بازیابی JSON.</li>
        </ol>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('settings.data-backup.export') }}"
               id="data-backup-export-btn"
               class="ds-btn ds-btn-primary"
               data-busy-label="در حال آماده‌سازی فایل…">
                @include('components._icons', ['name' => 'download', 'class' => 'w-4 h-4'])
                <span class="export-btn-text">دانلود JSON</span>
            </a>
            <span class="text-sm" style="color: var(--ds-text-subtle); max-width: 16rem;">حداکثر حجم بازیابی از همین صفحه: حدود ۲۵۰ مگابایت برای هر فایل.</span>
        </div>
    </section>

    {{-- Import --}}
    <section class="ds-form-card" style="border-color: var(--ds-danger-border); background: linear-gradient(to bottom, #fffefe 0%, #fff 5rem);">
        <h2 class="ds-form-card-title" style="color: var(--ds-danger); display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'upload', 'class' => 'w-5 h-5'])
            بازیابی (حذف و جایگزینی)
        </h2>
        <div class="ds-alert-error" style="margin-bottom: 1rem; font-size: 0.875rem; line-height: 1.6;">
            تمام ردیف‌های جداول کاری در <strong>همین نصب</strong> پاک می‌شوند و با فایل جایگزین می‌شوند.
            اسکیما عوض نمی‌شود. اگر فایل تقریباً خالی باشد، بعد از بازیابی پایگاه بدون رکورد می‌ماند.
            حتماً قبل از تست، یک پشتیبان از محیط فعلی بگیرید.
        </div>
        <form id="data-backup-import-form"
              action="{{ route('settings.data-backup.import') }}"
              method="post"
              enctype="multipart/form-data"
              class="space-y-5">
            @csrf
            <div>
                <label for="backup" class="ds-label">فایل پشتیبان</label>
                <input type="file"
                       name="backup"
                       id="backup"
                       accept=".json,application/json,text/plain"
                       class="ds-input"
                       style="padding: 0.5rem;"
                       required>
                @error('backup')
                    <p class="text-red-600 text-sm mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="rounded-lg border px-3 py-3" style="border-color: var(--ds-border); background: var(--ds-bg-muted);">
                <div class="flex items-start gap-2">
                    <input type="checkbox"
                           name="confirm_restore"
                           id="confirm_restore"
                           value="1"
                           class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500"
                           required
                           {{ old('confirm_restore') ? 'checked' : '' }}>
                    <label for="confirm_restore" class="text-sm" style="color: var(--ds-text); line-height: 1.6;">
                        تأیید می‌کنم که از <strong>غیرقابل‌بازگشت بودن</strong> این کار مطلعم و اخیراً در صورت نیاز پشتیبان گرفته‌ام.
                    </label>
                </div>
                @error('confirm_restore')
                    <p class="text-red-600 text-sm mt-2 mb-0" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" id="data-backup-import-submit" class="ds-btn ds-btn-danger">
                    @include('components._icons', ['name' => 'upload', 'class' => 'w-4 h-4'])
                    <span class="import-submit-label">بازیابی از فایل</span>
                </button>
                <span id="data-backup-import-hint" class="text-sm" style="color: var(--ds-text-subtle); display: none;">
                    در حال بازیابی… لطفاً صبر کنید و صفحه را نبندید.
                </span>
            </div>
        </form>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var exportBtn = document.getElementById('data-backup-export-btn');
    if (exportBtn) {
        exportBtn.addEventListener('click', function () {
            if (exportBtn.getAttribute('aria-busy') === 'true') {
                return;
            }
            exportBtn.setAttribute('aria-busy', 'true');
            var label = exportBtn.querySelector('.export-btn-text');
            var busy = exportBtn.getAttribute('data-busy-label') || '…';
            if (label) {
                exportBtn.setAttribute('data-original-label', label.textContent);
                label.textContent = busy;
            }
            exportBtn.style.pointerEvents = 'none';
            exportBtn.style.opacity = '0.85';
        });
    }

    var form = document.getElementById('data-backup-import-form');
    var submitBtn = document.getElementById('data-backup-import-submit');
    var hint = document.getElementById('data-backup-import-hint');
    if (form && submitBtn) {
        form.addEventListener('submit', function () {
            if (form.getAttribute('data-submitting') === 'true') {
                return;
            }
            form.setAttribute('data-submitting', 'true');
            submitBtn.disabled = true;
            submitBtn.style.opacity = '0.85';
            var sl = submitBtn.querySelector('.import-submit-label');
            if (sl) {
                sl.textContent = 'در حال بازیابی…';
            }
            if (hint) {
                hint.style.display = 'inline';
            }
        });
    }
})();
</script>
@endpush
