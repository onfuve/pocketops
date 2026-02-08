@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', 'سرنخ‌ها — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #fef3c7; color: #b45309; border-color: #fde68a; }
.leads-quick-form { display: grid; grid-template-columns: 1fr 140px 140px auto; gap: 0.75rem; align-items: end; width: 100%; max-width: 100%; box-sizing: border-box; }
@media (max-width: 768px) { .leads-quick-form { grid-template-columns: 1fr; } }
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

    {{-- Quick add --}}
    <div class="ds-form-card">
        <p class="ds-form-card-title" style="display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            افزودن سریع سرنخ
        </p>
        <form action="{{ route('leads.store') }}" method="post" class="leads-quick-form">
            @csrf
            <input type="hidden" name="status" value="{{ Lead::STATUS_NEW }}">
            <div style="position: relative; min-width: 0;">
                <label for="quick_name" class="ds-label">نام <span style="color: #b91c1c;">*</span></label>
                <input type="text" name="name" id="quick_name" required autocomplete="off" placeholder="نام طرف مقابل" class="ds-input">
                <div id="quick_name_results" style="position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; display: none; max-height: 12rem; overflow: auto; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
            </div>
            <div style="min-width: 0;">
                <label for="quick_phone" class="ds-label">تلفن</label>
                <input type="text" name="phone" id="quick_phone" placeholder="۰۹۱۲۳۴۵۶۷۸۹" dir="ltr" class="ds-input">
            </div>
            <div style="min-width: 0;">
                <label for="quick_company" class="ds-label">شرکت</label>
                <input type="text" name="company" id="quick_company" placeholder="اختیاری" class="ds-input">
            </div>
            <button type="submit" class="ds-btn ds-btn-primary">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                <span>افزودن</span>
            </button>
        </form>
        <p style="margin: 0.75rem 0 0 0; font-size: 0.75rem; color: #78716c;">برای ثبت جزئیات بیشتر بعد از افزودن، روی سرنخ کلیک کنید.</p>
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
                                    <span class="lead-name">{{ $lead->name }}</span>
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
(function () {
    var nameInput = document.getElementById('quick_name');
    var nameResults = document.getElementById('quick_name_results');
    var phoneInput = document.getElementById('quick_phone');
    if (!nameInput || !nameResults) return;
    var debounce = null;
    nameInput.addEventListener('input', function () {
        var q = nameInput.value.trim();
        if (q.length < 1) { nameResults.style.display = 'none'; nameResults.innerHTML = ''; return; }
        clearTimeout(debounce);
        debounce = setTimeout(function () {
            fetch('{{ route("contacts.search.api") }}?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.json(); })
                .then(function (list) {
                    nameResults.innerHTML = '';
                    if (list.length === 0) { nameResults.style.display = 'none'; return; }
                    list.forEach(function (c) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.style.cssText = 'display: block; padding: 0.75rem 1rem; font-size: 0.875rem; color: #292524; border-bottom: 1px solid #f5f5f4;';
                        a.textContent = c.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            nameInput.value = c.name;
                            nameResults.style.display = 'none';
                            nameResults.innerHTML = '';
                            if (phoneInput) {
                                fetch('{{ url("api/contacts") }}/' + c.id, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                                    .then(function (r) { return r.json(); })
                                    .then(function (data) { if (data.first_phone) phoneInput.value = data.first_phone; });
                            }
                        });
                        nameResults.appendChild(a);
                    });
                    nameResults.style.display = 'block';
                });
        }, 250);
    });
    nameInput.addEventListener('blur', function () { setTimeout(function () { nameResults.style.display = 'none'; }, 200); });
})();
</script>
@endpush
@endsection
