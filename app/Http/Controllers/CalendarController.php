<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Reminder;
use App\Models\Tag;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /** Get all calendar events for a Shamsi month (Y/m). */
    public function index(Request $request)
    {
        $tehran = new \DateTimeZone('Asia/Tehran');
        $shamsiMonth = $request->get('month', Verta::now($tehran)->format('Y/m'));
        $view = $request->get('view', 'month'); // month | week

        // Parse Shamsi month to get start/end Gregorian dates (noon Tehran to avoid day boundary shift)
        $v = Verta::parse($shamsiMonth . '/01 12:00:00', $tehran);
        $startDate = $v->datetime()->setTimezone($tehran)->format('Y-m-d');
        $endDate = $v->endMonth()->datetime()->setTimezone($tehran)->format('Y-m-d');

        $user = $request->user();

        // Reminders (manual + lead tasks) - own or linked to visible leads (admin sees all)
        $reminderQuery = Reminder::with('tags', 'remindable')
            ->whereNull('done_at')
            ->whereBetween('due_date', [$startDate, $endDate]);
        if (!$user->isAdmin()) {
            $reminderQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHasMorph('remindable', [Lead::class], fn ($q2) => $q2->where(function ($q3) use ($user) {
                        $q3->where('user_id', $user->id)->orWhere('assigned_to_id', $user->id);
                    }));
            });
        }
        $reminders = $reminderQuery->orderBy('due_date')->orderBy('due_time')->get();

        $tags = Tag::forCurrentUser()->orderBy('name')->get();

        // Invoices with due_date in range (final, not fully paid)
        $invoices = Invoice::visibleToUser($user)
            ->where('status', '!=', Invoice::STATUS_DRAFT)
            ->whereNotNull('due_date')
            ->whereBetween('due_date', [$startDate, $endDate])
            ->orderBy('due_date')
            ->get()
            ->filter(fn ($inv) => !$inv->isPaid());

        // Leads that are in calendar (as reminders) - already in $reminders
        // Also show leads with lead_date in range? User said "every lead could be sent to it"
        // So we only show leads that were explicitly added as tasks (in reminders).
        // Invoices we show automatically by due_date.

        $events = $this->buildEvents($reminders, $invoices);

        $vStart = Verta::parse($startDate . ' 12:00:00', $tehran);
        $prevMonth = (clone $vStart)->subMonth()->format('Y/m');
        $nextMonth = (clone $vStart)->addMonth()->format('Y/m');
        $monthLabel = $v->format('F Y'); // Persian month name
        $monthLabelFa = $this->monthName($v->month) . ' ' . FormatHelper::englishToPersian((string) $v->year);

        // Today in Tehran for grid highlight
        $todayGregorian = (new \DateTimeImmutable('now', $tehran))->format('Y-m-d');

        // Build calendar grid for month view (consistent Shamsi day → Gregorian date)
        $grid = $this->buildMonthGrid($v, $events, $tehran);

        // Sort events by Shamsi date for "همه رویدادهای این ماه" (display order 1..31)
        $eventsSorted = collect($events)->sortBy(fn ($e) => FormatHelper::gregorianToShamsiSortKey($e['date']))->values()->all();

        return view('calendar.index', compact(
            'events', 'eventsSorted', 'grid', 'shamsiMonth', 'prevMonth', 'nextMonth', 'monthLabelFa', 'view', 'tags', 'todayGregorian'
        ));
    }

    /** Build unified events array for display. */
    private function buildEvents($reminders, $invoices): array
    {
        $events = [];
        foreach ($reminders as $r) {
            $events[] = [
                'type' => $r->type,
                'id' => 'r' . $r->id,
                'title' => $r->title,
                'body' => $r->body,
                'tags' => $r->tags,
                'date' => $r->due_date->format('Y-m-d'),
                'time' => is_string($r->due_time) ? $r->due_time : ($r->due_time ? substr((string) $r->due_time, 0, 5) : null),
                'url' => $r->remindable_type === Lead::class && $r->remindable_id
                    ? route('leads.show', $r->remindable_id)
                    : null,
                'color' => $r->type === Reminder::TYPE_LEAD_TASK ? '#f59e0b' : '#059669',
                'icon' => $r->type === Reminder::TYPE_LEAD_TASK ? 'lightbulb' : 'check',
            ];
        }
        foreach ($invoices as $inv) {
            $label = $inv->type === 'sell' ? 'سررسید فاکتور فروش' : 'سررسید رسید خرید';
            $events[] = [
                'type' => 'invoice_due',
                'id' => 'i' . $inv->id,
                'title' => $label . ' — ' . $inv->contact->name,
                'body' => FormatHelper::rial($inv->total),
                'tags' => collect(),
                'date' => $inv->due_date->format('Y-m-d'),
                'time' => null,
                'url' => route('invoices.show', $inv),
                'color' => $inv->type === 'sell' ? '#047857' : '#0369a1',
                'icon' => $inv->type === 'sell' ? 'sell' : 'buy',
            ];
        }
        return $events;
    }

    private function monthName(int $m): string
    {
        $names = [
            1 => 'فروردین', 2 => 'اردیبهشت', 3 => 'خرداد', 4 => 'تیر', 5 => 'مرداد', 6 => 'شهریور',
            7 => 'مهر', 8 => 'آبان', 9 => 'آذر', 10 => 'دی', 11 => 'بهمن', 12 => 'اسفند',
        ];
        return $names[$m] ?? (string) $m;
    }

    private function buildMonthGrid(Verta $v, array $events, \DateTimeZone $tehran): array
    {
        $daysInMonth = $v->daysInMonth;
        // Grid columns: 0=Saturday, 1=Sunday, ... 6=Friday (Iranian week). Compute from Gregorian first day to avoid Verta dayOfWeek quirks.
        $firstGregorian = $v->datetime()->setTimezone($tehran);
        $phpW = (int) $firstGregorian->format('w'); // PHP: 0=Sun, 1=Mon, ..., 6=Sat
        $firstDayOfWeek = ($phpW + 1) % 7; // 0=Sat, 1=Sun, ..., 6=Fri

        $grid = [];
        $week = [];
        for ($i = 0; $i < $firstDayOfWeek; $i++) {
            $week[] = ['day' => null, 'date' => null, 'events' => []];
        }
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayVerta = Verta::parse($v->year . '/' . $v->month . '/' . $d . ' 12:00:00', $tehran);
            $dateStr = $dayVerta->datetime()->setTimezone($tehran)->format('Y-m-d'); // Gregorian for event matching
            $dayEvents = array_filter($events, fn ($e) => $e['date'] === $dateStr);
            $week[] = ['day' => $d, 'date' => $dateStr, 'events' => array_values($dayEvents)];
            if (count($week) >= 7) {
                $grid[] = $week;
                $week = [];
            }
        }
        if (!empty($week)) {
            while (count($week) < 7) {
                $week[] = ['day' => null, 'date' => null, 'events' => []];
            }
            $grid[] = $week;
        }
        return $grid;
    }

    private function authorizeReminderAccess(Reminder $reminder): void
    {
        $user = request()->user();
        if ($user->isAdmin()) {
            return;
        }
        if ($reminder->user_id === $user->id) {
            return;
        }
        if ($reminder->remindable_type === Lead::class && $reminder->remindable_id) {
            $lead = Lead::find($reminder->remindable_id);
            if ($lead && $lead->isVisibleTo($user)) {
                return;
            }
        }
        abort(403, 'شما به این مورد دسترسی ندارید.');
    }

    public function storeReminder(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'nullable|string',
            'due_date' => 'required|string',
            'due_time' => 'nullable|string|regex:/^\d{1,2}:\d{2}$/',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);
        $gregorian = FormatHelper::shamsiToGregorian($data['due_date']);
        if (!$gregorian) {
            return back()->withErrors(['due_date' => 'تاریخ نامعتبر است.'])->withInput();
        }
        $reminder = Reminder::create([
            'title' => $data['title'],
            'body' => $data['body'],
            'due_date' => $gregorian,
            'due_time' => $data['due_time'] ?? null,
            'type' => Reminder::TYPE_REMINDER,
            'user_id' => $request->user()->id,
        ]);
        $this->syncReminderTags($reminder, $request->input('tag_ids', []));
        return back()->with('success', 'یادآوری ذخیره شد.');
    }

    public function storeLeadTask(Request $request, Lead $lead)
    {
        abort_unless($lead->isVisibleTo($request->user()), 403, 'شما به این سرنخ دسترسی ندارید.');

        $data = $request->validate([
            'due_date' => 'required|string',
            'due_time' => 'nullable|string|regex:/^\d{1,2}:\d{2}$/',
            'tag_ids' => 'nullable|array',
            'tag_ids.*' => 'integer|exists:tags,id',
        ]);
        $gregorian = FormatHelper::shamsiToGregorian($data['due_date']);
        if (!$gregorian) {
            return back()->withErrors(['due_date' => 'تاریخ نامعتبر است.'])->withInput();
        }
        $reminder = Reminder::create([
            'title' => 'سرنخ: ' . $lead->name,
            'body' => $lead->company ?: null,
            'due_date' => $gregorian,
            'due_time' => $data['due_time'] ?? null,
            'type' => Reminder::TYPE_LEAD_TASK,
            'remindable_type' => Lead::class,
            'remindable_id' => $lead->id,
            'user_id' => $request->user()->id,
        ]);
        $this->syncReminderTags($reminder, $request->input('tag_ids', []));
        return redirect()->route('calendar.index')->with('success', 'سرنخ به تقویم اضافه شد.');
    }

    private function syncReminderTags(Reminder $reminder, array $tagIds): void
    {
        $validTagIds = Tag::forCurrentUser()
            ->whereIn('id', $tagIds)
            ->pluck('id')
            ->toArray();
        $reminder->tags()->sync($validTagIds);
    }

    public function toggleDone(Reminder $reminder)
    {
        $this->authorizeReminderAccess($reminder);

        if ($reminder->isDone()) {
            $reminder->markUndone();
            $msg = 'یادآوری به وضعیت انجام‌نشده برگردانده شد.';
        } else {
            $reminder->markDone();
            $msg = 'یادآوری انجام شد.';
        }
        return back()->with('success', $msg);
    }

    public function destroyReminder(Reminder $reminder)
    {
        $this->authorizeReminderAccess($reminder);

        $reminder->delete();
        return back()->with('success', 'یادآوری حذف شد.');
    }
}
