@php use App\Helpers\FormatHelper; use App\Models\Task; @endphp
@extends('layouts.app')

@section('title', 'وظیفه جدید — ' . config('app.name'))

@section('content')
<div style="max-width: 36rem; margin: 0 auto; padding: 0 1rem; font-family: 'Vazirmatn', sans-serif;">
    <div style="margin-bottom: 1.5rem;">
        <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524;">
            @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5'])
            وظیفه جدید
        </h1>
        <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c;">وظیفه می‌تواند مستقل باشد یا به مخاطب، سرنخ یا فاکتور مرتبط شود.</p>
    </div>

    <form action="{{ route('tasks.store') }}" method="post" id="task-create-form" style="background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem;">
        @csrf
        <input type="hidden" name="taskable_type" id="taskable_type" value="{{ $taskable ? ($taskable instanceof \App\Models\Lead ? 'lead' : ($taskable instanceof \App\Models\Invoice ? 'invoice' : 'contact')) : '' }}">
        <input type="hidden" name="taskable_id" id="taskable_id" value="{{ $taskable?->id ?? '' }}">

        <div style="margin-bottom: 1rem;" id="taskable-section">
            <label style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">مرتبط با <span style="font-weight: 400; color: #78716c;">(اختیاری)</span></label>
            <div id="taskable-chip" style="{{ $taskable ? '' : 'display: none;' }} margin-bottom: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; background: #e0f2fe; border: 2px solid #7dd3fc; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
                <span id="taskable-chip-label" style="font-size: 0.875rem; color: #0369a1;">
                    @if ($taskable)
                        @php
                            $label = $taskable instanceof \App\Models\Lead ? 'سرنخ: ' . $taskable->name : ($taskable instanceof \App\Models\Invoice ? (($taskable->type === 'buy' ? 'رسید' : 'فاکتور') . ': ' . ($taskable->invoice_number ?? $taskable->id)) : 'مخاطب: ' . $taskable->name);
                        @endphp
                        {{ $label }}
                    @endif
                </span>
                <button type="button" id="taskable-clear" class="ds-btn ds-btn-ghost" style="padding: 0.25rem 0.5rem; min-height: auto; font-size: 0.8125rem;">× حذف</button>
            </div>
            <div id="taskable-search-block" style="{{ $taskable ? 'display: none;' : '' }}">
                <select id="taskable_type_select" style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; margin-bottom: 0.5rem;">
                    <option value="">— بدون ارتباط —</option>
                    <option value="contact">مخاطب</option>
                    <option value="lead">سرنخ</option>
                    <option value="invoice">فاکتور / رسید</option>
                </select>
                <div id="taskable-search-wrap" style="display: none; position: relative;">
                    <input type="text" id="taskable_search" placeholder="جستجو نام…" autocomplete="off"
                           style="width: 100%; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    <div id="taskable_results" class="dropdown-results hidden" style="position: absolute; top: 100%; right: 0; left: 0; z-index: 50; margin-top: 2px; max-height: 12rem; overflow-y: auto; background: #fff; border: 2px solid #d6d3d1; border-radius: 0.5rem; box-shadow: 0 4px 12px rgba(0,0,0,0.1);"></div>
                </div>
            </div>
        </div>

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
                <label for="due_date" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.375rem;">تاریخ سررسید</label>
                <div style="display: flex; gap: 0.5rem;">
                    <input type="text" name="due_date" id="due_date" value="{{ old('due_date', $dueDateShamsi) }}" placeholder="۱۴۰۳/۱۱/۱۷" autocomplete="off"
                           style="flex: 1; min-width: 0; padding: 0.5rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem;">
                    <button type="button" id="due_date_today" class="ds-btn ds-btn-secondary" data-today="{{ $shamsiToday }}">امروز</button>
                </div>
            </div>
        </div>
        @include('tasks._assignees', ['users' => $users, 'selectedIds' => old('assigned_user_ids', [])])
        <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
            <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #059669; color: #fff; font-size: 0.9375rem; font-weight: 600; border: none; cursor: pointer;">ذخیره</button>
            @php
                $backUrl = $taskable ? match (get_class($taskable)) { \App\Models\Lead::class => route('leads.show', $taskable), \App\Models\Invoice::class => route('invoices.show', $taskable), \App\Models\Contact::class => route('contacts.show', $taskable), default => route('tasks.index') } : route('tasks.index');
            @endphp
            <a href="{{ $backUrl }}" style="display: inline-flex; align-items: center; padding: 0.5rem 1.25rem; border-radius: 0.5rem; background: #fff; color: #44403c; border: 2px solid #d6d3d1; text-decoration: none; font-size: 0.9375rem;">انصراف</a>
        </div>
    </form>
</div>

@push('styles')
<style>
.dropdown-results { list-style: none; padding: 0.25rem; margin: 0; }
.dropdown-results a { display: block; padding: 0.5rem 0.75rem; border-radius: 0.375rem; font-size: 0.875rem; color: #44403c; text-decoration: none; }
.dropdown-results a:hover { background: #f5f5f4; }
.dropdown-results.hidden { display: none !important; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    var dueBtn = document.getElementById('due_date_today');
    var dueInput = document.getElementById('due_date');
    if (dueBtn && dueInput) {
        dueBtn.addEventListener('click', function () {
            dueInput.value = this.getAttribute('data-today') || '';
        });
    }

    var chip = document.getElementById('taskable-chip');
    var chipLabel = document.getElementById('taskable-chip-label');
    var clearBtn = document.getElementById('taskable-clear');
    var searchBlock = document.getElementById('taskable-search-block');
    var typeSelect = document.getElementById('taskable_type_select');
    var searchWrap = document.getElementById('taskable-search-wrap');
    var searchInput = document.getElementById('taskable_search');
    var resultsDiv = document.getElementById('taskable_results');
    var typeHidden = document.getElementById('taskable_type');
    var idHidden = document.getElementById('taskable_id');

    function setTaskable(type, id, label) {
        typeHidden.value = type || '';
        idHidden.value = id || '';
        chipLabel.textContent = label || '';
        chip.style.display = type && id ? 'flex' : 'none';
        searchBlock.style.display = type && id ? 'none' : 'block';
        if (type && id) {
            typeSelect.value = type;
            searchInput.value = '';
            searchWrap.style.display = 'none';
            resultsDiv.classList.add('hidden');
        } else {
            typeSelect.value = '';
            searchWrap.style.display = 'none';
        }
    }

    if (clearBtn) {
        clearBtn.addEventListener('click', function () {
            setTaskable('', '', '');
        });
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            var val = this.value;
            searchWrap.style.display = val ? 'block' : 'none';
            searchInput.value = '';
            resultsDiv.classList.add('hidden');
            if (!val) {
                typeHidden.value = '';
                idHidden.value = '';
            }
        });
    }

    var debounce = null;
    if (searchInput && resultsDiv) {
        searchInput.addEventListener('input', function () {
            var q = searchInput.value.trim();
            var type = typeSelect.value;
            if (!type || q.length < 1) {
                resultsDiv.classList.add('hidden');
                return;
            }
            clearTimeout(debounce);
            debounce = setTimeout(function () {
                fetch('{{ route("tasks.search-taskable.api") }}?type=' + encodeURIComponent(type) + '&q=' + encodeURIComponent(q), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                }).then(function (r) { return r.json(); }).then(function (list) {
                    resultsDiv.innerHTML = '';
                    if (list.length === 0) {
                        resultsDiv.classList.add('hidden');
                        return;
                    }
                    list.forEach(function (item) {
                        var a = document.createElement('a');
                        a.href = '#';
                        a.textContent = item.name;
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                            setTaskable(item.type, item.id, item.name);
                        });
                        resultsDiv.appendChild(a);
                    });
                    resultsDiv.classList.remove('hidden');
                });
            }, 200);
        });
        searchInput.addEventListener('blur', function () {
            setTimeout(function () { resultsDiv.classList.add('hidden'); }, 150);
        });
    }
})();
</script>
@endpush
@endsection
