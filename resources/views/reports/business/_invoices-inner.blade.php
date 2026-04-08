@php use App\Helpers\FormatHelper; use App\Models\Invoice; @endphp
<div class="ds-page" style="max-width: 60rem;">
    <header class="ds-page-header" style="flex-wrap: wrap;">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #b45309; border-color: #fcd34d;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                گزارش فاکتورها
            </h1>
            <p class="ds-page-subtitle">بازهٔ <strong>تاریخ فاکتور</strong>: {{ $fromLabel }} تا {{ $toLabel }}</p>
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
    <form method="get" action="{{ route('reports.business.invoices') }}" class="ds-form-card space-y-4" style="margin-bottom: 1.25rem;">
        @include('reports.business._period-form')
        <div>
            <label class="ds-label">نوع فاکتور</label>
            <select name="invoice_type" class="ds-select" style="max-width: 14rem;">
                <option value="both" @selected($type === 'both')>فروش و خرید</option>
                <option value="sell" @selected($type === 'sell')>فقط فروش</option>
                <option value="buy" @selected($type === 'buy')>فقط خرید</option>
            </select>
        </div>
        <button type="submit" class="ds-btn ds-btn-primary">اعمال فیلتر</button>
    </form>
    @endif

    <p class="text-sm" style="color: var(--ds-text-muted); margin-bottom: 0.75rem;">
        جمع فروش: {{ FormatHelper::rial($sumSell) }} — جمع خرید: {{ FormatHelper::rial($sumBuy) }}
    </p>

    <div class="ds-form-card" style="overflow-x: auto;">
        <table class="min-w-full text-sm" style="border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--ds-border); text-align: right;">
                    <th class="py-2 px-2">تاریخ</th>
                    <th class="py-2 px-2">شماره</th>
                    <th class="py-2 px-2">نوع</th>
                    <th class="py-2 px-2">مخاطب</th>
                    <th class="py-2 px-2">جمع</th>
                    <th class="py-2 px-2">وضعیت</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    <tr style="border-bottom: 1px solid #e7e5e4;">
                        <td class="py-2 px-2 whitespace-nowrap">{{ FormatHelper::shamsi($inv->date) }}</td>
                        <td class="py-2 px-2"><a href="{{ route('invoices.show', $inv) }}" class="underline">{{ FormatHelper::englishToPersian((string) ($inv->invoice_number ?? $inv->id)) }}</a></td>
                        <td class="py-2 px-2">
                            @if($inv->type === Invoice::TYPE_SELL)<span class="ds-badge ds-badge-primary">فروش</span>@else<span class="ds-badge ds-badge-amber">خرید</span>@endif
                        </td>
                        <td class="py-2 px-2">{{ $inv->contact?->name }}</td>
                        <td class="py-2 px-2">{{ FormatHelper::rial((int) $inv->total) }}</td>
                        <td class="py-2 px-2">{{ $inv->status }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="py-4 text-center" style="color: var(--ds-text-muted);">فاکتوری نیست.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
