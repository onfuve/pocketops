@php
    use App\Helpers\FormatHelper;
@endphp

<div class="space-y-5 max-w-xl">
    @if ($errors->any())
        <div class="ds-alert-error rounded-2xl border px-4 py-3 text-sm" role="alert" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">
            <ul class="mt-1 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div>
        <label class="ds-label">مبلغ (ریال) <span style="color:#b91c1c;">*</span></label>
        <input type="number" name="amount" value="{{ old('amount', $expense->amount) }}" min="1" required
               class="ds-input @error('amount') border-red-500 @enderror" dir="ltr" style="text-align:left;">
        @error('amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="ds-label">کارمزد / مبلغ اضافی کارت (ریال)</label>
        <input type="number" name="fee_amount" value="{{ old('fee_amount', $expense->fee_amount) }}" min="0"
               class="ds-input @error('fee_amount') border-red-500 @enderror" dir="ltr" style="text-align:left;" placeholder="اختیاری — مثلاً کارمزد POS">
        <p class="mt-1 text-xs" style="color: var(--ds-text-muted);">در صورت پرداخت کارتی، کارمزد بانک یا کسر جداگانه را اینجا ثبت کنید. جمع «خروج از حساب» = مبلغ اصلی + این مقدار.</p>
        @error('fee_amount')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="ds-label">تاریخ پرداخت (شمسی) <span style="color:#b91c1c;">*</span></label>
        <div class="flex gap-2 flex-wrap items-center">
            <input type="text" name="paid_at" value="{{ old('paid_at', $defaultPaidAt ?? FormatHelper::shamsi($expense->paid_at)) }}"
                   class="ds-input flex-1 min-w-0 @error('paid_at') border-red-500 @enderror" placeholder="" dir="rtl" autocomplete="off">
            <button type="button" class="ds-btn ds-btn-secondary expense-paid-today" data-today="{{ FormatHelper::shamsi(now()) }}" type="button">امروز</button>
        </div>
        @error('paid_at')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="ds-label">دستهٔ اصلی <span style="color:#b91c1c;">*</span></label>
        <select name="expense_category_id" class="ds-select @error('expense_category_id') border-red-500 @enderror" required>
            @foreach ($expenseCategories as $cat)
                <option value="{{ $cat->id }}" {{ (string) old('expense_category_id', $expense->expense_category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs m-0" style="color: var(--ds-text-muted);">
            افزودن یا حذف دسته در <a href="{{ route('settings.expense-categories') }}" class="font-semibold" style="color:#059669;">تنظیمات دسته‌های هزینه</a>.
        </p>
        @error('expense_category_id')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
    </div>

    <div>
        <label class="ds-label">کارت یا حساب پرداخت</label>
        <select name="payment_option_id" class="ds-select">
            <option value="">—</option>
            @foreach ($paymentOptions as $opt)
                <option value="{{ $opt->id }}" {{ (string) old('payment_option_id', $expense->payment_option_id) === (string) $opt->id ? 'selected' : '' }}>
                    {{ $opt->label ?: ($opt->holder_name ?? $opt->bank_name ?? '—') }}
                    @if($opt->account_number) ({{ $opt->account_number }}) @endif
                </option>
            @endforeach
        </select>
        @if($paymentOptions->isEmpty())
            <p class="mt-1 text-xs" style="color: var(--ds-text-muted);">
                هنوز کارتی ثبت نشده.
                <a href="{{ route('settings.payment-options') }}" class="font-semibold" style="color:#059669;">تنظیمات کارت و حساب</a>
            </p>
        @else
            <p class="mt-1 text-xs" style="color: var(--ds-text-muted);">همان فهرستی که در <a href="{{ route('settings.payment-options') }}" class="font-semibold" style="color:#059669;">تنظیمات کارت و حساب</a> تعریف کرده‌اید.</p>
        @endif
    </div>

    <div>
        <label class="ds-label">یادداشت</label>
        <textarea name="notes" rows="3" class="ds-input @error('notes') border-red-500 @enderror" placeholder="توضیح کوتاه…">{{ old('notes', $expense->notes) }}</textarea>
    </div>

    @include('components._tag-section', [
        'tags' => $tags,
        'entity' => $expense->exists ? $expense : null,
        'embedded' => true,
        'noCollapse' => true,
        'accentColor' => '#c2410c',
    ])
</div>

@push('scripts')
<script>
(function () {
    document.querySelectorAll('.expense-paid-today').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var t = btn.getAttribute('data-today');
            var form = btn.closest('form');
            if (t && form) {
                var inp = form.querySelector('input[name="paid_at"]');
                if (inp) inp.value = t;
            }
        });
    });
})();
</script>
@endpush
