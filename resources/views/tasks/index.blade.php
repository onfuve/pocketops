@php use App\Helpers\FormatHelper; use App\Models\Task; @endphp
@extends('layouts.app')

@section('title', 'وظایف — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #0369a1; border-color: #93c5fd; }
.task-card:hover { border-color: #93c5fd; background: #f0f9ff; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5'])
                </span>
                وظایف
            </h1>
            <p class="ds-page-subtitle">وظایف مرتبط با سرنخ، فاکتور، رسید و مخاطب را مدیریت کنید.</p>
        </div>
        <a href="{{ route('tasks.create') }}" class="ds-btn ds-btn-primary">+ وظیفه جدید</a>
    </div>

    {{-- Filters --}}
    <div class="ds-filter-tabs">
        <a href="{{ route('tasks.index') }}" class="{{ !request('status') ? 'ds-filter-active' : '' }}" style="{{ !request('status') ? 'background: #0369a1; color: #fff;' : '' }}">همه</a>
        @foreach (Task::statusLabels() as $st => $label)
            <a href="{{ route('tasks.index', array_merge(request()->except('status'), ['status' => $st])) }}" class="{{ request('status') === $st ? 'ds-filter-active' : '' }}" style="{{ request('status') === $st ? 'background: #0369a1; color: #fff;' : '' }}">{{ $label }}</a>
        @endforeach
    </div>

    @if ($tasks->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 1rem 0; color: var(--ds-text-subtle); font-size: 0.9375rem;">هنوز وظیفه‌ای ثبت نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem; color: var(--ds-text-faint);">از صفحه سرنخ، فاکتور یا مخاطب می‌توانید وظیفه ایجاد کنید یا از دکمه بالا.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
            @foreach ($tasks as $task)
                <a href="{{ route('tasks.show', $task) }}" class="ds-card task-card">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 600; color: var(--ds-text); font-size: 0.9375rem;">{{ $task->title }}</div>
                            <div style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-top: 0.25rem;">{{ $task->taskableLabel() }} — {{ FormatHelper::shamsi($task->due_date ?? $task->created_at) }}</div>
                        </div>
                        <span style="display: inline-flex; align-items: center; padding: 0.25rem 0.625rem; border-radius: 0.375rem; font-size: 0.75rem; font-weight: 600; background: {{ Task::statusColors()[$task->status] }}20; color: {{ Task::statusColors()[$task->status] }};">{{ Task::statusLabels()[$task->status] }}</span>
                    </div>
                    @if ($task->assignedUsers->isNotEmpty())
                        <div style="font-size: 0.75rem; color: var(--ds-text-faint); margin-top: 0.5rem;">واگذار به: {{ $task->assignedUsers->pluck('name')->implode('، ') }}</div>
                    @endif
                </a>
            @endforeach
        </div>
        <div style="margin-top: 1.5rem;">{{ $tasks->links() }}</div>
    @endif
</div>
@endsection
