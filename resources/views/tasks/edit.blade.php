@php use App\Helpers\FormatHelper; use App\Models\Task; @endphp
@extends('layouts.app')

@section('title', 'ویرایش وظیفه — ' . config('app.name'))

@section('content')
<div style="max-width: 36rem; margin: 0 auto; padding: 0 1rem; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
            ویرایش وظیفه
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c;">{{ $task->taskableLabel() }}</p>
    </div>

    <form action="{{ route('tasks.update', $task) }}" method="post" style="background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem;">
        @csrf
        @method('PUT')
        <div style="margin-bottom: 1rem;">
            <label for="title" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">عنوان <span style="color: #b91c1c;">*</span></label>
            <input type="text" name="title" id="title" required value="{{ old('title', $task->title) }}" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
            @error('title')<p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: #b91c1c;">{{ $message }}</p>@enderror
        </div>
        <div style="margin-bottom: 1rem;">
            <label for="notes" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">یادداشت</label>
            <textarea name="notes" id="notes" rows="4" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">{{ old('notes', $task->notes) }}</textarea>
        </div>
        <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="status" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">وضعیت</label>
                <select name="status" id="status" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    @foreach (Task::statusLabels() as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $task->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="due_date" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">تاریخ سررسید</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="due_date" id="due_date" value="{{ old('due_date', $dueDateShamsi) }}" placeholder="۱۴۰۳/۱۱/۱۷" autocomplete="off"
                           style="flex: 1; min-width: 0; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    <button type="button" id="due_date_today" class="ds-btn ds-btn-secondary" data-today="{{ $shamsiToday }}">امروز</button>
                </div>
            </div>
        </div>
        @include('tasks._assignees', ['users' => $users, 'selectedIds' => old('assigned_user_ids', $task->assignedUsers->pluck('id')->toArray())])
        <div style="display: flex; gap: 0.75rem;">
            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.9375rem; font-weight: 600; border: none; cursor: pointer;">ذخیره</button>
            <a href="{{ route('tasks.show', $task) }}" style="display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #fff; color: #44403c; border: 2px solid #d6d3d1; text-decoration: none; font-size: 0.9375rem;">انصراف</a>
        </div>
    </form>
</div>

@push('scripts')
<script>
(function () {
    var btn = document.getElementById('due_date_today');
    var input = document.getElementById('due_date');
    if (btn && input) {
        btn.addEventListener('click', function () {
            input.value = this.getAttribute('data-today') || '';
        });
    }
})();
</script>
@endpush
@endsection
