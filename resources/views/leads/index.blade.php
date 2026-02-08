@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', 'سرنخ‌ها — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
/* Quick add box — enhanced visual */
.leads-quick-box { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fef9c3 100%); border: 2px solid #fde68a; border-radius: var(--ds-radius-lg); padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(251, 191, 36, 0.15); }
.leads-quick-box .quick-title { display: flex; align-items: center; gap: 0.625rem; margin: 0 0 1.25rem 0; font-size: 1rem; font-weight: 600; color: #92400e; }
.leads-quick-box .quick-title svg { flex-shrink: 0; }
.leads-quick-form { display: grid; grid-template-columns: 1fr minmax(120px, 140px) minmax(100px, 130px); gap: 1rem; align-items: end; width: 100%; max-width: 100%; box-sizing: border-box; }
.leads-quick-form .quick-row-1 { grid-column: 1 / -1; display: grid; grid-template-columns: 1fr minmax(120px, 140px) minmax(100px, 130px) auto; gap: 1rem; align-items: end; }
.leads-quick-form .quick-details { grid-column: 1 / -1; }
.leads-quick-form .quick-field { min-width: 0; }
.leads-quick-form .quick-field .ds-label { font-size: 0.8125rem; color: #78716c; margin-bottom: 0.375rem; }
.leads-quick-form .quick-field .ds-input { background: #fff; border-color: #fde68a; }
.leads-quick-form .quick-field .ds-input:focus { border-color: #f59e0b; box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.2); }
.leads-quick-form .quick-actions { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
.leads-quick-form .quick-btn-add { min-width: 44px; }
.leads-quick-form .quick-btn-add-another { background: var(--ds-bg); color: var(--ds-text-muted); border-color: var(--ds-border); font-weight: 500; }
.leads-quick-form .quick-btn-add-another:hover { background: var(--ds-bg-muted); color: var(--ds-primary); border-color: var(--ds-primary-border); }
.leads-quick-form .quick-hint { margin: 1rem 0 0 0; font-size: 0.75rem; color: #a16207; display: flex; align-items: center; gap: 0.375rem; }
@media (max-width: 768px) { .quick-hint-kbd { display: none; } }
.leads-quick-form .quick-dropdown { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 30; max-height: 14rem; overflow-y: auto; border-radius: var(--ds-radius); border: 2px solid #fde68a; background: #fff; box-shadow: 0 4px 16px rgba(0,0,0,0.12); }
.leads-quick-form .quick-dropdown a { display: block; padding: 0.625rem 1rem; font-size: 0.875rem; color: var(--ds-text); border-bottom: 1px solid var(--ds-bg-subtle); transition: background 0.15s; }
.leads-quick-form .quick-dropdown a:last-child { border-bottom: none; }
.leads-quick-form .quick-dropdown a:hover { background: var(--ds-primary-bg); color: var(--ds-primary-dark); }
.leads-quick-form .quick-dropdown.hidden { display: none !important; }
@media (max-width: 768px) {
  .leads-quick-form .quick-row-1 { grid-template-columns: 1fr; }
  .leads-quick-form .quick-row-1 .quick-actions { grid-column: 1; }
  .leads-quick-form .quick-row-1 .quick-actions { flex-direction: column; width: 100%; }
  .leads-quick-form .quick-row-1 .quick-actions .ds-btn { flex: 1; width: 100%; justify-content: center; }
}
.ds-card .lead-name { font-weight: 600; font-size: 1rem; color: var(--ds-text); }
.ds-card .lead-badge { display: inline-flex; align-items: center; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500; margin-right: 0.5rem; }
.ds-card .lead-meta { font-size: 0.875rem; color: var(--ds-text-subtle); margin-top: 0.25rem; }
.ds-card .lead-footer { font-size: 0.75rem; color: var(--ds-text-faint); margin-top: 0.25rem; }
.ds-card .lead-arrow { flex-shrink: 0; color: #d6d3d1; transform: rotate(180deg); }
.ds-card .lead-arrow svg { transition: color 0.2s; }
.ds-card:hover .lead-arrow { color: var(--ds-primary); }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                </span>
                سرنخ‌ها
            </h1>
            <p class="ds-page-subtitle">سرنخ جدید را سریع اضافه کنید یا از فیلتر مرحله استفاده کنید.</p>
        </div>
        <a href="{{ route('leads.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-5 h-5'])
            سرنخ جدید
        </a>
    </div>

    {{-- Quick add — enhanced box --}}
    <div class="leads-quick-box">
        <p class="quick-title">
            <span style="display: flex; align-items: center; justify-content: center; width: 2rem; height: 2rem; border-radius: 0.5rem; background: #fde68a; color: #b45309;">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-5 h-5'])
            </span>
            افزودن سریع سرنخ
        </p>
        <form action="{{ route('leads.store') }}" method="post" class="leads-quick-form" id="quick_add_form" onsubmit="return quickAddValidate(this)">
            @csrf
            <input type="hidden" name="status" value="{{ Lead::STATUS_NEW }}">
            <input type="hidden" name="from_quick_add" value="1">
            <div class="quick-row-1">
                <div class="quick-field" style="position: relative;">
                    <label for="quick_name" class="ds-label">نام</label>
                    <input type="text" name="name" id="quick_name" autocomplete="off" placeholder="نام یا از لیست انتخاب کنید" class="ds-input">
                    <div id="quick_name_results" class="quick-dropdown hidden"></div>
                </div>
                <div class="quick-field">
                    <label for="quick_phone" class="ds-label">تلفن</label>
                    <input type="text" name="phone" id="quick_phone" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" class="ds-input">
                </div>
                <div class="quick-field">
                    <label for="quick_company" class="ds-label">شرکت</label>
                    <input type="text" name="company" id="quick_company" placeholder="اختیاری" class="ds-input">
                </div>
                <div class="quick-actions">
                    <button type="submit" name="add_another" value="0" class="ds-btn ds-btn-primary quick-btn-add">
                        @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                        <span>افزودن</span>
                    </button>
                    <button type="submit" name="add_another" value="1" class="ds-btn quick-btn-add-another">
                        @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                        <span>افزودن و بعدی</span>
                    </button>
                </div>
            </div>
            <div class="quick-details quick-field">
                <label for="quick_details" class="ds-label">نیاز / تقاضا</label>
                <input type="text" name="details" id="quick_details" placeholder="مثلاً: قطعه X، محصول Y، قیمت فلان…" class="ds-input">
            </div>
        </form>
        <p class="quick-hint">
            <svg style="width: 1rem; height: 1rem; flex-shrink: 0;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            برای ثبت جزئیات بیشتر روی سرنخ کلیک کنید
            <span class="quick-hint-kbd"> · Ctrl+Enter برای افزودن و سرنخ بعدی</span>
        </p>
    </div>

    {{-- Search + full form link --}}
    <div class="ds-search-row">
        <form action="{{ route('leads.index') }}" method="get" class="ds-search-form">
            <input type="hidden" name="status" value="{{ request('status') }}">
            <input type="search" name="q" value="{{ request('q') }}" placeholder="جستجو نام، شرکت، تلفن…" class="ds-input">
            <button type="submit" class="ds-btn ds-btn-secondary">
                @include('components._icons', ['name' => 'search', 'class' => 'w-4 h-4'])
                <span>جستجو</span>
            </button>
        </form>
        <a href="{{ route('leads.create') }}" class="ds-btn ds-btn-dashed">
            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
            <span>فرم کامل سرنخ جدید</span>
        </a>
    </div>

    {{-- Pipeline filter: status colors همۀ | جدید | تماس | جدی | پیشنهاد | بسته شد | رد شد --}}
    <div class="ds-filter-tabs">
        <a href="{{ route('leads.index', request()->only('q')) }}" class="{{ !request('status') ? 'ds-filter-active' : '' }}" style="{{ !request('status') ? 'background: #f5f5f4; color: #44403c; box-shadow: 0 1px 2px rgba(0,0,0,0.06);' : '' }}">همه</a>
        @foreach (Lead::pipelineStatuses() as $st)
            <a href="{{ route('leads.index', array_merge(request()->only('q'), ['status' => $st])) }}" class="{{ request('status') === $st ? 'ds-filter-active' : '' }}" style="{{ request('status') === $st ? 'background: ' . Lead::statusBgColor($st) . '; color: ' . Lead::statusTextColor($st) . '; box-shadow: 0 1px 2px rgba(0,0,0,0.06);' : '' }}">{{ Lead::statusLabels()[$st] }}</a>
        @endforeach
    </div>

    @if ($leads->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; font-size: 1rem; font-weight: 500; color: #57534e;">در این مرحله سرنخی نیست.</p>
            <p style="margin: 0; font-size: 0.875rem; color: #78716c;">از باکس «افزودن سریع» بالا یک سرنخ اضافه کنید یا <a href="{{ route('leads.create') }}" style="font-weight: 600; color: #059669; text-decoration: none;">فرم کامل</a> را پر کنید.</p>
        </div>
    @else
        <ul style="list-style: none; padding: 0; margin: 0;">
            @foreach ($leads as $lead)
                <li>
                    <a href="{{ route('leads.show', $lead) }}" class="ds-card">
                        <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                            <div style="min-width: 0; flex: 1;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                    <span class="lead-name">{{ $lead->name ?? 'بدون نام' }}</span>
                                    <span class="lead-badge" style="background: {{ Lead::statusBgColor($lead->status) }}; color: {{ Lead::statusTextColor($lead->status) }}; border: 1px solid {{ Lead::statusTextColor($lead->status) }}40;">{{ $lead->status_label }}</span>
                                    @if ($lead->contact_id)
                                        <span style="font-size: 0.75rem; color: #059669; font-weight: 500;">→ مخاطب</span>
                                    @endif
                                </div>
                                @if ($lead->company)
                                    <p class="lead-meta">{{ $lead->company }}</p>
                                @endif
                                @if ($lead->leadChannel || $lead->referrerContact)
                                    <p class="lead-footer" style="color: #78716c;">
                                        @if ($lead->leadChannel)<span style="font-weight: 500;">{{ $lead->leadChannel->name }}</span>@endif
                                        @if ($lead->leadChannel && $lead->referrerContact) · @endif
                                        @if ($lead->referrerContact)<a href="{{ route('contacts.show', $lead->referrerContact) }}" style="color: #57534e; text-decoration: none;">معرف: {{ $lead->referrerContact->name }}</a>@endif
                                    </p>
                                @endif
                                @if ($lead->phone || $lead->lead_date)
                                    <p class="lead-footer">
                                        @if ($lead->phone)<span dir="ltr">{{ $lead->phone }}</span>@endif
                                        @if ($lead->phone && $lead->lead_date) · @endif
                                        @if ($lead->lead_date){{ FormatHelper::shamsi($lead->lead_date) }}@endif
                                    </p>
                                @endif
                            </div>
                            <span class="lead-arrow">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-5 h-5'])</span>
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>

        <div style="margin-top: 1.5rem;">
            {{ $leads->links() }}
        </div>
    @endif
</div>

@push('scripts')
<script>
function quickAddValidate(form) {
    var name = (form.querySelector('#quick_name')?.value || '').trim();
    var phone = (form.querySelector('#quick_phone')?.value || '').trim();
    var company = (form.querySelector('#quick_company')?.value || '').trim();
    var details = (form.querySelector('#quick_details')?.value || '').trim();
    if (!name && !phone && !company && !details) {
        alert('حداقل یک فیلد را پر کنید (نام، تلفن، شرکت یا نیاز/تقاضا)');
        return false;
    }
    return true;
}
(function () {
    var form = document.getElementById('quick_add_form');
    var nameInput = document.getElementById('quick_name');
    var nameResults = document.getElementById('quick_name_results');
    var phoneInput = document.getElementById('quick_phone');
    if (!nameInput || !nameResults) return;
    var debounce = null;
    function hideDropdown() { nameResults.classList.add('hidden'); nameResults.innerHTML = ''; }
    function showDropdown() { nameResults.classList.remove('hidden'); }
    nameInput.addEventListener('input', function () {
        var q = nameInput.value.trim();
        if (q.length < 1) { hideDropdown(); return; }
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            fetch('{{ route("contacts.search.api") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    nameResults.innerHTML = '';
                    if (list.length === 0) { hideDropdown(); return; }
                    list.forEach(function (c) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = c.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            nameInput.value = c.name;
                            hideDropdown();
                            if (phoneInput) {
                                fetch('{{ url("api/contacts") }}/' + c.id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(function (r) { return r.json(); })
                                    .then(function (data) { if (data.first_phone) phoneInput.value = data.first_phone; });
                            }
                        });
                        nameResults.appendChild(a);
                    });
                    showDropdown();
                });
        }, 250);
    });
    nameInput.addEventListener('blur', function () { setTimeout(hideDropdown, 200); });
    if (form) {
        form.addEventListener('keydown', function (e) {
            if (e.ctrlKey && e.key === 'Enter') {
                e.preventDefault();
                var addAnother = document.querySelector('.quick-btn-add-another');
                if (addAnother) addAnother.click();
            }
        });
    }
    if (nameInput) nameInput.focus();
})();
</script>
@endpush
@endsection
