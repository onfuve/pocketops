@php use App\Models\Task; @endphp
@extends('layouts.app')

@section('title', 'وظیفه جدید — ' . config('app.name'))

@section('content')
<div style="max-width: 36rem; margin: 0 auto; padding: 0 1rem; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5'])
            وظیفه جدید
        </h1>
        @if ($taskable)
            @php
                $label = $taskable instanceof \App\Models\Lead ? 'سرنخ: ' . $taskable->name : ($taskable instanceof \App\Models\Invoice ? (($taskable->type === 'buy' ? 'رسید' : 'فاکتور') . ': ' . ($taskable->invoice_number ?? $taskable->id)) : 'مخاطب: ' . $taskable->name);
            @endphp
            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #0369a1;">مرتبط با: {{ $label }}</p>
        @endif
    </div>

    <form action="{{ route('tasks.store') }}" method="post" style="background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem;">
        @csrf
        @if ($taskable)
            <input type="hidden" name="taskable_type" value="{{ $taskable instanceof \App\Models\Lead ? 'lead' : ($taskable instanceof \App\Models\Invoice ? 'invoice' : 'contact') }}">
            <input type="hidden" name="taskable_id" value="{{ $taskable->id }}">
        @else
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">مرتبط با</label>
                <select name="taskable_type" required style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    <option value="">انتخاب کنید…</option>
                    <option value="lead">سرنخ</option>
                    <option value="invoice">فاکتور / رسید</option>
                    <option value="contact">مخاطب</option>
                </select>
            </div>
            <div style="margin-bottom: 1rem;">
                <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">شناسه</label>
                <input type="number" name="taskable_id" required placeholder="مثلاً 2" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
            </div>
        @endif
        <div style="margin-bottom: 1rem;">
            <label for="title" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">عنوان <span style="color: #b91c1c;">*</span></label>
            <input type="text" name="title" id="title" required value="{{ old('title') }}" placeholder="عنوان وظیفه" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
            @error('title')<p style="margin: 0.25rem 0 0 0; font-size: 0.8125rem; color: #b91c1c;">{{ $message }}</p>@enderror
        </div>
        <div style="margin-bottom: 1rem;">
            <label for="notes" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">یادداشت</label>
            <textarea name="notes" id="notes" rows="4" placeholder="توضیحات…" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">{{ old('notes') }}</textarea>
        </div>
        <div style="margin-bottom: 1rem; display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
            <div>
                <label for="status" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">وضعیت</label>
                <select name="status" id="status" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    @foreach (Task::statusLabels() as $val => $label)
                        <option value="{{ $val }}" {{ old('status', 'todo') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="due_date" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">تاریخ</label>
                <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
            </div>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">واگذار به</label>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach ($users as $u)
                    <label style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; cursor: pointer; font-size: 0.875rem;">
                        <input type="checkbox" name="assigned_user_ids[]" value="{{ $u->id }}" {{ in_array($u->id, old('assigned_user_ids', [])) ? 'checked' : '' }}>
                        {{ $u->name }}
                    </label>
                @endforeach
            </div>
        </div>
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.9375rem; font-weight: 600; border: none; cursor: pointer;">ذخیره</button>
            @php
                $backUrl = $taskable ? match (get_class($taskable)) { \App\Models\Lead::class => route('leads.show', $taskable), \App\Models\Invoice::class => route('invoices.show', $taskable), \App\Models\Contact::class => route('contacts.show', $taskable), default => route('tasks.index') } : route('tasks.index');
            @endphp
            <a href="{{ $backUrl }}" style="display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #fff; color: #44403c; border: 2px solid #d6d3d1; text-decoration: none; font-size: 0.9375rem;">انصراف</a>
        </div>
    </form>
</div>
@endsection
