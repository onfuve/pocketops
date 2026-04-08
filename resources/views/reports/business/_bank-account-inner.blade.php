@php
use App\Helpers\FormatHelper;
use App\Models\Invoice;
use App\Models\ContactTransaction;
@endphp
<div class="ds-page" style="max-width: 56rem;">
    <header class="ds-page-header" style="flex-wrap: wrap;">
        <div style="min-width: 0;">
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1d4ed8; border-color: #93c5fd;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-5 h-5'])
                </span>
                تراکنش‌های حساب بانکی
            </h1>
            <p class="ds-page-subtitle meta">
                بازه: <strong>{{ $fromLabel }}</strong> تا <strong>{{ $toLabel }}</strong>
                @if($bank)
                    — حساب: <strong>{{ $bank->name }}</strong>
                @endif
            </p>
        </div>
        @if(! request()->query('print'))
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.business.index') }}" class="ds-btn ds-btn-secondary">گزارش‌ها</a>
            @if($bank)
                <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" class="ds-btn ds-btn-outline" target="_blank">نسخهٔ چاپ</a>
                <a href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'print' => null]) }}" class="ds-btn ds-btn-primary">خروجی Excel (CSV)</a>
            @endif
        </div>
        @endif
    </header>

    @if(! request()->query('print'))
    <form method="get" class="ds-form-card space-y-4" action="{{ route('reports.business.bank-account') }}" style="margin-bottom: 1.25rem;">
        @include('reports.business._period-form')
        <div>
            <label class="ds-label">حساب بانکی</label>
            <select name="bank_account_id" class="ds-select" style="max-width: 22rem;">
                <option value="">— انتخاب کنید —</option>
                @foreach($banks as $b)
                    <option value="{{ $b->id }}" @selected((string) request('bank_account_id') === (string) $b->id)>{{ $b->name }}@if($b->bank_name) — {{ $b->bank_name }}@endif</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="ds-btn ds-btn-primary">اعمال فیلتر</button>
    </form>
    @endif

    @if(!$bank)
        <div class="ds-alert-error" role="status">برای مشاهدهٔ گزارش، حساب بانکی را انتخاب کنید.</div>
    @else
        <section class="ds-form-card" style="margin-bottom: 1.25rem;">
            <h2 class="ds-form-card-title">پرداخت‌های فاکتور</h2>
            <div style="overflow-x: auto;">
                <table class="min-w-full text-sm" style="border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--ds-border); text-align: right;">
                            <th class="py-2 px-2">تاریخ</th>
                            <th class="py-2 px-2">مبلغ</th>
                            <th class="py-2 px-2">فاکتور</th>
                            <th class="py-2 px-2">نوع</th>
                            <th class="py-2 px-2">مخاطب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoicePayments as $p)
                            <tr style="border-bottom: 1px solid #e7e5e4;">
                                <td class="py-2 px-2 whitespace-nowrap">{{ FormatHelper::shamsi($p->paid_at) }}</td>
                                <td class="py-2 px-2">{{ FormatHelper::rial((int) $p->amount) }}</td>
                                <td class="py-2 px-2">
                                    @if($p->invoice)
                                        <a href="{{ route('invoices.show', $p->invoice) }}" class="underline">{{ FormatHelper::englishToPersian((string) ($p->invoice->invoice_number ?? $p->invoice_id)) }}</a>
                                    @endif
                                </td>
                                <td class="py-2 px-2">
                                    @if($p->invoice && $p->invoice->type === Invoice::TYPE_SELL)<span class="ds-badge ds-badge-primary">فروش</span>@elseif($p->invoice)<span class="ds-badge ds-badge-amber">خرید</span>@endif
                                </td>
                                <td class="py-2 px-2">{{ $p->invoice?->contact?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="py-3 text-center" style="color: var(--ds-text-muted);">رکوردی نیست.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-sm mt-2" style="color: var(--ds-text-muted);">جمع این بخش: {{ FormatHelper::rial($sumInvoice) }}</p>
        </section>

        <section class="ds-form-card">
            <h2 class="ds-form-card-title">دریافت / پرداخت (بدون فاکتور)</h2>
            <div style="overflow-x: auto;">
                <table class="min-w-full text-sm" style="border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 1px solid var(--ds-border); text-align: right;">
                            <th class="py-2 px-2">تاریخ</th>
                            <th class="py-2 px-2">مبلغ</th>
                            <th class="py-2 px-2">نوع</th>
                            <th class="py-2 px-2">مخاطب</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($contactTransactions as $t)
                            <tr style="border-bottom: 1px solid #e7e5e4;">
                                <td class="py-2 px-2">{{ FormatHelper::shamsi($t->paid_at) }}</td>
                                <td class="py-2 px-2">{{ FormatHelper::rial((int) $t->amount) }}</td>
                                <td class="py-2 px-2">
                                    @if($t->type === ContactTransaction::TYPE_RECEIVE)<span class="ds-badge ds-badge-primary">دریافت</span>@else<span class="ds-badge ds-badge-amber">پرداخت</span>@endif
                                </td>
                                <td class="py-2 px-2">{{ $t->contact?->name }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-3 text-center" style="color: var(--ds-text-muted);">رکوردی نیست.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <p class="text-sm mt-2" style="color: var(--ds-text-muted);">جمع این بخش: {{ FormatHelper::rial($sumContact) }}</p>
        </section>
    @endif
</div>
