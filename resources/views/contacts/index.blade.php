@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', 'مخاطبین — ' . config('app.name'))

@push('styles')
<style>
.contacts-search-wrap { position: relative; flex: 1; min-width: 0; }
.contacts-search-wrap .search-icon { position: absolute; right: 0.75rem; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--ds-text-faint); }
.contacts-search-wrap .contacts-search-clear {
    position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%);
    width: 1.5rem; height: 1.5rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
    color: var(--ds-text-subtle); background: var(--ds-border); text-decoration: none; font-size: 1rem; line-height: 1;
    transition: background 0.2s, color 0.2s;
}
.contacts-search-wrap .contacts-search-clear:hover { background: var(--ds-border-hover); color: var(--ds-text); }
.contacts-search-wrap .ds-input { padding-left: {{ !empty($q) ? '2.5rem' : '0.75rem' }}; }
.ds-page .ds-page-title-icon { background: #d1fae5; color: #047857; border-color: #a7f3d0; }
.contacts-balance-filter { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
.contacts-balance-filter .ds-filter-tabs { margin-bottom: 0; }
.contact-card { background: var(--ds-bg); border: 2px solid var(--ds-border); border-radius: var(--ds-radius-lg); padding: 1rem; margin-bottom: 0.5rem; box-shadow: var(--ds-shadow); transition: all 0.2s; text-decoration: none; color: inherit; display: block; width: 100%; box-sizing: border-box; }
.contact-card:hover { border-color: var(--ds-border-hover); box-shadow: var(--ds-shadow-hover); }
.contacts-list { width: 100%; }
.contacts-list > li { width: 100%; box-sizing: border-box; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                مخاطبین
            </h1>
            <p class="ds-page-subtitle">جستجو، فیلتر و مدیریت مخاطبین — مانده حساب در هر کارت نمایش داده می‌شود.</p>
        </div>
        <a href="{{ route('contacts.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'user-plus', 'class' => 'w-4 h-4'])
            مخاطب جدید
        </a>
    </div>

    {{-- Search + actions --}}
    <div class="ds-search-row">
        <form action="{{ route('contacts.index') }}" method="get" class="ds-search-form">
            <input type="hidden" name="balance" value="{{ $balanceFilter ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sort ?? 'recent' }}">
            <input type="hidden" name="per_page" value="{{ $perPage ?? 20 }}">
            <div class="contacts-search-wrap">
                <input type="search" name="q" value="{{ $q ?? '' }}" placeholder="جستجو نام، تلفن، شهر، آدرس…" id="contacts-search-input" autocomplete="off" class="ds-input">
                <span class="search-icon">@include('components._icons', ['name' => 'search', 'class' => 'w-5 h-5'])</span>
                @if(!empty($q))
                    <a href="{{ route('contacts.index', array_filter(['balance' => $balanceFilter, 'sort' => $sort, 'per_page' => $perPage ?? 20])) }}" class="contacts-search-clear" title="پاک کردن جستجو" aria-label="پاک کردن جستجو">&times;</a>
                @endif
            </div>
            <button type="submit" class="ds-btn ds-btn-secondary">
                @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
                <span>جستجو</span>
            </button>
        </form>
        <a href="{{ route('contacts.export', request()->query()) }}" class="ds-btn ds-btn-outline">
            @include('components._icons', ['name' => 'download', 'class' => 'w-4 h-4'])
            خروجی CSV
        </a>
    </div>

    {{-- Active search / result summary --}}
    @if(!empty($q) || !empty($balanceFilter) || (isset($sort) && $sort !== 'recent'))
        <div style="margin-bottom: 1rem; display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; font-size: 0.875rem;">
            @if(!empty($q))
                <span style="color: var(--ds-text-muted);">جستجو: <strong style="color: var(--ds-text);">«{{ $q }}»</strong></span>
            @endif
            @if($contacts->total() > 0)
                <span style="color: var(--ds-text-subtle);">{{ FormatHelper::englishToPersian((string) $contacts->total()) }} نتیجه</span>
            @endif
            <a href="{{ route('contacts.index') }}" style="color: var(--ds-primary); font-weight: 500; text-decoration: none;">نمایش همه</a>
        </div>
    @endif

    {{-- Balance filter + Sort --}}
    <div class="contacts-balance-filter">
        <div class="ds-filter-tabs" style="margin-bottom: 0;">
            @php $baseQuery = array_filter(['q' => $q, 'sort' => $sort, 'per_page' => $perPage ?? 20]); @endphp
            <span style="font-size: 0.875rem; font-weight: 500; color: var(--ds-text-subtle); padding: 0 0.25rem;">مانده:</span>
            <a href="{{ route('contacts.index', $baseQuery) }}" class="{{ empty($balanceFilter) ? 'ds-filter-active' : '' }}" style="{{ empty($balanceFilter) ? 'background: var(--ds-primary); color: #fff;' : '' }}">همه</a>
            <a href="{{ route('contacts.index', array_merge($baseQuery, ['balance' => 'positive'])) }}" class="{{ ($balanceFilter ?? '') === 'positive' ? 'ds-filter-active' : '' }}" style="{{ ($balanceFilter ?? '') === 'positive' ? 'background: var(--ds-primary); color: #fff;' : '' }}">بستانکار</a>
            <a href="{{ route('contacts.index', array_merge($baseQuery, ['balance' => 'negative'])) }}" class="{{ ($balanceFilter ?? '') === 'negative' ? 'ds-filter-active' : '' }}" style="{{ ($balanceFilter ?? '') === 'negative' ? 'background: var(--ds-primary); color: #fff;' : '' }}">بدهکار</a>
            <a href="{{ route('contacts.index', array_merge($baseQuery, ['balance' => 'zero'])) }}" class="{{ ($balanceFilter ?? '') === 'zero' ? 'ds-filter-active' : '' }}" style="{{ ($balanceFilter ?? '') === 'zero' ? 'background: var(--ds-primary); color: #fff;' : '' }}">تسویه</a>
        </div>
        <span style="width: 1px; height: 1.25rem; background: var(--ds-border); margin: 0 0.25rem;" aria-hidden="true"></span>
        <label for="contacts-sort" class="ds-label" style="margin-bottom: 0;">مرتب‌سازی:</label>
        <form method="get" action="{{ route('contacts.index') }}" style="display: inline;">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <input type="hidden" name="balance" value="{{ $balanceFilter ?? '' }}">
            <input type="hidden" name="per_page" value="{{ $perPage ?? 20 }}">
            <select name="sort" id="contacts-sort" class="ds-select" style="width: auto; min-width: 8rem;" onchange="this.form.submit()">
                <option value="recent" {{ ($sort ?? 'recent') === 'recent' ? 'selected' : '' }}>جدیدترین</option>
                <option value="name" {{ ($sort ?? '') === 'name' ? 'selected' : '' }}>نام</option>
                <option value="balance" {{ ($sort ?? '') === 'balance' ? 'selected' : '' }}>مانده</option>
            </select>
        </form>
        <span style="width: 1px; height: 1.25rem; background: var(--ds-border); margin: 0 0.25rem;" aria-hidden="true"></span>
        <label for="contacts-per-page" class="ds-label" style="margin-bottom: 0;">در هر صفحه:</label>
        <form method="get" action="{{ route('contacts.index') }}" style="display: inline;" id="contacts-per-page-form">
            <input type="hidden" name="q" value="{{ $q ?? '' }}">
            <input type="hidden" name="balance" value="{{ $balanceFilter ?? '' }}">
            <input type="hidden" name="sort" value="{{ $sort ?? 'recent' }}">
            <select name="per_page" id="contacts-per-page" class="ds-select" style="width: auto; min-width: 5rem;" onchange="this.form.submit()">
                <option value="20" {{ ($perPage ?? 20) == 20 ? 'selected' : '' }}>۲۰</option>
                <option value="50" {{ ($perPage ?? 20) == 50 ? 'selected' : '' }}>۵۰</option>
                <option value="100" {{ ($perPage ?? 20) == 100 ? 'selected' : '' }}>۱۰۰</option>
                <option value="200" {{ ($perPage ?? 20) == 200 ? 'selected' : '' }}>۲۰۰</option>
                <option value="500" {{ ($perPage ?? 20) == 500 ? 'selected' : '' }}>۵۰۰</option>
            </select>
        </form>
    </div>

    @if ($contacts->isEmpty())
        <div class="ds-empty">
            @if(!empty($q) || isset($balanceFilter) && $balanceFilter !== '')
                <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-muted);">نتیجه‌ای یافت نشد.</p>
                <p style="margin: 0 0 1rem 0; font-size: 0.875rem; color: var(--ds-text-subtle);">جستجو یا فیلتر را تغییر دهید یا همه مخاطبین را ببینید.</p>
                <a href="{{ route('contacts.index') }}" class="ds-btn ds-btn-primary">نمایش همه مخاطبین</a>
            @else
                <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-muted);">مخاطبی ثبت نشده است.</p>
                <p style="margin: 0; font-size: 0.875rem; color: var(--ds-text-subtle);">
                    <a href="{{ route('contacts.create') }}" style="font-weight: 600; color: var(--ds-primary); text-decoration: none;">اولین مخاطب را اضافه کنید</a>
                    یا
                    <a href="{{ route('contacts.import') }}" style="font-weight: 600; color: #0284c7; text-decoration: none;">از فایل CSV وارد کنید</a>.
                </p>
            @endif
        </div>
    @else
        <div style="margin: 0 0 1rem 0; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
            <p style="margin: 0; font-size: 0.875rem; color: var(--ds-text-subtle);">
                نمایش {{ FormatHelper::englishToPersian((string) $contacts->firstItem()) }}–{{ FormatHelper::englishToPersian((string) $contacts->lastItem()) }} از {{ FormatHelper::englishToPersian((string) $contacts->total()) }} مخاطب
            </p>
            @if (auth()->user()->canDeleteContact())
            <form action="{{ route('contacts.bulk-delete') }}" method="post" id="contacts-bulk-delete-form" style="display: none;">
                @csrf
                <div id="contacts-bulk-delete-ids"></div>
                <button type="submit" class="ds-btn ds-btn-danger" onclick="return confirm('مخاطب‌های انتخاب‌شده حذف شوند؟');">
                    @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                    <span id="contacts-bulk-delete-count">۰</span> حذف
                </button>
            </form>
            @endif
        </div>

        <ul class="contacts-list" style="list-style: none; padding: 0; margin: 0;">
            <li style="margin-bottom: 0.5rem; padding: 0.5rem 1rem; background: var(--ds-bg-subtle); border-radius: var(--ds-radius); border: 2px solid var(--ds-border);">
                <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; font-size: 0.875rem; font-weight: 500; width: 100%;">
                    <input type="checkbox" id="contacts-select-all" style="width: 1.25rem; height: 1.25rem; accent-color: var(--ds-primary);">
                    <span>انتخاب همه این صفحه</span>
                </label>
            </li>
            @foreach ($contacts as $contact)
                @php
                    $balance = (float) ($contact->balance ?? 0);
                    $balanceBorder = $balance > 0 ? '#a7f3d0' : ($balance < 0 ? '#fde68a' : 'var(--ds-border)');
                    $balanceLabel = $balance > 0 ? 'بستانکار' : ($balance < 0 ? 'بدهکار' : 'تسویه');
                    $balanceColor = $balance > 0 ? '#047857' : ($balance < 0 ? '#b45309' : 'var(--ds-text-subtle)');
                    $balanceBg = $balance > 0 ? '#ecfdf5' : ($balance < 0 ? '#fffbeb' : 'var(--ds-bg-subtle)');
                @endphp
                <li>
                    <div class="contact-card" style="border-right-width: 4px; border-right-color: {{ $balanceBorder }}; display: flex; flex-wrap: wrap; align-items: flex-start; gap: 0.75rem;">
                        @if (auth()->user()->canDeleteContact())
                        <label style="flex-shrink: 0; cursor: pointer; padding: 0.25rem;">
                            <input type="checkbox" class="contact-select-checkbox" value="{{ $contact->id }}" style="width: 1.25rem; height: 1.25rem; accent-color: var(--ds-primary);">
                        </label>
                        @endif
                        <div style="min-width: 0; flex: 1;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                    <a href="{{ route('contacts.show', $contact) }}" style="font-weight: 600; color: var(--ds-text); text-decoration: none;">{{ $contact->name }}</a>
                                    @if ($contact->is_hamkar)
                                        <span class="ds-badge ds-badge-amber">همکار</span>
                                    @endif
                                    <span style="font-size: 0.75rem; font-weight: 600; padding: 0.25rem 0.625rem; border-radius: 9999px; background: {{ $balanceBg }}; color: {{ $balanceColor }};" title="{{ $balanceLabel }}">
                                        {{ FormatHelper::rial(abs($balance)) }} <span style="font-weight: 400; opacity: 0.9;">({{ $balanceLabel }})</span>
                                    </span>
                                </div>
                                @if ($contact->contactPhones->isNotEmpty())
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: var(--ds-text-subtle);" dir="ltr">{{ $contact->contactPhones->pluck('phone')->implode('، ') }}</p>
                                @endif
                                @if ($contact->city || $contact->referrer_name)
                                    <p style="margin: 0.25rem 0 0 0; font-size: 0.75rem; color: var(--ds-text-faint);">
                                        @if ($contact->city)شهر: {{ $contact->city }}@endif
                                        @if ($contact->city && $contact->referrer_name) · @endif
                                        @if ($contact->referrer_name)معرف: {{ $contact->referrer_name }}@endif
                                    </p>
                                @endif
                            </div>
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                <a href="{{ route('contacts.receive-pay', $contact) }}" class="ds-btn ds-btn-primary">
                                    <svg style="width: 1rem; height: 1rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                                    <span class="hidden sm:inline">دریافت/پرداخت</span>
                                </a>
                                <a href="{{ route('contacts.edit', $contact) }}" class="ds-btn ds-btn-outline">
                                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                                    <span class="hidden sm:inline">ویرایش</span>
                                </a>
                                @if (auth()->user()->canDeleteContact())
                                    <form action="{{ route('contacts.destroy', $contact) }}" method="post" style="display: inline;" onsubmit="return confirm('مخاطب حذف شود؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="ds-btn ds-btn-danger">
                                            @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                            <span class="hidden sm:inline">حذف</span>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 1.5rem;">
            {{ $contacts->withQueryString()->links() }}
        </div>
    @endif
</div>

@if (!$contacts->isEmpty() && auth()->user()->canDeleteContact())
@push('scripts')
<script>
(function () {
    var selectAll = document.getElementById('contacts-select-all');
    var checkboxes = document.querySelectorAll('.contact-select-checkbox');
    var form = document.getElementById('contacts-bulk-delete-form');
    var idsDiv = document.getElementById('contacts-bulk-delete-ids');
    var countSpan = document.getElementById('contacts-bulk-delete-count');
    function updateBulkForm() {
        var checked = document.querySelectorAll('.contact-select-checkbox:checked');
        if (checked.length === 0) {
            form.style.display = 'none';
            return;
        }
        form.style.display = 'inline-block';
        idsDiv.innerHTML = '';
        checked.forEach(function (cb) {
            var inp = document.createElement('input');
            inp.type = 'hidden';
            inp.name = 'ids[]';
            inp.value = cb.value;
            idsDiv.appendChild(inp);
        });
        countSpan.textContent = checked.length;
    }
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(function (cb) { cb.checked = selectAll.checked; });
            updateBulkForm();
        });
    }
    checkboxes.forEach(function (cb) {
        cb.addEventListener('change', updateBulkForm);
    });
})();
</script>
@endpush
@endif
@endsection
