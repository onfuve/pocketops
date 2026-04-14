@php
    use App\Helpers\FormatHelper;
    $exportQuery = [
        'from' => request('from', $fromLabel),
        'to' => request('to', $toLabel),
    ];
    if (($expenseCategoryId ?? 0) > 0) {
        $exportQuery['expense_category_id'] = $expenseCategoryId;
    }
    if (! empty($selectedTagIds ?? [])) {
        $exportQuery['tag_ids'] = $selectedTagIds;
    }
@endphp
@extends('layouts.app')

@section('title', 'هزینه‌های عملیاتی — ' . config('app.name'))

@section('content')
<div class="ds-page expenses-page">
    <div class="ds-page-header">
        <div class="min-w-0">
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #fff7ed; color: #c2410c; border-color: #fed7aa;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-5 h-5'])
                </span>
                هزینه‌های عملیاتی
            </h1>
            <p class="ds-page-subtitle">ثبت و فیلتر هزینه‌های غیرمخاطب؛ دسته از تنظیمات، چند برچسب هم‌زمان، خروجی CSV با همان فیلترها.</p>
        </div>
        @if(auth()->user()->canModule('expenses', \App\Models\User::ABILITY_CREATE))
        <a href="{{ route('expenses.create') }}" class="ds-btn ds-btn-primary shrink-0">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            هزینه جدید
        </a>
        @endif
    </div>

    @if (session('success'))
        <div class="ds-alert-success mb-4 rounded-lg px-3 py-2 text-sm">{{ session('success') }}</div>
    @endif

    <div class="expenses-shell rounded-xl border overflow-hidden mb-4" style="border-color: #e7e5e4; background: #fff;">
        <div class="expenses-stats flex flex-wrap items-center gap-x-4 gap-y-2 px-4 py-3 text-sm" style="background: #fffbeb; border-bottom: 1px solid #fde68a; color: #92400e;">
            <span>جمع مبلغ اصلی: <strong class="font-vazir">{{ FormatHelper::rial($sumAmount) }}</strong></span>
            @if(($sumFees ?? 0) > 0)
                <span class="hidden sm:inline w-px h-4 self-center opacity-40 bg-amber-800/30" aria-hidden="true"></span>
                <span>کارمزد: <strong class="font-vazir">{{ FormatHelper::rial($sumFees) }}</strong></span>
            @endif
            <span class="hidden sm:inline w-px h-4 self-center opacity-40 bg-amber-800/30" aria-hidden="true"></span>
            <span>خروج از حساب: <strong class="font-vazir">{{ FormatHelper::rial($sumOutlay ?? $sumAmount) }}</strong></span>
        </div>

        <form action="{{ route('expenses.index') }}" method="get" class="p-4 space-y-4">
            @include('reports.business._period-form', ['fromLabel' => $fromLabel, 'toLabel' => $toLabel])

            <div class="grid gap-4 lg:grid-cols-12 lg:items-start">
                <div class="lg:col-span-4 space-y-3">
                    <div>
                        <label class="ds-label" for="expense_category_id">دسته</label>
                        <select name="expense_category_id" id="expense_category_id" class="ds-select w-full text-sm" style="min-height: 2.75rem;">
                            <option value="0">همه</option>
                            @foreach ($expenseCategories as $c)
                                <option value="{{ $c->id }}" {{ (int) ($expenseCategoryId ?? 0) === (int) $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-xs m-0" style="color: var(--ds-text-muted);">
                            <a href="{{ route('settings.expense-categories') }}" class="font-semibold" style="color: #059669;">ویرایش دسته‌ها</a>
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        <button type="submit" class="ds-btn ds-btn-secondary text-sm">اعمال فیلتر</button>
                        <a href="{{ route('expenses.index', ['from' => request('from', $fromLabel), 'to' => request('to', $toLabel)]) }}" class="ds-btn ds-btn-outline text-sm">پاک کردن فیلتر دسته و برچسب</a>
                    </div>
                </div>
                <div class="lg:col-span-8">
                    @include('expenses.partials.tag-filter', ['tags' => $tags, 'selectedTagIds' => $selectedTagIds ?? []])
                </div>
            </div>

            <div class="flex flex-wrap gap-2 pt-1 border-t" style="border-color: var(--ds-border);">
                <a href="{{ route('expenses.index', array_merge($exportQuery, ['export' => 'csv'])) }}" class="ds-btn ds-btn-outline text-sm">
                    @include('components._icons', ['name' => 'download', 'class' => 'w-4 h-4'])
                    خروجی CSV
                </a>
            </div>
        </form>
    </div>

    @include('reports.business._period-script')

    @if ($expenses->isEmpty())
        <div class="ds-empty rounded-xl border p-8 text-center" style="border-color: var(--ds-border);">
            <p class="m-0 text-stone-600">رکوردی با این فیلتر نیست.</p>
            @if(auth()->user()->canModule('expenses', \App\Models\User::ABILITY_CREATE))
                <a href="{{ route('expenses.create') }}" class="ds-btn ds-btn-primary mt-4 inline-flex">هزینه جدید</a>
            @endif
        </div>
    @else
        <div class="overflow-x-auto rounded-xl border" style="border-color: var(--ds-border);">
            <table class="expenses-table min-w-full text-right text-sm">
                <thead>
                    <tr>
                        <th class="px-3 py-2.5">تاریخ</th>
                        <th class="px-3 py-2.5">مبلغ</th>
                        <th class="px-3 py-2.5">کارمزد</th>
                        <th class="px-3 py-2.5">جمع</th>
                        <th class="px-3 py-2.5">دسته</th>
                        <th class="px-3 py-2.5">کارت/حساب</th>
                        <th class="px-3 py-2.5 min-w-[8rem]">برچسب</th>
                        <th class="px-3 py-2.5 w-28"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($expenses as $e)
                        <tr>
                            <td class="px-3 py-2.5 font-vazir whitespace-nowrap">{{ FormatHelper::shamsi($e->paid_at) }}</td>
                            <td class="px-3 py-2.5 font-vazir font-medium whitespace-nowrap">{{ FormatHelper::rial($e->amount) }}</td>
                            <td class="px-3 py-2.5 font-vazir text-xs" style="color: var(--ds-text-muted);">{{ ($e->fee_amount ?? 0) > 0 ? FormatHelper::rial($e->fee_amount) : '—' }}</td>
                            <td class="px-3 py-2.5 font-vazir font-medium whitespace-nowrap">{{ FormatHelper::rial($e->totalOutlayRial()) }}</td>
                            <td class="px-3 py-2.5 text-stone-800">{{ $e->expenseCategory?->name ?? '—' }}</td>
                            <td class="px-3 py-2.5 text-xs" style="color: var(--ds-text-muted);">{{ $e->paymentOption?->label ?: '—' }}</td>
                            <td class="px-3 py-2.5">
                                <div class="flex flex-wrap gap-1">
                                    @forelse ($e->tags as $tg)
                                        <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium" style="background: {{ $tg->color }}22; color: {{ $tg->color }};">{{ $tg->name }}</span>
                                    @empty
                                        <span class="text-stone-400 text-xs">—</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="px-3 py-2.5 whitespace-nowrap">
                                @if(auth()->user()->canModule('expenses', \App\Models\User::ABILITY_EDIT))
                                <a href="{{ route('expenses.edit', $e) }}" class="text-sm font-medium" style="color: var(--ds-primary);">ویرایش</a>
                                @endif
                                @if(auth()->user()->canModule('expenses', \App\Models\User::ABILITY_DELETE))
                                <form action="{{ route('expenses.destroy', $e) }}" method="post" class="inline mr-2" onsubmit='return confirm(@json("حذف این هزینه؟"))'>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm p-0 bg-transparent border-0 cursor-pointer" style="color: #b91c1c;">حذف</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $expenses->links() }}</div>
    @endif
</div>
@endsection

@push('styles')
<style>
.expenses-table { border-collapse: collapse; }
.expenses-table thead { background: var(--ds-bg-muted, #f5f5f4); }
.expenses-table th { font-size: 0.6875rem; font-weight: 600; color: #57534e; border-bottom: 1px solid var(--ds-border, #e7e5e4); }
.expenses-table tbody tr { border-top: 1px solid #f5f5f4; }
.expenses-table tbody tr:hover { background: #fffbeb; }
.exp-tag-filter-list {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
    max-height: 14rem;
    overflow-y: auto;
    padding: 0.35rem;
    border-radius: 0.5rem;
    background: #fff;
    border: 1px solid #e7e5e4;
}
.exp-tag-filter-row {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.35rem 0.5rem;
    border-radius: 0.375rem;
    cursor: pointer;
    margin: 0;
}
.exp-tag-filter-row:hover { background: #fff7ed; }
.exp-tag-filter-row.tag-filter-hidden { display: none; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var search = document.getElementById('exp-tag-filter-search');
    var list = document.getElementById('exp-tag-filter-list');
    var summary = document.getElementById('exp-tag-filter-summary');
    var clearBtn = document.getElementById('exp-tag-filter-clear');
    function updateSummary() {
        if (!list || !summary) return;
        var rows = list.querySelectorAll('.exp-tag-filter-row');
        var sel = 0, vis = 0;
        rows.forEach(function (row) {
            if (!row.classList.contains('tag-filter-hidden')) vis++;
            var cb = row.querySelector('input[type="checkbox"]');
            if (cb && cb.checked) sel++;
        });
        summary.textContent = sel ? (sel + ' برچسب برای فیلتر') : 'همه رکوردها (بدون فیلتر برچسب)';
    }
    function filterRows() {
        if (!search || !list) return;
        var q = (search.value || '').trim().toLowerCase();
        list.querySelectorAll('.exp-tag-filter-row').forEach(function (row) {
            var name = row.getAttribute('data-tag-name') || '';
            row.classList.toggle('tag-filter-hidden', !!(q && name.indexOf(q) === -1));
        });
        updateSummary();
    }
    if (search && list) {
        search.addEventListener('input', filterRows);
        filterRows();
    } else if (summary) {
        summary.textContent = '';
    }
    if (clearBtn && list) {
        clearBtn.addEventListener('click', function () {
            list.querySelectorAll('input[type="checkbox"]').forEach(function (cb) { cb.checked = false; });
            updateSummary();
        });
    }
})();
</script>
@endpush
