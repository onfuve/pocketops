@php use App\Helpers\FormatHelper; @endphp
<div class="ds-page" style="max-width: 48rem;">
    <header class="ds-page-header" style="flex-wrap: wrap;">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #9d174d; border-color: #f9a8d4;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                گزارش بدهکار / بستانکار
            </h1>
            <p class="ds-page-subtitle">مانده بر اساس تراکنش‌های ثبت‌شده در سیستم. مانده <strong>مثبت</strong>: ما به مخاطب بدهکار؛ مانده <strong>منفی</strong>: مخاطب به ما بدهکار.</p>
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
    <form method="get" action="{{ route('reports.business.balances') }}" class="ds-form-card space-y-4" style="margin-bottom: 1.25rem;">
        <div>
            <label class="ds-label">فیلتر مانده</label>
            <select name="balance_filter" class="ds-select" style="max-width: 22rem;">
                <option value="nonzero" @selected($filter === 'nonzero')>فقط مخاطبین با مانده غیرصفر</option>
                <option value="debit_us" @selected($filter === 'debit_us')>ما به مخاطب بدهکار (مانده مثبت)</option>
                <option value="credit_us" @selected($filter === 'credit_us')>مخاطب به ما بدهکار (مانده منفی)</option>
                <option value="all" @selected($filter === 'all')>همه مخاطبین</option>
            </select>
        </div>
        <button type="submit" class="ds-btn ds-btn-primary">اعمال</button>
    </form>
    @endif

    <p class="text-sm" style="color: var(--ds-text-muted); margin-bottom: 0.75rem;">
        جمع مانده‌های مثبت: {{ FormatHelper::rial($sumPositive) }}
        — جمع مانده‌های منفی: {{ FormatHelper::rial($sumNegative) }}
    </p>

    <div class="ds-form-card" style="overflow-x: auto;">
        <table class="min-w-full text-sm" style="border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--ds-border); text-align: right;">
                    <th class="py-2 px-2">مخاطب</th>
                    <th class="py-2 px-2">مانده (ریال)</th>
                    <th class="py-2 px-2">توضیح</th>
                </tr>
            </thead>
            <tbody>
                @forelse($contacts as $c)
                    @php $b = (float) $c->balance; @endphp
                    <tr style="border-bottom: 1px solid #e7e5e4;">
                        <td class="py-2 px-2"><a href="{{ route('contacts.show', $c) }}" class="underline">{{ $c->name }}</a></td>
                        <td class="py-2 px-2 {{ $b != 0 ? ($b > 0 ? 'text-emerald-700' : 'text-rose-700') : '' }}">{{ FormatHelper::rial((int) round($b)) }}</td>
                        <td class="py-2 px-2">
                            @if($b > 0) ما به مخاطب بدهکار
                            @elseif($b < 0) مخاطب به ما بدهکار
                            @else تسویه @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="py-4 text-center" style="color: var(--ds-text-muted);">موردی یافت نشد.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
