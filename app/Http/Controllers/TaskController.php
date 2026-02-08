<?php

namespace App\Http\Controllers;

use App\Helpers\FormatHelper;
use App\Models\Attachment;
use App\Models\Contact;
use App\Models\Invoice;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::visibleToUser($request->user())
            ->with('taskable', 'user', 'assignedUsers');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('taskable_type')) {
            $query->where('taskable_type', $request->taskable_type);
        }
        if ($request->filled('assigned_to')) {
            $query->whereHas('assignedUsers', fn ($q) => $q->where('users.id', $request->assigned_to));
        }

        $tasks = $query->latest('due_date')->latest('id')->paginate(20)->withQueryString();
        $users = User::orderBy('name')->get();

        return view('tasks.index', compact('tasks', 'users'));
    }

    public function show(Task $task)
    {
        $this->authorizeTask($task);
        $task->load('taskable', 'user', 'assignedUsers', 'attachments', 'logs.user');
        $users = User::orderBy('name')->get();

        return view('tasks.show', compact('task', 'users'));
    }

    public function create(Request $request)
    {
        $taskable = $this->resolveTaskable($request);
        $users = User::orderBy('name')->get();
        $shamsiToday = FormatHelper::shamsi(now());
        $dueDateShamsi = $shamsiToday;

        return view('tasks.create', compact('taskable', 'users', 'shamsiToday', 'dueDateShamsi'));
    }

    public function store(Request $request)
    {
        $taskable = $this->resolveTaskable($request);
        if ($taskable && !$this->canAccessTaskable($taskable)) {
            abort(403, 'شما به این مورد دسترسی ندارید.');
        }

        $dueDate = $request->get('due_date');
        if (is_string($dueDate) && trim($dueDate) !== '') {
            $gregorian = FormatHelper::shamsiToGregorian($dueDate);
            if ($gregorian !== null) {
                $request->merge(['due_date' => $gregorian]);
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:todo,in_progress,done,cancelled',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
        ]);

        $task = Task::create([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
            'taskable_type' => $taskable ? get_class($taskable) : null,
            'taskable_id' => $taskable?->id,
            'user_id' => $request->user()->id,
        ]);

        $task->assignedUsers()->sync($validated['assigned_user_ids'] ?? []);
        $task->log('created', null, null, 'وظیفه ایجاد شد');

        return redirect()->route('tasks.show', $task)->with('success', 'وظیفه ذخیره شد.');
    }

    public function edit(Task $task)
    {
        $this->authorizeTask($task);
        $users = User::orderBy('name')->get();
        $shamsiToday = FormatHelper::shamsi(now());
        $dueDateShamsi = $task->due_date ? FormatHelper::shamsi($task->due_date) : '';

        return view('tasks.edit', compact('task', 'users', 'shamsiToday', 'dueDateShamsi'));
    }

    public function update(Request $request, Task $task)
    {
        $this->authorizeTask($task);

        $dueDate = $request->get('due_date');
        if (is_string($dueDate) && trim($dueDate) !== '') {
            $gregorian = FormatHelper::shamsiToGregorian($dueDate);
            if ($gregorian !== null) {
                $request->merge(['due_date' => $gregorian]);
            }
        } elseif (is_string($dueDate) && trim($dueDate) === '') {
            $request->merge(['due_date' => null]);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'notes' => 'nullable|string|max:2000',
            'status' => 'required|in:todo,in_progress,done,cancelled',
            'due_date' => 'nullable|date',
            'due_time' => 'nullable|date_format:H:i',
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
        ]);

        $oldStatus = $task->status;
        $task->update([
            'title' => $validated['title'],
            'notes' => $validated['notes'] ?? null,
            'status' => $validated['status'],
            'due_date' => $validated['due_date'] ?? null,
            'due_time' => $validated['due_time'] ?? null,
        ]);

        $task->assignedUsers()->sync($validated['assigned_user_ids'] ?? []);
        if ($oldStatus !== $validated['status']) {
            $task->log('status_changed', $oldStatus, $validated['status']);
        }

        return redirect()->route('tasks.show', $task)->with('success', 'وظیفه به‌روز شد.');
    }

    public function destroy(Task $task)
    {
        $this->authorizeTask($task);
        $task->delete();

        return redirect()->route('tasks.index')->with('success', 'وظیفه حذف شد.');
    }

    public function storeNote(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $note = $request->validate(['notes' => 'required|string|max:2000'])['notes'];
        $task->update(['notes' => $note]);
        $task->log('note_updated', null, null, substr($note, 0, 100));

        return back()->with('success', 'یادداشت به‌روز شد.');
    }

    public function storeAttachment(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $request->validate(['file' => 'required|file|max:10240']); // 10MB

        $file = $request->file('file');
        $path = $file->store('attachments/tasks', 'public');
        $task->attachments()->create([
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
        $task->log('attachment_added', null, $file->getClientOriginalName());

        return back()->with('success', 'پیوست اضافه شد.');
    }

    public function destroyAttachment(Task $task, Attachment $attachment)
    {
        $this->authorizeTask($task);
        if ($attachment->attachable_type !== Task::class || $attachment->attachable_id != $task->id) {
            abort(404);
        }
        Storage::disk('public')->delete($attachment->path);
        $attachment->delete();

        return back()->with('success', 'پیوست حذف شد.');
    }

    public function changeStatus(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $status = $request->validate(['status' => 'required|in:todo,in_progress,done,cancelled'])['status'];
        $old = $task->status;
        $task->update(['status' => $status]);
        $task->log('status_changed', $old, $status);

        return back()->with('success', 'وضعیت به‌روز شد.');
    }

    public function assign(Request $request, Task $task)
    {
        $this->authorizeTask($task);
        $validated = $request->validate([
            'assigned_user_ids' => 'nullable|array',
            'assigned_user_ids.*' => 'exists:users,id',
        ]);
        $ids = $validated['assigned_user_ids'] ?? [];
        $task->assignedUsers()->sync($ids);
        $task->log('user_assigned', null, null, count($ids) ? 'واگذار به ' . count($ids) . ' نفر' : 'واگذاری لغو شد');

        return back()->with('success', count($ids) ? 'واگذاری به‌روز شد.' : 'واگذاری لغو شد.');
    }

    private function authorizeTask(Task $task): void
    {
        $user = request()->user();
        if (!$user) {
            abort(403, 'لطفاً وارد شوید.');
        }
        if ($user->isAdmin()) {
            return;
        }
        if ($task->user_id === $user->id) {
            return;
        }
        if ($task->assignedUsers()->where('users.id', $user->id)->exists()) {
            return;
        }
        $taskable = $task->taskable;
        if ($taskable && method_exists($taskable, 'isVisibleTo') && $taskable->isVisibleTo($user)) {
            return;
        }
        abort(403, 'شما به این وظیفه دسترسی ندارید.');
    }

    private function resolveTaskable(Request $request)
    {
        $type = $request->get('taskable_type');
        $id = (int) $request->get('taskable_id');
        if (!$type || !$id) {
            return null;
        }
        $model = match ($type) {
            'lead' => Lead::find($id),
            'invoice' => Invoice::find($id),
            'contact' => Contact::find($id),
            default => null,
        };

        return $model;
    }

    public function searchTaskableApi(Request $request)
    {
        $type = $request->get('type'); // contact, lead, invoice
        $q = trim((string) $request->get('q', ''));
        if (!in_array($type, ['contact', 'lead', 'invoice'], true) || $q === '') {
            return response()->json([]);
        }
        $user = $request->user();
        if (!$user) {
            return response()->json([]);
        }
        $like = '%' . $q . '%';
        if ($type === 'contact') {
            $items = Contact::query()
                ->visibleToUser($user)
                ->where('name', 'like', $like)
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name'])
                ->map(fn ($c) => ['id' => $c->id, 'name' => $c->name, 'type' => 'contact']);
        } elseif ($type === 'lead') {
            $items = Lead::query()
                ->visibleToUser($user)
                ->search($q)
                ->orderBy('name')
                ->limit(20)
                ->get(['id', 'name'])
                ->map(fn ($l) => ['id' => $l->id, 'name' => $l->name, 'type' => 'lead']);
        } else {
            $items = Invoice::query()
                ->visibleToUser($user)
                ->with('contact:id,name')
                ->where(function ($query) use ($like) {
                    $query->where('invoice_number', 'like', $like)
                        ->orWhereHas('contact', fn ($cq) => $cq->where('name', 'like', $like));
                })
                ->orderByDesc('id')
                ->limit(20)
                ->get()
                ->map(fn ($i) => [
                    'id' => $i->id,
                    'name' => ($i->type === 'buy' ? 'رسید ' : 'فاکتور ') . ($i->invoice_number ?? $i->id) . ($i->contact ? ' — ' . $i->contact->name : ''),
                    'type' => 'invoice',
                ]);
        }
        return response()->json($items->values()->all());
    }

    private function canAccessTaskable($model): bool
    {
        $user = request()->user();
        if (!$user) {
            return false;
        }
        if (method_exists($model, 'isVisibleTo')) {
            return $model->isVisibleTo($user);
        }

        return true;
    }
}
