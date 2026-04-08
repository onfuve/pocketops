@extends('layouts.app')

@section('title', 'همگام‌سازی بین نصب‌ها — ' . config('app.name'))

@section('content')
<div class="ds-page" style="max-width: 46rem;">
    <header class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #e0e7ff, #c7d2fe); color: #4338ca; border-color: #a5b4fc;">
                    @include('components._icons', ['name' => 'link', 'class' => 'w-5 h-5'])
                </span>
                همگام‌سازی بین نصب‌ها
            </h1>
            <p class="ds-page-subtitle">
                هر نصب یک کلید مخفی برای <strong>ورودی</strong> می‌سازد؛ نصب دیگر آدرس پایه و همان کلید را به‌عنوان Bearer ذخیره می‌کند و دادهٔ جداول کاری را مثل پشتیبان JSON می‌گیرد و با گزینه‌های زیر ادغام می‌کند.
            </p>
            <p class="ds-page-subtitle" style="margin-top: 0.35rem; font-size: 0.8125rem; color: var(--ds-text-faint);">
                مسیر عمومی دریافت: <code style="font-size: 0.8125rem; background: var(--ds-bg-subtle); padding: 0.125rem 0.375rem; border-radius: 0.25rem;">GET {{ url('/instance-sync/export') }}</code>
                — ترجیحاً HTTPS. فایل‌های <code style="font-size: 0.8125rem;">storage</code> را جداگانه منتقل کنید.
            </p>
        </div>
        <a href="{{ route('settings.company') }}" class="ds-btn ds-btn-secondary" style="white-space: nowrap;">
            @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
            تنظیمات شرکت
        </a>
    </header>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1.25rem;" role="status">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ds-alert-error" style="margin-bottom: 1.25rem;" role="alert">{{ session('error') }}</div>
    @endif

    @if (!empty($newToken))
        <div class="ds-alert-error" style="margin-bottom: 1.25rem; border-color: #fcd34d; background: #fffbeb; color: #92400e;" role="status">
            <strong>کلید ورودی جدید (فقط همین نمایش):</strong>
            <div style="margin-top: 0.5rem; word-break: break-all; font-family: ui-monospace, monospace; font-size: 0.8125rem;">{{ $newToken }}</div>
            <p style="margin: 0.75rem 0 0 0; font-size: 0.875rem;">این مقدار را در نصب مقابل در فیلد «کلید Bearer سرور مقصد» بگذارید (هدر Authorization).</p>
        </div>
    @endif

    <section class="ds-form-card" style="margin-bottom: 1.25rem;">
        <h2 class="ds-form-card-title">۱. کلید ورودی (این سرور را برای دیگری باز کنید)</h2>
        <p class="text-sm" style="color: var(--ds-text-muted); line-height: 1.65; margin-bottom: 1rem;">
            تا کلید نسازید، آدرس <code style="font-size: 0.8125rem;">/instance-sync/export</code> روی این نصب پاسخ نمی‌دهد.
            وضعیت فعلی: @if($hasIncomingKey)<span class="ds-badge ds-badge-primary">فعال</span>@else<span class="ds-badge">غیرفعال</span>@endif
        </p>
        <div class="flex flex-wrap items-center gap-3">
            <form action="{{ route('settings.instance-sync.incoming-token') }}" method="post">
                @csrf
                <button type="submit" class="ds-btn ds-btn-primary">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-4 h-4'])
                    ساخت / تعویض کلید ورودی
                </button>
            </form>
            @if($hasIncomingKey)
                <form action="{{ route('settings.instance-sync.revoke-incoming') }}" method="post" onsubmit="return confirm('ورودی همگام‌سازی غیرفعال شود؟');">
                    @csrf
                    <button type="submit" class="ds-btn ds-btn-outline">
                        غیرفعال کردن کلید ورودی
                    </button>
                </form>
            @endif
        </div>
    </section>

    <section class="ds-form-card" style="margin-bottom: 1.25rem;">
        <h2 class="ds-form-card-title">۲. سرور مقصد (از اینجا به نصب دیگر وصل شوید)</h2>
        <form action="{{ route('settings.instance-sync.remote') }}" method="post" class="space-y-4">
            @csrf
            <div>
                <label for="remote_url" class="ds-label">آدرس پایه (مثلاً https://crm.example.com)</label>
                <input type="url" name="remote_url" id="remote_url" class="ds-input" dir="ltr" style="text-align: left;"
                       value="{{ old('remote_url', $remoteUrl) }}"
                       placeholder="https://...">
                @error('remote_url')
                    <p class="text-red-600 text-sm mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="remote_token" class="ds-label">کلید Bearer سرور مقصد (همان چیزی که مدیر آنجا ساخت)</label>
                <input type="password" name="remote_token" id="remote_token" class="ds-input" dir="ltr" style="text-align: left;" autocomplete="off"
                       placeholder="{{ $hasRemote ? '•••••••• (خالی بگذارید تا کلید ذخیره‌شده عوض نشود)' : 'الزامی' }}">
                @error('remote_token')
                    <p class="text-red-600 text-sm mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">ذخیرهٔ آدرس و کلید</button>
            </div>
        </form>
        @if($hasRemote)
            <form action="{{ route('settings.instance-sync.remote.clear') }}" method="post" class="mt-4" onsubmit="return confirm('آدرس و کلید مقصد پاک شود؟');">
                @csrf
                <button type="submit" class="ds-btn ds-btn-outline text-sm">پاک کردن آدرس و کلید مقصد</button>
            </form>
        @endif
    </section>

    <section class="ds-form-card" style="border-color: var(--ds-primary-border); background: linear-gradient(to bottom, #f0fdf9 0%, #fff 4rem);">
        <h2 class="ds-form-card-title" style="color: var(--ds-primary-dark);">۳. اجرای همگام‌سازی</h2>
        <p class="text-sm" style="color: var(--ds-text-muted); line-height: 1.65; margin-bottom: 1rem;">
            ترتیب ادغام per جدول: ابتدا در صورت فعال بودن «حذف محلی‌های اضافه» ردیف‌هایی که در مقابل نیستند پاک می‌شوند؛ سپس درج / به‌روزرسانی بر اساس شناسهٔ <code style="font-size: 0.8125rem;">id</code> (یا <code style="font-size: 0.8125rem;">key</code> برای تنظیمات). هر دو نصب باید نسخهٔ migrate یکسان داشته باشند.
        </p>
        <form action="{{ route('settings.instance-sync.run') }}" method="post" class="space-y-4">
            @csrf
            <div class="space-y-3 rounded-lg border px-3 py-3" style="border-color: var(--ds-border); background: var(--ds-bg-muted);">
                <div class="flex items-start gap-2">
                    <input type="hidden" name="add_missing" value="0">
                    <input type="checkbox" name="add_missing" id="add_missing" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" {{ old('add_missing', '1') == '1' ? 'checked' : '' }}>
                    <label for="add_missing" class="text-sm" style="line-height: 1.55;">درج رکوردهایی که اینجا نداریم و در مقصد هستند</label>
                </div>
                <div class="flex items-start gap-2">
                    <input type="hidden" name="update_existing" value="0">
                    <input type="checkbox" name="update_existing" id="update_existing" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" {{ old('update_existing', '1') == '1' ? 'checked' : '' }}>
                    <label for="update_existing" class="text-sm" style="line-height: 1.55;">به‌روزرسانی رکوردهای هم‌شناسه با دادهٔ مقصد (مقصد برنده است)</label>
                </div>
                <div class="flex items-start gap-2">
                    <input type="hidden" name="delete_orphans" value="0">
                    <input type="checkbox" name="delete_orphans" id="delete_orphans" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-amber-600 focus:ring-amber-500" {{ old('delete_orphans', '0') == '1' ? 'checked' : '' }}>
                    <label for="delete_orphans" class="text-sm" style="line-height: 1.55;"><strong>خطرناک:</strong> حذف رکوردهای محلی که آن شناسه را در مقصد ندارند (خالی بودن جدول در مقصد یعنی حذف همهٔ محلی)</label>
                </div>
                <div class="flex items-start gap-2">
                    <input type="hidden" name="include_users" value="0">
                    <input type="checkbox" name="include_users" id="include_users" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-stone-600 focus:ring-stone-500" {{ old('include_users', '0') == '1' ? 'checked' : '' }}>
                    <label for="include_users" class="text-sm" style="line-height: 1.55;">شامل جدول کاربران و رمزها (پیش‌فرض: خیر)</label>
                </div>
            </div>
            <div class="rounded-lg border px-3 py-3" style="border-color: var(--ds-border); background: #fff;">
                <div class="flex items-start gap-2">
                    <input type="checkbox" name="confirm_sync" id="confirm_sync" value="1" class="mt-1 h-4 w-4 shrink-0 rounded border-stone-300 text-emerald-600 focus:ring-emerald-500" required {{ old('confirm_sync') ? 'checked' : '' }}>
                    <label for="confirm_sync" class="text-sm" style="line-height: 1.55;">
                        می‌دانم این عمل روی پایگاه دادهٔ همین نصب اثر مستقیم دارد و در صورت نیاز پشتیبان گرفته‌ام.
                    </label>
                </div>
                @error('confirm_sync')
                    <p class="text-red-600 text-sm mt-2 mb-0" role="alert">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="ds-btn ds-btn-primary" data-busy-label="در حال دریافت و ادغام…">
                @include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4'])
                <span class="sync-run-label">همگام‌سازی از سرور مقصد</span>
            </button>
        </form>
    </section>

    <p class="text-sm mt-6" style="color: var(--ds-text-subtle);">
        برای انتقال کامل یک‌جا هنوز از
        <a href="{{ route('settings.data-backup') }}" class="underline decoration-stone-300">پشتیبان و بازیابی</a>
        استفاده کنید؛ این صفحه فقط <strong>ادغام تدریجی</strong> با همان قالب JSON است.
    </p>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.querySelector('form[action="{{ route('settings.instance-sync.run') }}"]');
    var btn = form ? form.querySelector('button[type="submit"]') : null;
    if (form && btn) {
        form.addEventListener('submit', function () {
            if (form.getAttribute('data-submitting') === 'true') return;
            form.setAttribute('data-submitting', 'true');
            btn.disabled = true;
            var busy = btn.getAttribute('data-busy-label');
            var label = btn.querySelector('.sync-run-label');
            if (busy && label) label.textContent = busy;
        });
    }
})();
</script>
@endpush
