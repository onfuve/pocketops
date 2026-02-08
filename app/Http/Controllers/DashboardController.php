<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Reminder;
use App\Models\Task;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $today = now()->format('Y-m-d');
        $endOfWeek = now()->addDays(7)->format('Y-m-d');

        // Stats
        $contactsCount = Contact::visibleToUser($user)->count();
        $leadsCount = Lead::visibleToUser($user)->count();
        $tasksCount = Task::visibleToUser($user)
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
            ->where(function ($q) use ($today) {
                $q->whereNull('due_date')->orWhere('due_date', '>=', $today);
            })
            ->count();
        $tasksOverdue = Task::visibleToUser($user)
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
            ->where('due_date', '<', $today)
            ->count();
        $invoicesUnpaid = Invoice::visibleToUser($user)
            ->where('status', Invoice::STATUS_FINAL)
            ->whereNotNull('due_date')
            ->get()
            ->filter(fn ($inv) => !$inv->isPaid());
        $invoicesOverdue = $invoicesUnpaid->filter(fn ($inv) => $inv->due_date->format('Y-m-d') < $today)->count();

        // Today's reminders (undone)
        $remindersQuery = Reminder::with('tags', 'remindable')
            ->whereNull('done_at')
            ->where('due_date', $today);
        if (!$user->isAdmin()) {
            $remindersQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHasMorph('remindable', [Lead::class], fn ($q2) => $q2->where(function ($q3) use ($user) {
                        $q3->where('user_id', $user->id)->orWhere('assigned_to_id', $user->id);
                    }));
            });
        }
        $todayReminders = $remindersQuery->orderBy('due_time')->get();

        // Upcoming reminders (next 7 days, exclude today)
        $upcomingRemindersQuery = Reminder::with('tags', 'remindable')
            ->whereNull('done_at')
            ->where('due_date', '>', $today)
            ->where('due_date', '<=', $endOfWeek);
        if (!$user->isAdmin()) {
            $upcomingRemindersQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereHasMorph('remindable', [Lead::class], fn ($q2) => $q2->where(function ($q3) use ($user) {
                        $q3->where('user_id', $user->id)->orWhere('assigned_to_id', $user->id);
                    }));
            });
        }
        $upcomingReminders = $upcomingRemindersQuery->orderBy('due_date')->orderBy('due_time')->limit(10)->get();

        // Tasks needing attention (todo/in_progress, overdue or due soon)
        $tasksNeedingAttention = Task::visibleToUser($user)
            ->with('taskable')
            ->whereIn('status', [Task::STATUS_TODO, Task::STATUS_IN_PROGRESS])
            ->where(function ($q) use ($today, $endOfWeek) {
                $q->where('due_date', '<', $today) // overdue
                    ->orWhereBetween('due_date', [$today, $endOfWeek]); // due within 7 days
            })
            ->orderBy('due_date')
            ->orderBy('due_time')
            ->limit(8)
            ->get();

        // Leads needing follow-up (new, contacted - active pipeline)
        $leadsNeedingAttention = Lead::visibleToUser($user)
            ->whereIn('status', [Lead::STATUS_NEW, Lead::STATUS_CONTACTED])
            ->orderByDesc('lead_date')
            ->limit(6)
            ->get();

        // Overdue invoices
        $overdueInvoices = $invoicesUnpaid->filter(fn ($inv) => $inv->due_date->format('Y-m-d') < $today)->take(5)->values();

        // Upcoming invoice dues (next 7 days)
        $upcomingInvoiceDues = $invoicesUnpaid->filter(fn ($inv) => $inv->due_date->format('Y-m-d') >= $today && $inv->due_date->format('Y-m-d') <= $endOfWeek)->take(5)->values();

        return view('dashboard.index', compact(
            'contactsCount',
            'leadsCount',
            'tasksCount',
            'tasksOverdue',
            'invoicesUnpaid',
            'invoicesOverdue',
            'todayReminders',
            'upcomingReminders',
            'tasksNeedingAttention',
            'leadsNeedingAttention',
            'overdueInvoices',
            'upcomingInvoiceDues'
        ));
    }
}
