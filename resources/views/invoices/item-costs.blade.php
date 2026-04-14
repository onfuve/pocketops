@php
    use App\Helpers\FormatHelper;
    use App\Models\Invoice;
@endphp
@extends('layouts.app')

@section('title', 'اتصال خرید و هزینه به فروش — ' . config('app.name'))

@section('content')
<div class="ds-page cost-link-page">
    <div class="cost-link-page-head">
        <div class="min-w-0">
            <h1 class="ds-page-title m-0" style="font-size: 1.125rem;">
                <span class="ds-page-title-icon" style="background: #ecfdf5; color: #047857; border-color: #a7f3d0; width: 2rem; height: 2rem;">
                    @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4'])
                </span>
                اتصال خرید و هزینه به فروش
                <span class="font-normal text-stone-500 text-base mr-1">#{{ $invoice->invoice_number ?: $invoice->id }} · {{ $invoice->contact->name }}</span>
            </h1>
        </div>
        <a href="{{ route('invoices.show', $invoice) }}" class="ds-btn ds-btn-outline shrink-0 text-sm py-2">بازگشت</a>
    </div>

    @if (session('success'))
        <div class="ds-alert-success mb-3 rounded-lg px-3 py-2 text-sm">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="mb-3 rounded-lg border px-3 py-2 text-sm" style="border-color: #fecaca; background: #fef2f2; color: #b91c1c;">
            <ul class="list-disc list-inside m-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="cost-link-shell">
        @php $mp = $invoice->marginPercentFromLinkedBuys(); @endphp
        <div class="cost-link-stats">
            <span><span class="cost-link-stat-k">فاکتور</span> <strong class="font-vazir">{{ FormatHelper::rial($invoice->total) }}</strong></span>
            <span class="cost-link-stat-sep" aria-hidden="true"></span>
            <span><span class="cost-link-stat-k">خرید</span> <strong class="font-vazir">{{ FormatHelper::rial($invoice->totalLinkedBuyCostRial()) }}</strong></span>
            <span class="cost-link-stat-sep" aria-hidden="true"></span>
            <span><span class="cost-link-stat-k">هزینه</span> <strong class="font-vazir">{{ FormatHelper::rial($invoice->totalLinkedExpenseCostRial()) }}</strong></span>
            <span class="cost-link-stat-sep" aria-hidden="true"></span>
            <span><span class="cost-link-stat-k">جمع تمام‌شده</span> <strong class="font-vazir">{{ FormatHelper::rial($invoice->totalAttributedCostRial()) }}</strong></span>
            <span class="cost-link-stat-sep" aria-hidden="true"></span>
            <span><span class="cost-link-stat-k">سود</span> <strong class="font-vazir">{{ FormatHelper::rial($invoice->grossProfitFromLinkedBuysRial()) }}</strong></span>
            <span class="cost-link-stat-sep" aria-hidden="true"></span>
            <span><span class="cost-link-stat-k">حاشیه</span> <strong class="font-vazir">{{ $mp !== null ? FormatHelper::englishToPersian((string) $mp).' %' : '—' }}</strong></span>
        </div>

        <div class="cost-link-section">
            <div class="cost-link-section-h">ارتباطات فعلی</div>
            <div class="overflow-x-auto">
                <table class="cost-link-table cost-link-table-existing">
                    <thead>
                        <tr>
                            <th class="w-8">#</th>
                            <th>ردیف فروش</th>
                            <th class="whitespace-nowrap">خرید</th>
                            <th class="whitespace-nowrap">هزینه</th>
                            <th class="whitespace-nowrap">جمع</th>
                            <th>ارتباطات</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($invoice->items as $idx => $item)
                            <tr>
                                <td class="font-vazir text-stone-500">{{ FormatHelper::englishToPersian((string) ($idx + 1)) }}</td>
                                <td>
                                    <div class="font-medium text-stone-800 leading-snug">{{ \Illuminate\Support\Str::limit($item->description, 72) }}</div>
                                    <div class="text-xs text-stone-500 font-vazir mt-0.5">{{ FormatHelper::numberFormat($item->quantity) }} × {{ FormatHelper::rial($item->amount) }}</div>
                                </td>
                                <td class="font-vazir whitespace-nowrap">{{ FormatHelper::rial($item->totalLinkedBuyCostRial()) }}</td>
                                <td class="font-vazir whitespace-nowrap">{{ FormatHelper::rial($item->totalLinkedExpenseCostRial()) }}</td>
                                <td class="font-vazir whitespace-nowrap font-medium">{{ FormatHelper::rial($item->totalAttributedCostRial()) }}</td>
                                <td>
                                    @if ($item->sellBuyCostLinks->isEmpty() && $item->sellExpenseCostLinks->isEmpty())
                                        <span class="text-stone-400 text-sm">—</span>
                                    @else
                                        <ul class="cost-link-mini-list m-0 p-0 list-none space-y-2">
                                            @foreach ($item->sellBuyCostLinks as $link)
                                                @php $bi = $link->buyItem; $inv = $bi?->invoice; @endphp
                                                <li class="text-sm leading-snug flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0">
                                                    <span>
                                                        <span class="text-emerald-800 font-medium">خرید</span>
                                                        @if ($inv)
                                                            <a href="{{ route('invoices.show', $inv) }}" class="text-emerald-700 font-medium hover:underline">رسید {{ $inv->invoice_number ?: $inv->id }}</a>
                                                            <span class="text-stone-600"> · {{ \Illuminate\Support\Str::limit($bi->description, 36) }} · {{ FormatHelper::numberFormat($link->quantity) }} @ {{ FormatHelper::numberFormat($bi->purchaseUnitCostRial()) }}</span>
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                    @if(auth()->user()->canModule('invoices', \App\Models\User::ABILITY_EDIT))
                                                    <form action="{{ route('invoices.item-costs.destroy', [$invoice, $link->id]) }}" method="post" class="inline shrink-0" onsubmit='return confirm(@json("حذف این ارتباط خرید؟"))'>
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:underline p-0 bg-transparent border-0 cursor-pointer">حذف</button>
                                                    </form>
                                                    @endif
                                                </li>
                                            @endforeach
                                            @foreach ($item->sellExpenseCostLinks as $ex)
                                                @php $be = $ex->businessExpense; @endphp
                                                <li class="text-sm leading-snug flex flex-wrap items-baseline justify-between gap-x-2 gap-y-0">
                                                    <span>
                                                        <span class="text-amber-800 font-medium">هزینه</span>
                                                        @if ($be)
                                                            @if(auth()->user()->canModule('expenses', \App\Models\User::ABILITY_VIEW))
                                                            <a href="{{ route('expenses.edit', $be) }}" class="text-amber-800 font-medium hover:underline">#{{ $be->id }}</a>
                                                            @else
                                                            <span class="font-medium text-amber-900">#{{ $be->id }}</span>
                                                            @endif
                                                            <span class="text-stone-600"> · {{ $be->expenseCategory?->name ?? '—' }} · {{ FormatHelper::shamsi($be->paid_at) }} · <span class="font-vazir">{{ FormatHelper::rial($ex->amount_rial) }}</span></span>
                                                        @else
                                                            —
                                                        @endif
                                                    </span>
                                                    @if(auth()->user()->canModule('invoices', \App\Models\User::ABILITY_EDIT) && auth()->user()->canModule('expenses', \App\Models\User::ABILITY_VIEW))
                                                    <form action="{{ route('invoices.item-costs.expense.destroy', [$invoice, $ex]) }}" method="post" class="inline shrink-0" onsubmit='return confirm(@json("حذف این مبلغ هزینه؟"))'>
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="text-xs text-red-600 hover:underline p-0 bg-transparent border-0 cursor-pointer">حذف</button>
                                                    </form>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if(auth()->user()->canModule('invoices', \App\Models\User::ABILITY_EDIT))
        <div class="cost-link-section cost-link-section-add" id="cost-link-add-card">
            <div class="cost-link-section-h">افزودن ارتباط</div>
            @if (!$hasBuyReceipts)
                <p class="text-sm m-0 text-stone-600">رسید خریدی نیست — <a href="{{ route('invoices.create', ['type' => Invoice::TYPE_BUY]) }}" class="font-semibold text-emerald-700">رسید جدید</a></p>
            @else
                <p class="text-xs m-0 mb-3 text-stone-500">فروش را انتخاب کنید، رسید را جستجو کنید، یک ردیف از جدول انتخاب کنید، تعداد را وارد و ثبت کنید.</p>
                <form id="cost-link-form" action="{{ route('invoices.item-costs.store', $invoice) }}" method="post" class="cost-link-form">
                    @csrf
                    <div class="cost-link-form-toolbar">
                        <label class="cost-link-label cost-link-tb-label cost-link-tb-sell-l" for="cost-link-sell">ردیف فروش</label>
                        <label class="cost-link-label cost-link-tb-label cost-link-tb-search-l" for="cost-link-buy-search">جستجوی رسید خرید</label>
                        <label class="cost-link-label cost-link-tb-label cost-link-tb-qty-l" for="cost-link-qty">تعداد</label>
                        <select name="sell_invoice_item_id" id="cost-link-sell" class="ds-select cost-link-control cost-link-tb-sell-c w-full text-sm" required>
                            @foreach ($invoice->items as $item)
                                <option value="{{ $item->id }}" @selected((string) old('sell_invoice_item_id', $invoice->items->first()?->id) === (string) $item->id)>{{ \Illuminate\Support\Str::limit($item->description, 55) }} — {{ FormatHelper::numberFormat($item->quantity) }} عدد</option>
                            @endforeach
                        </select>
                        <div class="cost-link-search-row cost-link-tb-search-c">
                            <div class="relative flex-1 min-w-0">
                                <input type="search" id="cost-link-buy-search" class="ds-input cost-link-control w-full text-sm" placeholder="شماره، فروشنده، شرح…" autocomplete="off" dir="rtl">
                                <span id="cost-link-buy-search-loading" class="hidden text-xs absolute left-2 top-1/2 -translate-y-1/2 text-stone-400">…</span>
                            </div>
                            <button type="button" id="cost-link-buy-search-btn" class="ds-btn ds-btn-secondary cost-link-search-btn shrink-0 text-sm" title="جستجو">
                                @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
                            </button>
                        </div>
                        <input type="text" name="quantity" id="cost-link-qty" class="ds-input cost-link-control cost-link-tb-qty-c w-full text-sm" dir="ltr" style="text-align:left;" required placeholder="1" value="{{ old('quantity', '1') }}">
                    </div>

                    <div class="cost-link-form-panel">
                        <div id="cost-link-buy-results" class="hidden rounded border max-h-36 overflow-y-auto bg-white text-sm border-stone-200" role="listbox"></div>
                        <p id="cost-link-buy-results-empty" class="hidden text-xs m-0 py-2 text-stone-500">نتیجه‌ای نیست.</p>
                        <div id="cost-link-buy-selected" class="hidden flex flex-wrap items-center gap-x-2 gap-y-1 text-xs py-1.5 px-2 rounded bg-emerald-50 border border-emerald-200">
                            <span id="cost-link-selected-label" class="font-medium text-stone-800"></span>
                            <a id="cost-link-selected-open" href="#" target="_blank" rel="noopener" class="text-emerald-700 hover:underline">باز کردن</a>
                            <button type="button" id="cost-link-buy-change" class="text-stone-600 hover:text-stone-900 underline bg-transparent border-0 cursor-pointer p-0 text-xs mr-auto">تغییر</button>
                        </div>
                        <div id="cost-link-buy-lines-wrap" class="hidden mt-2">
                            <div class="overflow-x-auto rounded border border-stone-200 bg-white">
                                <table class="cost-link-table cost-link-lines-table min-w-full text-sm">
                                    <thead>
                                        <tr>
                                            <th class="w-8"></th>
                                            <th>شرح</th>
                                            <th class="whitespace-nowrap">مانده</th>
                                            <th class="whitespace-nowrap">واحد</th>
                                        </tr>
                                    </thead>
                                    <tbody id="cost-link-buy-lines-body"></tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="cost-link-form-actions">
                        <button type="submit" id="cost-link-submit" class="ds-btn ds-btn-primary text-sm cost-link-submit-btn" disabled>ثبت ارتباط</button>
                        <span id="cost-link-submit-hint" class="text-xs text-stone-500">رسید و ردیف خرید را انتخاب کنید.</span>
                    </div>
                </form>
            @endif
        </div>

        @if($canLinkExpenses ?? false)
        <div class="cost-link-section cost-link-section-add cost-link-exp-add">
            <div class="cost-link-section-h">وصل هزینهٔ عملیاتی</div>
            <p class="text-xs m-0 mb-3 text-stone-500">از <a href="{{ route('expenses.index') }}" class="font-semibold text-amber-800 underline">هزینه‌های ثبت‌شده</a>، مبلغی را به ردیف فروش وصل کنید. جمع مبالغ وصل‌شده از هر هزینه نمی‌تواند از مبلغ کل آن هزینه (با کارمزد) بیشتر شود. ثبت دوباره همان هزینه و همان ردیف، مبلغ را جمع می‌کند.</p>
            <form id="cost-link-expense-form" action="{{ route('invoices.item-costs.expense.store', $invoice) }}" method="post" class="cost-link-form">
                @csrf
                <input type="hidden" name="business_expense_id" id="cost-link-exp-id" value="{{ old('business_expense_id') }}">
                <div class="cost-link-form-toolbar cost-link-exp-toolbar">
                    <label class="cost-link-label cost-link-tb-label cost-link-exp-sell-l" for="cost-link-exp-sell">ردیف فروش</label>
                    <label class="cost-link-label cost-link-tb-label cost-link-exp-search-l" for="cost-link-exp-search">جستجوی هزینه</label>
                    <label class="cost-link-label cost-link-tb-label cost-link-exp-amt-l" for="cost-link-exp-amt">مبلغ هزینه (ریال)</label>
                    <select name="sell_invoice_item_id" id="cost-link-exp-sell" class="ds-select cost-link-control cost-link-exp-sell-c w-full text-sm" required>
                        @foreach ($invoice->items as $item)
                            <option value="{{ $item->id }}" @selected((string) old('sell_invoice_item_id', $invoice->items->first()?->id) === (string) $item->id)>{{ \Illuminate\Support\Str::limit($item->description, 55) }} — {{ FormatHelper::numberFormat($item->quantity) }} عدد</option>
                        @endforeach
                    </select>
                    <div class="cost-link-search-row cost-link-exp-search-c">
                        <div class="relative flex-1 min-w-0">
                            <input type="search" id="cost-link-exp-search" class="ds-input cost-link-control w-full text-sm" placeholder="یادداشت، دسته، شناسه…" autocomplete="off" dir="rtl">
                            <span id="cost-link-exp-search-loading" class="hidden text-xs absolute left-2 top-1/2 -translate-y-1/2 text-stone-400">…</span>
                        </div>
                        <button type="button" id="cost-link-exp-search-btn" class="ds-btn ds-btn-secondary cost-link-search-btn shrink-0 text-sm" title="جستجو">
                            @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
                        </button>
                    </div>
                    <input type="text" name="amount_rial" id="cost-link-exp-amt" class="ds-input cost-link-control cost-link-exp-amt-c w-full text-sm" dir="ltr" style="text-align:left;" placeholder="عدد انگلیسی یا فارسی" value="{{ old('amount_rial') }}">
                </div>
                <div class="cost-link-form-panel">
                    <div id="cost-link-exp-results" class="hidden rounded border max-h-36 overflow-y-auto bg-white text-sm border-stone-200" role="listbox"></div>
                    <p id="cost-link-exp-results-empty" class="hidden text-xs m-0 py-2 text-stone-500">نتیجه‌ای نیست.</p>
                    <div id="cost-link-exp-selected" class="hidden flex flex-wrap items-center gap-x-2 gap-y-1 text-xs py-1.5 px-2 rounded bg-amber-50 border border-amber-200">
                        <span id="cost-link-exp-selected-label" class="font-medium text-stone-800"></span>
                        <a id="cost-link-exp-selected-open" href="#" target="_blank" rel="noopener" class="text-amber-900 hover:underline font-medium">باز کردن هزینه</a>
                        <button type="button" id="cost-link-exp-change" class="text-stone-600 hover:text-stone-900 underline bg-transparent border-0 cursor-pointer p-0 text-xs mr-auto">تغییر</button>
                    </div>
                </div>
                <div class="cost-link-form-actions">
                    <button type="submit" id="cost-link-exp-submit" class="ds-btn ds-btn-primary text-sm cost-link-submit-btn" style="background:#b45309;border-color:#b45309;" disabled>ثبت هزینه</button>
                    <span id="cost-link-exp-hint" class="text-xs text-stone-500">هزینه را انتخاب کنید و مبلغ هزینه را وارد کنید.</span>
                </div>
            </form>
        </div>
        @endif
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
.cost-link-page { max-width: 56rem; }
.cost-link-page-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; margin-bottom: 0.75rem; flex-wrap: wrap; }
.cost-link-shell { border: 1px solid #e7e5e4; border-radius: 0.5rem; background: #fff; overflow: hidden; }
.cost-link-stats { display: flex; flex-wrap: wrap; align-items: center; gap: 0.35rem 0.75rem; padding: 0.5rem 0.75rem; background: #fafaf9; border-bottom: 1px solid #e7e5e4; font-size: 0.8125rem; }
.cost-link-stat-k { color: #78716c; margin-left: 0.25rem; }
.cost-link-stat-sep { width: 1px; height: 0.875rem; background: #d6d3d1; display: inline-block; }
.cost-link-section { padding: 0.5rem 0.75rem 0.75rem; border-bottom: 1px solid #e7e5e4; }
.cost-link-section:last-child { border-bottom: none; }
.cost-link-section-h { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em; color: #78716c; margin-bottom: 0.35rem; }
.cost-link-table { width: 100%; border-collapse: collapse; text-align: right; }
.cost-link-table th { padding: 0.35rem 0.5rem; font-size: 0.6875rem; font-weight: 600; color: #57534e; background: #f5f5f4; border-bottom: 1px solid #e7e5e4; }
.cost-link-table td { padding: 0.4rem 0.5rem; border-bottom: 1px solid #f5f5f4; vertical-align: top; font-size: 0.8125rem; }
.cost-link-table-existing tbody tr:last-child td { border-bottom: none; }
.cost-link-mini-list { max-width: 28rem; }
.cost-link-form { margin: 0; }
.cost-link-form-toolbar {
    display: grid;
    grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.95fr) 6.75rem;
    grid-template-rows: auto minmax(2.5rem, auto);
    column-gap: 1rem;
    row-gap: 0.35rem;
    align-items: stretch;
}
.cost-link-form-toolbar .cost-link-tb-sell-l { grid-column: 1; grid-row: 1; }
.cost-link-form-toolbar .cost-link-tb-search-l { grid-column: 2; grid-row: 1; }
.cost-link-form-toolbar .cost-link-tb-qty-l { grid-column: 3; grid-row: 1; }
.cost-link-form-toolbar .cost-link-tb-sell-c { grid-column: 1; grid-row: 2; min-width: 0; }
.cost-link-form-toolbar .cost-link-tb-search-c { grid-column: 2; grid-row: 2; min-width: 0; }
.cost-link-form-toolbar .cost-link-tb-qty-c { grid-column: 3; grid-row: 2; }
@media (max-width: 899px) {
    .cost-link-form-toolbar {
        grid-template-columns: 1fr;
        grid-template-rows: auto;
    }
    .cost-link-form-toolbar .cost-link-tb-sell-l { grid-column: 1; grid-row: 1; }
    .cost-link-form-toolbar .cost-link-tb-search-l { grid-column: 1; grid-row: 3; }
    .cost-link-form-toolbar .cost-link-tb-qty-l { grid-column: 1; grid-row: 5; }
    .cost-link-form-toolbar .cost-link-tb-sell-c { grid-column: 1; grid-row: 2; }
    .cost-link-form-toolbar .cost-link-tb-search-c { grid-column: 1; grid-row: 4; }
    .cost-link-form-toolbar .cost-link-tb-qty-c { grid-column: 1; grid-row: 6; }
}
.cost-link-label {
    display: block;
    font-size: 0.6875rem;
    font-weight: 600;
    color: #57534e;
    margin-bottom: 0.35rem;
    line-height: 1.2;
    min-height: 1.05rem;
}
.cost-link-form-toolbar .cost-link-tb-label {
    margin-bottom: 0;
    align-self: end;
}
.cost-link-control {
    min-height: 2.5rem;
    box-sizing: border-box;
}
.cost-link-search-row {
    display: flex;
    align-items: stretch;
    gap: 0.375rem;
    min-height: 2.5rem;
}
.cost-link-search-row .relative { display: flex; align-items: stretch; }
.cost-link-search-row .ds-input { align-self: stretch; }
.cost-link-search-btn {
    min-width: 2.5rem;
    min-height: 2.5rem;
    padding-left: 0.65rem;
    padding-right: 0.65rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
}
.cost-link-form-toolbar .cost-link-tb-qty-c {
    text-align: left;
    padding-left: 0.5rem;
    padding-right: 0.5rem;
}
.cost-link-form-panel {
    margin-top: 0.65rem;
    padding-top: 0.65rem;
    border-top: 1px solid #e7e5e4;
}
.cost-link-form-actions {
    margin-top: 0.75rem;
    padding-top: 0.65rem;
    border-top: 1px solid #e7e5e4;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 0.5rem 0.75rem;
}
.cost-link-submit-btn {
    min-height: 2.5rem;
    padding-left: 1rem;
    padding-right: 1rem;
}
.cost-link-result-row { padding: 0.4rem 0.5rem; border-bottom: 1px solid #e7e5e4; cursor: pointer; text-align: right; width: 100%; display: block; border-left: 0; border-right: 0; background: #fff; font: inherit; }
.cost-link-result-row:last-child { border-bottom: none; }
.cost-link-result-row:hover, .cost-link-result-row:focus { background: #ecfdf5; outline: none; }
.cost-link-lines-table tbody tr.cost-link-line-disabled { opacity: 0.4; pointer-events: none; }
.cost-link-lines-table tbody tr:not(.cost-link-line-disabled) { cursor: pointer; }
.cost-link-lines-table tbody tr.cost-link-line-active { background: #ecfdf5; }
.cost-link-lines-table th, .cost-link-lines-table td { padding: 0.3rem 0.45rem; font-size: 0.75rem; }
.cost-link-exp-toolbar { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1.95fr) 7.5rem; grid-template-rows: auto minmax(2.5rem, auto); column-gap: 1rem; row-gap: 0.35rem; align-items: stretch; }
.cost-link-exp-toolbar .cost-link-exp-sell-l { grid-column: 1; grid-row: 1; }
.cost-link-exp-toolbar .cost-link-exp-search-l { grid-column: 2; grid-row: 1; }
.cost-link-exp-toolbar .cost-link-exp-amt-l { grid-column: 3; grid-row: 1; }
.cost-link-exp-toolbar .cost-link-exp-sell-c { grid-column: 1; grid-row: 2; min-width: 0; }
.cost-link-exp-toolbar .cost-link-exp-search-c { grid-column: 2; grid-row: 2; min-width: 0; }
.cost-link-exp-toolbar .cost-link-exp-amt-c { grid-column: 3; grid-row: 2; text-align: left; padding-left: 0.5rem; padding-right: 0.5rem; }
.cost-link-exp-toolbar .cost-link-tb-label { margin-bottom: 0; align-self: end; }
@media (max-width: 899px) {
    .cost-link-exp-toolbar { grid-template-columns: 1fr; grid-template-rows: auto; }
    .cost-link-exp-toolbar .cost-link-exp-sell-l { grid-column: 1; grid-row: 1; }
    .cost-link-exp-toolbar .cost-link-exp-search-l { grid-column: 1; grid-row: 3; }
    .cost-link-exp-toolbar .cost-link-exp-amt-l { grid-column: 1; grid-row: 5; }
    .cost-link-exp-toolbar .cost-link-exp-sell-c { grid-column: 1; grid-row: 2; }
    .cost-link-exp-toolbar .cost-link-exp-search-c { grid-column: 1; grid-row: 4; }
    .cost-link-exp-toolbar .cost-link-exp-amt-c { grid-column: 1; grid-row: 6; }
}
.cost-link-result-row--exp:hover, .cost-link-result-row--exp:focus { background: #fffbeb; }
.cost-link-result-row.is-disabled { opacity: 0.45; cursor: not-allowed; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var searchUrl = @json(route('invoices.item-costs.search-buy', $invoice));
    var buyReceiptsBase = @json(rtrim(url('/invoices/'.$invoice->id.'/item-costs/buy-receipts'), '/'));
    var linesUrl = function (buyId) { return buyReceiptsBase + '/' + buyId + '/lines'; };
    var invoiceShowBase = @json(url('/invoices'));

    var searchInput = document.getElementById('cost-link-buy-search');
    var searchBtn = document.getElementById('cost-link-buy-search-btn');
    var resultsEl = document.getElementById('cost-link-buy-results');
    var emptyEl = document.getElementById('cost-link-buy-results-empty');
    var loadingEl = document.getElementById('cost-link-buy-search-loading');
    var selectedWrap = document.getElementById('cost-link-buy-selected');
    var selectedLabel = document.getElementById('cost-link-selected-label');
    var selectedOpen = document.getElementById('cost-link-selected-open');
    var changeBtn = document.getElementById('cost-link-buy-change');
    var linesWrap = document.getElementById('cost-link-buy-lines-wrap');
    var linesBody = document.getElementById('cost-link-buy-lines-body');
    var submitBtn = document.getElementById('cost-link-submit');
    var submitHint = document.getElementById('cost-link-submit-hint');
    var form = document.getElementById('cost-link-form');

    if (!form || !searchInput) return;

    var debounceTimer;

    function setLoading(on) {
        if (loadingEl) loadingEl.classList.toggle('hidden', !on);
    }

    function rial(n) {
        return new Intl.NumberFormat('fa-IR').format(Math.round(Number(n) || 0));
    }

    function num(n) {
        return new Intl.NumberFormat('fa-IR', { maximumFractionDigits: 4 }).format(Number(n) || 0);
    }

    function clearLines() {
        linesBody.innerHTML = '';
        linesWrap.classList.add('hidden');
        submitBtn.disabled = true;
        submitHint.textContent = 'رسید و ردیف خرید را انتخاب کنید.';
    }

    function clearSelection() {
        selectedWrap.classList.add('hidden');
        resultsEl.classList.add('hidden');
        resultsEl.innerHTML = '';
        emptyEl.classList.add('hidden');
        clearLines();
    }

    function runSearch() {
        var q = (searchInput.value || '').trim();
        resultsEl.innerHTML = '';
        emptyEl.classList.add('hidden');
        if (q.length < 1) {
            resultsEl.classList.add('hidden');
            return;
        }
        setLoading(true);
        fetch(searchUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                setLoading(false);
                var list = (data && data.receipts) ? data.receipts : [];
                if (!list.length) {
                    resultsEl.classList.add('hidden');
                    emptyEl.classList.remove('hidden');
                    return;
                }
                emptyEl.classList.add('hidden');
                resultsEl.classList.remove('hidden');
                list.forEach(function (rec) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'cost-link-result-row';
                    row.setAttribute('role', 'option');
                    row.innerHTML = '<span class="font-medium">' + (rec.invoice_number || rec.id) + '</span> <span class="text-stone-500">' + (rec.contact_name || '') + ' · ' + (rec.date_label || '') + ' · ' + rial(rec.total) + '</span>';
                    row.addEventListener('click', function () { selectReceipt(rec); });
                    resultsEl.appendChild(row);
                });
            })
            .catch(function () {
                setLoading(false);
                resultsEl.classList.add('hidden');
            });
    }

    function selectReceipt(rec) {
        resultsEl.classList.add('hidden');
        emptyEl.classList.add('hidden');
        selectedWrap.classList.remove('hidden');
        selectedLabel.textContent = 'رسید ' + (rec.invoice_number || rec.id) + ' — ' + (rec.contact_name || '—');
        selectedOpen.href = invoiceShowBase + '/' + rec.id;

        setLoading(true);
        linesBody.innerHTML = '';
        linesWrap.classList.remove('hidden');
        fetch(linesUrl(rec.id), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                setLoading(false);
                var items = (data && data.items) ? data.items : [];
                linesBody.innerHTML = '';
                if (!items.length) {
                    submitBtn.disabled = true;
                    submitHint.textContent = 'این رسید ردیفی ندارد.';
                    return;
                }
                items.forEach(function (it) {
                    var tr = document.createElement('tr');
                    var disabled = (Number(it.remaining) <= 0);
                    if (disabled) tr.classList.add('cost-link-line-disabled');
                    tr.innerHTML = '<td><input type="radio" name="buy_invoice_item_id" value="' + it.id + '" ' + (disabled ? 'disabled' : '') + '></td>' +
                        '<td>' + escapeHtml(it.description || '') + '</td>' +
                        '<td class="font-vazir">' + num(it.remaining) + '</td>' +
                        '<td class="font-vazir">' + rial(it.unit_cost_rial) + '</td>';
                    if (!disabled) {
                        tr.addEventListener('click', function (e) {
                            if (e.target && e.target.name === 'buy_invoice_item_id') return;
                            var radio = tr.querySelector('input[type=radio]');
                            if (radio) {
                                radio.checked = true;
                                tr.parentElement.querySelectorAll('tr').forEach(function (x) { x.classList.remove('cost-link-line-active'); });
                                tr.classList.add('cost-link-line-active');
                                submitBtn.disabled = false;
                                submitHint.textContent = '';
                            }
                        });
                        tr.querySelector('input[type=radio]').addEventListener('change', function () {
                            tr.parentElement.querySelectorAll('tr').forEach(function (x) { x.classList.remove('cost-link-line-active'); });
                            tr.classList.add('cost-link-line-active');
                            submitBtn.disabled = false;
                            submitHint.textContent = '';
                        });
                    }
                    linesBody.appendChild(tr);
                });
                submitBtn.disabled = true;
                submitHint.textContent = 'یک ردیف از جدول انتخاب کنید.';
            })
            .catch(function () {
                setLoading(false);
                submitHint.textContent = 'خطا در بارگذاری ردیف‌ها.';
            });
    }

    function escapeHtml(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    searchBtn.addEventListener('click', runSearch);
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { e.preventDefault(); runSearch(); }
    });
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(runSearch, 350);
    });

    changeBtn.addEventListener('click', function () {
        clearSelection();
        searchInput.focus();
    });

    form.addEventListener('submit', function (e) {
        var buy = form.querySelector('input[name=buy_invoice_item_id]:checked');
        if (!buy || !buy.value) {
            e.preventDefault();
            submitHint.textContent = 'ردیف رسید خرید را انتخاب کنید.';
        }
    });
})();

(function () {
    var expSearchUrl = @json(route('invoices.item-costs.search-expenses', $invoice));
    var expenseEditBase = @json(rtrim(url('/expenses'), '/'));
    var expForm = document.getElementById('cost-link-expense-form');
    if (!expForm) return;

    var expSearch = document.getElementById('cost-link-exp-search');
    var expSearchBtn = document.getElementById('cost-link-exp-search-btn');
    var expResults = document.getElementById('cost-link-exp-results');
    var expEmpty = document.getElementById('cost-link-exp-results-empty');
    var expLoading = document.getElementById('cost-link-exp-search-loading');
    var expSelWrap = document.getElementById('cost-link-exp-selected');
    var expSelLabel = document.getElementById('cost-link-exp-selected-label');
    var expSelOpen = document.getElementById('cost-link-exp-selected-open');
    var expChange = document.getElementById('cost-link-exp-change');
    var expIdInput = document.getElementById('cost-link-exp-id');
    var expAmt = document.getElementById('cost-link-exp-amt');
    var expSubmit = document.getElementById('cost-link-exp-submit');
    var expHint = document.getElementById('cost-link-exp-hint');
    var selectedExp = null;
    var debounceExp;

    function setExpLoading(on) {
        if (expLoading) expLoading.classList.toggle('hidden', !on);
    }

    function rial(n) {
        return new Intl.NumberFormat('fa-IR').format(Math.round(Number(n) || 0));
    }

    function validateExpSubmit() {
        var id = expIdInput && expIdInput.value;
        var amt = expAmt && String(expAmt.value).trim();
        if (id && amt) {
            expSubmit.disabled = false;
            expHint.textContent = '';
        } else {
            expSubmit.disabled = true;
            expHint.textContent = id ? 'مبلغ هزینه را وارد کنید.' : 'هزینه را از نتایج جستجو انتخاب کنید.';
        }
    }

    function clearExpSelection() {
        selectedExp = null;
        if (expIdInput) expIdInput.value = '';
        if (expSelWrap) expSelWrap.classList.add('hidden');
        if (expResults) {
            expResults.classList.add('hidden');
            expResults.innerHTML = '';
        }
        if (expEmpty) expEmpty.classList.add('hidden');
        validateExpSubmit();
    }

    function runExpSearch() {
        var q = (expSearch && expSearch.value || '').trim();
        if (expResults) expResults.innerHTML = '';
        if (expEmpty) expEmpty.classList.add('hidden');
        if (q.length < 1) {
            if (expResults) expResults.classList.add('hidden');
            return;
        }
        setExpLoading(true);
        fetch(expSearchUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                setExpLoading(false);
                var list = (data && data.expenses) ? data.expenses : [];
                if (!list.length) {
                    if (expResults) expResults.classList.add('hidden');
                    if (expEmpty) expEmpty.classList.remove('hidden');
                    return;
                }
                if (expEmpty) expEmpty.classList.add('hidden');
                if (expResults) expResults.classList.remove('hidden');
                list.forEach(function (ex) {
                    var row = document.createElement('button');
                    row.type = 'button';
                    var disabled = Number(ex.remaining) <= 0;
                    row.className = 'cost-link-result-row cost-link-result-row--exp' + (disabled ? ' is-disabled' : '');
                    row.disabled = disabled;
                    row.innerHTML = '<span class="font-medium font-vazir">#' + ex.id + '</span> <span class="text-stone-600">' + (ex.category || '') + ' · ' + (ex.paid_at_label || '') + '</span><br><span class="text-stone-500 text-xs">خروج ' + rial(ex.total_outlay) + ' · مانده ' + rial(ex.remaining) + '</span>' + (ex.notes ? '<br><span class="text-stone-400 text-xs">' + escapeExp(ex.notes) + '</span>' : '');
                    if (!disabled) {
                        row.addEventListener('click', function () { selectExpense(ex); });
                    }
                    expResults.appendChild(row);
                });
            })
            .catch(function () { setExpLoading(false); if (expResults) expResults.classList.add('hidden'); });
    }

    function escapeExp(s) {
        var d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function selectExpense(ex) {
        selectedExp = ex;
        if (expResults) expResults.classList.add('hidden');
        if (expEmpty) expEmpty.classList.add('hidden');
        if (expSelWrap) expSelWrap.classList.remove('hidden');
        if (expIdInput) expIdInput.value = String(ex.id);
        if (expSelLabel) expSelLabel.textContent = 'هزینه #' + ex.id + ' — مانده ' + rial(ex.remaining) + ' ریال';
        if (expSelOpen) expSelOpen.href = expenseEditBase + '/' + ex.id + '/edit';
        if (expAmt && (!expAmt.value || String(expAmt.value).trim() === '')) {
            expAmt.placeholder = 'حداکثر ' + String(ex.remaining);
        }
        validateExpSubmit();
    }

    if (expSearchBtn) expSearchBtn.addEventListener('click', runExpSearch);
    if (expSearch) {
        expSearch.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') { e.preventDefault(); runExpSearch(); }
        });
        expSearch.addEventListener('input', function () {
            clearTimeout(debounceExp);
            debounceExp = setTimeout(runExpSearch, 350);
        });
    }
    if (expChange) expChange.addEventListener('click', function () { clearExpSelection(); if (expSearch) expSearch.focus(); });
    if (expAmt) expAmt.addEventListener('input', validateExpSubmit);

    expForm.addEventListener('submit', function (e) {
        if (!expIdInput || !expIdInput.value) {
            e.preventDefault();
            if (expHint) expHint.textContent = 'هزینه را انتخاب کنید.';
        }
    });

    if (expIdInput && expIdInput.value && expAmt && expAmt.value) {
        expSubmit.disabled = false;
        if (expHint) expHint.textContent = '';
    }
})();
</script>
@endpush
