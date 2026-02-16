@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title')
تقویم و وظایف — {{ config('app.name') }}
@endsection

@push('styles')
<style>
/* RTL: شنبه first (right side). Equal columns. Mobile: horizontal scroll. */
.calendar-scroll { overflow-x: auto; overflow-y: hidden; -webkit-overflow-scrolling: touch; margin: 0 -1rem; padding: 0 1rem; direction: rtl; }
.calendar-grid { display: grid; grid-template-columns: repeat(7, minmax(0, 1fr)); border: 2px solid #d6d3d1; border-radius: 1rem; overflow: hidden; font-family: 'Vazirmatn', sans-serif; direction: rtl; }
@media (max-width: 767px) {
  .calendar-grid { min-width: 32rem; }
}
.calendar-day-header { padding: 0.5rem; text-align: center; font-size: 0.75rem; font-weight: 600; color: #78716c; background: #f5f5f4; border-inline-start: 1px solid #e7e5e4; min-width: 0; }
.calendar-day-header:first-child { border-inline-start: none; }
.calendar-cell { min-height: 6rem; padding: 0.5rem; background: #fff; border-inline-start: 1px solid #e7e5e4; border-top: 1px solid #e7e5e4; min-width: 0; position: relative; cursor: pointer; }
.calendar-cell:nth-child(7n+1) { border-inline-start: none; }
.calendar-cell .cal-cell-inner { pointer-events: auto; }
.calendar-cell a { pointer-events: auto; }
.calendar-cell.other-month { background: #fafaf9; }
.calendar-cell.today { background: #ecfdf5; border: 2px solid #059669; margin: -1px; }
.calendar-day-num { font-size: 0.875rem; font-weight: 600; color: #292524; margin-bottom: 0.375rem; }
.calendar-event { font-size: 0.7rem; padding: 0.35rem 0.5rem; min-height: 1.75rem; margin-bottom: 0.25rem; border-radius: 0.375rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; display: flex; align-items: center; }
.calendar-event a { color: inherit; text-decoration: none; flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.calendar-event a:hover { text-decoration: underline; }
.calendar-cell .cal-events-collapsed { display: none; }
.calendar-cell.expanded .cal-events-collapsed { display: block; }
.calendar-cell .cal-toggle { font-size: 0.65rem; padding: 0.4rem 0.6rem; min-height: 2.25rem; margin-top: 0.25rem; border-radius: 0.375rem; background: #e7e5e4; color: #57534e; cursor: pointer; display: inline-flex; align-items: center; text-decoration: none; border: none; touch-action: manipulation; }
.calendar-cell .cal-toggle:hover { background: #d6d3d1; color: #292524; }
.cal-cell-popover { display: none; position: fixed; z-index: 100; min-width: 16rem; max-width: 22rem; max-height: 70vh; overflow-y: auto; background: #fff; border: 2px solid #059669; border-radius: 0.5rem; box-shadow: 0 8px 24px rgba(0,0,0,0.15); padding: 0.75rem 1rem; white-space: normal; direction: rtl; font-family: 'Vazirmatn', sans-serif; }
.calendar-cell.cal-cell-expanded .cal-cell-popover { display: block; }
.cal-cell-popover-title { font-weight: 600; font-size: 0.9375rem; color: #292524; margin-bottom: 0.5rem; }
.cal-cell-popover .calendar-event { white-space: normal; }
.week-row { display: flex; flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.75rem; margin-bottom: 0.5rem; background: #fff; }
.week-row-date { font-weight: 600; color: #292524; min-width: 5rem; }
.week-row-events { flex: 1; display: flex; flex-wrap: wrap; gap: 0.5rem; }
</style>
@endpush

@section('content')
<div style="max-width: 56rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #e0f2fe; color: #0369a1; border: 2px solid #bae6fd;">
                @include('components._icons', ['name' => 'calendar', 'class' => 'w-5 h-5'])
            </span>
            تقویم و وظایف
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c;">سرنخ‌ها، فاکتورها، یادآوری‌ها — تصویر کلی از تاریخ‌های مهم.</p>
    </div>

    @if (session('success'))
        <div style="margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.75rem; background: #ecfdf5; border: 2px solid #a7f3d0; color: #065f46; font-size: 0.875rem;">
            {{ session('success') }}
        </div>
    @endif

    {{-- Month navigation + Add reminder --}}
    <div style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem; margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; text-decoration: none; font-weight: 700;">‌‹</a>
            <span style="font-size: 1.125rem; font-weight: 600; color: #292524; min-width: 10rem; text-align: center;">{{ $monthLabelFa }}</span>
            <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" style="display: inline-flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; text-decoration: none; font-weight: 700;">›</a>
        </div>
        <a href="#add-reminder" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; text-decoration: none;">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            <span>یادآوری جدید</span>
        </a>
    </div>

    {{-- Month grid: horizontal scroll on mobile so همه روزها (including پنج‌شنبه و جمعه) are reachable --}}
    <div class="calendar-scroll">
    <div class="calendar-grid">
        @foreach (['شنبه', 'یک‌شنبه', 'دوشنبه', 'سه‌شنبه', 'چهارشنبه', 'پنج‌شنبه', 'جمعه'] as $wd)
            <div class="calendar-day-header">{{ $wd }}</div>
        @endforeach
        @php $today = $todayGregorian ?? now()->format('Y-m-d'); @endphp
        @php $accordionLimit = 2; @endphp
        @foreach ($grid as $week)
            @foreach ($week as $cell)
                @php
                    $evs = $cell['events'] ?? [];
                    $visible = array_slice($evs, 0, $accordionLimit);
                    $hidden = array_slice($evs, $accordionLimit);
                    $hasAccordion = count($hidden) > 0;
                @endphp
                <div class="calendar-cell {{ $cell['date'] === $today ? 'today' : '' }}" data-date="{{ $cell['date'] ?? '' }}" tabindex="0" role="button" aria-label="{{ $cell['day'] !== null ? FormatHelper::shamsi($cell['date'] ?? '') : '' }}" onclick="calCellClick(event, this)">
                    @if ($cell['day'] !== null)
                        <div class="cal-cell-inner">
                        <div class="calendar-day-num">{{ FormatHelper::englishToPersian((string) $cell['day']) }}</div>
                        @foreach ($visible as $ev)
                            <div class="calendar-event" style="background: {{ $ev['color'] }}20; color: {{ $ev['color'] }}; border-inline-end: 3px solid {{ $ev['color'] }};">
                                @if ($ev['url'])
                                    <a href="{{ $ev['url'] }}" title="{{ $ev['body'] ?? '' }}">{{ $ev['title'] }}</a>
                                @else
                                    <span title="{{ $ev['body'] ?? '' }}">{{ $ev['title'] }}</span>
                                @endif
                            </div>
                        @endforeach
                        @if ($hasAccordion)
                            <div class="cal-events-collapsed">
                                @foreach ($hidden as $ev)
                                    <div class="calendar-event" style="background: {{ $ev['color'] }}20; color: {{ $ev['color'] }}; border-inline-end: 3px solid {{ $ev['color'] }};">
                                        @if ($ev['url'])
                                            <a href="{{ $ev['url'] }}" title="{{ $ev['body'] ?? '' }}">{{ $ev['title'] }}</a>
                                        @else
                                            <span title="{{ $ev['body'] ?? '' }}">{{ $ev['title'] }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="cal-toggle" data-more="{{ count($hidden) }} رویداد دیگر ›" data-less="‌« کمتر" onclick="event.stopPropagation(); var c=this.closest('.calendar-cell'); var e=c.classList.toggle('expanded'); this.textContent=e?this.dataset.less:this.dataset.more; this.setAttribute('aria-expanded',e?'true':'false');" aria-expanded="false">{{ count($hidden) }} رویداد دیگر ›</button>
                        @endif
                        </div>
                        <div class="cal-cell-popover" aria-hidden="true">
                            <div class="cal-cell-popover-title">{{ FormatHelper::shamsi($cell['date'] ?? '') }}</div>
                            @foreach ($evs as $ev)
                                <div class="calendar-event" style="background: {{ $ev['color'] }}20; color: {{ $ev['color'] }}; border-inline-end: 3px solid {{ $ev['color'] }}; margin-bottom: 0.375rem;">
                                    @if ($ev['url'])
                                        <a href="{{ $ev['url'] }}">{{ $ev['title'] }}</a>
                                    @else
                                        <span>{{ $ev['title'] }}</span>
                                    @endif
                                    @if (!empty($ev['body']))
                                        <div style="font-size: 0.75rem; color: #57534e; margin-top: 0.125rem;">{{ $ev['body'] }}</div>
                                    @endif
                                </div>
                            @endforeach
                            @if (empty($evs))
                                <div style="font-size: 0.8125rem; color: #78716c;">رویدادی ثبت نشده</div>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        @endforeach
    </div>
    </div>

    {{-- Add reminder form --}}
    <div id="add-reminder" style="margin-top: 2rem; padding: 1.5rem; border: 2px solid #d6d3d1; border-radius: 1rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <h2 style="border-bottom: 2px solid #d6d3d1; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #292524;">یادآوری جدید</h2>
        <form action="{{ route('calendar.reminders.store') }}" method="post" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
            @csrf
            <div style="min-width: 12rem;">
                <label for="reminder_title" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.25rem;">عنوان *</label>
                <input type="text" name="title" id="reminder_title" required placeholder="مثلاً تماس با مشتری"
                       style="width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: 'Vazirmatn', sans-serif;">
            </div>
            <div style="min-width: 8rem;">
                <label for="reminder_date" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.25rem;">تاریخ *</label>
                <input type="text" name="due_date" id="reminder_date" required placeholder="۱۴۰۳/۱۱/۱۵"
                       value="{{ FormatHelper::shamsi(now()) }}"
                       style="width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: 'Vazirmatn', sans-serif;">
            </div>
            <div style="min-width: 6rem;">
                <label for="reminder_time" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.25rem;">ساعت</label>
                <input type="text" name="due_time" id="reminder_time" placeholder="۱۴:۳۰"
                       style="width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: 'Vazirmatn', sans-serif;">
            </div>
            <div style="min-width: 10rem;">
                <label for="reminder_body" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.25rem;">یادداشت</label>
                <input type="text" name="body" id="reminder_body" placeholder="اختیاری"
                       style="width: 100%; box-sizing: border-box; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; color: #292524; background: #fff; font-family: 'Vazirmatn', sans-serif;">
            </div>
            @if (isset($tags) && $tags->isNotEmpty())
            <div style="width: 100%;">
                @include('components._tag-section', ['tags' => $tags, 'embedded' => true, 'accentColor' => '#059669'])
            </div>
            @endif
            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; min-height: 42px; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; font-family: 'Vazirmatn', sans-serif;">
                @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
                <span>ذخیره</span>
            </button>
        </form>
    </div>

    {{-- Week view: list of events this month (ordered by Shamsi day 1..31) --}}
    <div style="margin-top: 2rem;">
        <h2 style="border-bottom: 2px solid #d6d3d1; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #292524;">همه رویدادهای این ماه</h2>
        @if (empty($eventsSorted ?? $events))
            <p style="color: #78716c; font-size: 0.875rem;">رویدادی در این ماه ثبت نشده است.</p>
        @else
            @php
                $listEvents = $eventsSorted ?? $events;
                $byDate = collect($listEvents)->groupBy('date');
                $sortedDates = $byDate->keys()->sortBy(fn ($date) => \App\Helpers\FormatHelper::gregorianToShamsiSortKey($date))->values();
            @endphp
            @foreach ($sortedDates as $date)
                @php $evs = $byDate[$date]; @endphp
                <div class="week-row">
                    <span class="week-row-date">{{ FormatHelper::shamsi($date) }}</span>
                    <div class="week-row-events">
                        @foreach ($evs as $ev)
                            <span style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.8125rem; background: {{ $ev['color'] }}20; color: {{ $ev['color'] }}; border: 1px solid {{ $ev['color'] }}40;">
                                @if ($ev['url'])
                                    <a href="{{ $ev['url'] }}" style="color: inherit; text-decoration: none;">{{ $ev['title'] }}</a>
                                @else
                                    {{ $ev['title'] }}
                                @endif
                                @if (isset($ev['tags']) && $ev['tags']->isNotEmpty())
                                    @foreach ($ev['tags'] as $t)
                                        <span style="font-size: 0.7rem; padding: 0.1rem 0.35rem; border-radius: 0.25rem; background: {{ $t->color }}30; color: {{ $t->color }};">{{ $t->name }}</span>
                                    @endforeach
                                @endif
                            </span>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

@push('scripts')
<script>
(function(){
  function calCellClick(e, cell) {
    if (e.target.closest('a')) return;
    if (e.target.closest('.cal-toggle')) return;
    var pop = cell.querySelector('.cal-cell-popover');
    if (!pop) return;
    document.querySelectorAll('.calendar-cell.cal-cell-expanded').forEach(function(x){ if (x !== cell) x.classList.remove('cal-cell-expanded'); });
    cell.classList.toggle('cal-cell-expanded');
    if (cell.classList.contains('cal-cell-expanded')) {
      var r = cell.getBoundingClientRect();
      pop.style.top = Math.max(8, r.bottom + 4) + 'px';
      pop.style.left = r.left + (r.width / 2) + 'px';
      pop.style.transform = 'translateX(-50%)';
    }
  }
  window.calCellClick = calCellClick;
  document.addEventListener('click', function(e) {
    if (!e.target.closest('.calendar-cell') && !e.target.closest('.cal-cell-popover')) {
      document.querySelectorAll('.calendar-cell.cal-cell-expanded').forEach(function(x){ x.classList.remove('cal-cell-expanded'); });
    }
  });
})();
</script>
@endpush
@endsection
