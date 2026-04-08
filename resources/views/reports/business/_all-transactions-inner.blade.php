@php
use App\Helpers\FormatHelper;
use App\Models\Invoice;
use App\Models\InvoicePayment;
use App\Models\ContactTransaction;
@endphp
<div class="ds-page" style="max-width: 60rem;">
    <header class="ds-page-header" style="flex-wrap: wrap;">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #ecfdf5, #d1fae5); color: #047857; border-color: #a7f3d0;">
                    @include('components._icons', ['name' => 'clipboard-list', 'class' => 'w-5 h-5'])
                </span>
                همه تراکنش‌ها
            </h1>
            <p class="ds-page-subtitle">بازه: <strong>{{ $fromLabel }}</strong> تا <strong>{{ $toLabel }}</strong> (تاریخ پرداخت)</p>
        </div>
        @if(! request()->query('print'))
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('reports.business.index') }}" class="ds-btn ds-btn-secondary">گزارش‌ها</a>
            <a href="{{ request()->fullUrlWithQuery(['print' => '1']) }}" class="ds-btn ds-btn-outline" target="_blank">نسخهٔ چاپ</a>
            <a href="{{ request()->fullUrlWithQuery(['export' => 'csv', 'print' => null]) }}" class="ds-btn ds-btn-primary">خروجی Excel (CSV)</a>
        </div>
        @endif
    </header>

    @if(! request()->query('print'))
    <form method="get" action="{{ route('reports.business.all-transactions') }}" class="ds-form-card" style="margin-bottom: 1.25rem;">
        @include('reports.business._period-form')
        <button type="submit" class="ds-btn ds-btn-primary mt-3">اعمال فیلتر</button>
    </form>
    @endif

    <p class="text-sm" style="color: var(--ds-text-muted); margin-bottom: 0.75rem;">
        جمع پرداخت‌های فاکتور: {{ FormatHelper::rial($sumInvoice) }}
        — جمع دریافت بدون فاکتور: {{ FormatHelper::rial($sumContactReceive) }}
        — جمع پرداخت بدون فاکتور: {{ FormatHelper::rial($sumContactPay) }}
    </p>

    <div class="ds-form-card" style="overflow-x: auto;">
        <table class="min-w-full text-sm" style="border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--ds-border); text-align: right;">
                    <th class="py-2 px-2">تاریخ</th>
                    <th class="py-2 px-2">نوع</th>
                    <th class="py-2 px-2">مبلغ</th>
                    <th class="py-2 px-2">فاکتور / فروش|خرید</th>
                    <th class="py-2 px-2">مخاطب</th>
                    <th class="py-2 px-2">حساب / روش</th>
                </tr>
            </thead>
            <tbody>
                @forelse($merged as $row)
                    @if($row['kind'] === 'invoice')
                        @php /** @var InvoicePayment $p */ $p = $row['item']; $inv = $p->invoice; @endphp
                        <tr style="border-bottom: 1px solid #e7e5e4;">
                            <td class="py-2 px-2 whitespace-nowrap">{{ FormatHelper::shamsi($p->paid_at) }}</td>
                            <td class="py-2 px-2">پرداخت فاکتور</td>
                            <td class="py-2 px-2">{{ FormatHelper::rial((int) $p->amount) }}</td>
                            <td class="py-2 px-2">
                                @if($inv)
                                    <a href="{{ route('invoices.show', $inv) }}" class="underline">{{ FormatHelper::englishToPersian((string) ($inv->invoice_number ?? $inv->id)) }}</a>
                                    @if($inv->type === Invoice::TYPE_SELL) <span class="ds-badge ds-badge-primary">فروش</span> @else <span class="ds-badge ds-badge-amber">خرید</span> @endif
                                @else — @endif
                            </td>
                            <td class="py-2 px-2">{{ $inv?->contact?->name }}</td>
                            <td class="py-2 px-2">{{ $p->bankAccount?->name ?? ($p->paymentOption?->label ?? '—') }}</td>
                        </tr>
                    @else
                        @php /** @var ContactTransaction $t */ $t = $row['item']; @endphp
                        <tr style="border-bottom: 1px solid #e7e5e4;">
                            <td class="py-2 px-2">{{ FormatHelper::shamsi($t->paid_at) }}</td>
                            <td class="py-2 px-2">{{ $t->type === ContactTransaction::TYPE_RECEIVE ? 'دریافت' : 'پرداخت' }}</td>
                            <td class="py-2 px-2">{{ FormatHelper::rial((int) $t->amount) }}</td>
                            <td class="py-2 px-2">—</td>
                            <td class="py-2 px-2">{{ $t->contact?->name }}</td>
                            <td class="py-2 px-2">{{ $t->bankAccount?->name ?? ($t->paymentOption?->label ?? '—') }}</td>
                        </tr>
                    @endif
                @empty
                    <tr><td colspan="6" class="py-4 text-center" style="color: var(--ds-text-muted);">تراکنشی نیست.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
