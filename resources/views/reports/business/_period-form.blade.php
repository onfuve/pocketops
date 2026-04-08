@php use App\Helpers\FormatHelper; @endphp
<div class="flex flex-wrap gap-3 items-end" style="margin-bottom: 1rem;">
    <div>
        <label class="ds-label">از تاریخ (شمسی)</label>
        <input type="text" name="from" class="ds-input" style="max-width: 11rem;" dir="ltr"
               value="{{ request('from', $fromLabel) }}" placeholder="۱۴۰۴/۰۱/۱۵" autocomplete="off">
    </div>
    <div>
        <label class="ds-label">تا تاریخ (شمسی)</label>
        <input type="text" name="to" class="ds-input" style="max-width: 11rem;" dir="ltr"
               value="{{ request('to', $toLabel) }}" placeholder="۱۴۰۴/۰۱/۱۵" autocomplete="off">
    </div>
    <button type="button" class="ds-btn ds-btn-secondary report-period-today" data-today="{{ FormatHelper::shamsi(now()) }}">امروز</button>
</div>
