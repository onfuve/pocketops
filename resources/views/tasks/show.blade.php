@php use App\Helpers\FormatHelper; use App\Models\Task; @endphp
@extends('layouts.app')

@section('title', $task->title . ' — وظیفه — ' . config('app.name'))

@section('content')
<div style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem; display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
        <div>
            <h1 style="margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">{{ $task->title }}</h1>
            <a href="{{ $task->taskableLink() }}" style="font-size: 0.875rem; color: #0369a1; text-decoration: none;">{{ $task->taskableLabel() }}</a>
            @if ($task->due_date)
                <span style="font-size: 0.875rem; color: #78716c; margin-right: 0.5rem;">— {{ FormatHelper::shamsi($task->due_date) }}{{ $task->due_time ? ' ' . substr($task->due_time, 0, 5) : '' }}</span>
            @endif
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('tasks.edit', $task) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: #fff; color: #44403c; border: 2px solid #d6d3d1; text-decoration: none; font-size: 0.875rem;">ویرایش</a>
            <form action="{{ route('tasks.change-status', $task) }}" method="post" style="display: inline;">
                @csrf
                <select name="status" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 0.875rem; cursor: pointer;">
                    @foreach (Task::statusLabels() as $st => $label)
                        <option value="{{ $st }}" {{ $task->status === $st ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
            <form action="{{ route('tasks.destroy', $task) }}" method="post" style="display: inline;" onsubmit="return confirm('وظیفه حذف شود؟');">
                @csrf
                @method('DELETE')
                <button type="submit" style="padding: 0.5rem 1rem; border-radius: 0.5rem; background: #fff; color: #b91c1c; border: 2px solid #fecaca; font-size: 0.875rem; cursor: pointer;">حذف</button>
            </form>
            <a href="{{ route('tasks.index') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: #fff; color: #44403c; border: 2px solid #d6d3d1; text-decoration: none; font-size: 0.875rem;">لیست</a>
        </div>
    </div>

    {{-- Status badge --}}
    <div style="margin-bottom: 1.5rem; padding: 1rem; border-radius: 0.75rem; background: {{ Task::statusColors()[$task->status] }}20; border: 2px solid {{ Task::statusColors()[$task->status] }}40;">
        <span style="font-weight: 600; color: {{ Task::statusColors()[$task->status] }};">{{ Task::statusLabels()[$task->status] }}</span>
    </div>

    {{-- واگذاری به عضو تیم --}}
    <div style="margin-bottom: 1.5rem; padding: 1.25rem; border-radius: 0.75rem; background: #f0f9ff; border: 2px solid #bae6fd;">
        <h3 style="font-size: 0.9375rem; font-weight: 600; color: #0369a1; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4'])
            واگذاری به عضو تیم
        </h3>
        <p style="font-size: 0.8125rem; color: #64748b; margin: 0 0 0.75rem 0;">با واگذاری، این اعضای تیم نیز وظیفه را می‌بینند و می‌توانند روی آن کار کنند.</p>
        @if ($task->assignedUsers->isNotEmpty())
            <p style="font-size: 0.875rem; margin: 0 0 0.75rem 0; color: #0369a1;">در حال حاضر واگذار شده به: <strong>{{ $task->assignedUsers->pluck('name')->implode('، ') }}</strong></p>
        @endif
        <form action="{{ route('tasks.assign', $task) }}" method="post">
            @csrf
            @include('tasks._assignees', ['users' => $users ?? collect(), 'selectedIds' => $task->assignedUsers->pluck('id')->toArray(), 'showMeLabel' => true])
            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: #0284c7; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer;">
                @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4'])
                <span>واگذار / به‌روزرسانی</span>
            </button>
        </form>
    </div>

    {{-- Notes --}}
    <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #fff; border: 2px solid #e7e5e4; border-radius: 0.75rem;">
        <h2 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600; color: #292524;">یادداشت</h2>
        @if ($task->notes)
            <p style="margin: 0; white-space: pre-wrap; font-size: 0.9375rem; color: #44403c;">{{ $task->notes }}</p>
            <form action="{{ route('tasks.notes.store', $task) }}" method="post" style="margin-top: 1rem;">
                @csrf
                <textarea name="notes" rows="3" placeholder="به‌روزرسانی یادداشت" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; margin-bottom: 0.5rem;">{{ $task->notes }}</textarea>
                <button type="submit" style="padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #0369a1; color: #fff; border: none; font-size: 0.8125rem; cursor: pointer;">به‌روزرسانی</button>
            </form>
        @else
            <form action="{{ route('tasks.notes.store', $task) }}" method="post">
                @csrf
                <textarea name="notes" rows="3" placeholder="یادداشت اضافه کنید…" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; margin-bottom: 0.5rem;"></textarea>
                <button type="submit" style="padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #0369a1; color: #fff; border: none; font-size: 0.8125rem; cursor: pointer;">ذخیره یادداشت</button>
            </form>
        @endif
    </div>

    {{-- Attachments --}}
    <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #fff; border: 2px solid #e7e5e4; border-radius: 0.75rem;">
        <h2 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600; color: #292524;">پیوست‌ها</h2>
        <form action="{{ route('tasks.attachments.store', $task) }}" method="post" enctype="multipart/form-data" style="margin-bottom: 1rem;">
            @csrf
            <input type="file" name="file" required style="font-size: 0.875rem;">
            <button type="submit" style="padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #059669; color: #fff; border: none; font-size: 0.8125rem; cursor: pointer; margin-right: 0.5rem;">افزودن</button>
        </form>
        @if ($task->attachments->isEmpty())
            <p style="margin: 0; font-size: 0.875rem; color: #78716c;">پیوستی ثبت نشده.</p>
        @else
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach ($task->attachments as $att)
                    <div style="padding: 0.5rem 0.75rem; background: #f5f5f4; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                        <a href="{{ $att->url() }}" target="_blank" style="font-size: 0.875rem; color: #0369a1; text-decoration: none;">{{ $att->original_name }}</a>
                        <form action="{{ route('tasks.attachments.destroy', [$task, $att]) }}" method="post" style="display: inline;" onsubmit="return confirm('حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="padding: 0.125rem 0.375rem; font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer;">حذف</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Log --}}
    @if ($task->logs->isNotEmpty())
        <div style="margin-bottom: 1.5rem; padding: 1.5rem; background: #fff; border: 2px solid #e7e5e4; border-radius: 0.75rem;">
            <h2 style="margin: 0 0 0.75rem 0; font-size: 1rem; font-weight: 600; color: #292524;">تاریخچه</h2>
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($task->logs as $log)
                    <div style="padding: 0.5rem 0.75rem; background: #fafaf9; border-radius: 0.5rem; font-size: 0.8125rem;">
                        <span style="font-weight: 600;">{{ $log->actionLabel() }}</span>
                        @if ($log->old_value && $log->new_value)
                            <span style="color: #78716c;">{{ Task::statusLabels()[$log->old_value] ?? $log->old_value }} → {{ Task::statusLabels()[$log->new_value] ?? $log->new_value }}</span>
                        @endif
                        @if ($log->message)
                            <span style="color: #57534e;">— {{ Str::limit($log->message, 80) }}</span>
                        @endif
                        <span style="color: #a8a29e;">— {{ $log->user?->name ?? 'سیستم' }} — {{ FormatHelper::shamsi($log->created_at) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
@endsection
