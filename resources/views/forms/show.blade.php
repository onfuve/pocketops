@extends('layouts.app')

@section('title', $form->title . ' — ' . config('app.name'))

@push('styles')
<style>
.form-link-create .link-field { position: relative; }
.form-link-create .link-field input[type="text"] { width: 100%; }
.form-link-create .link-results { position: absolute; left: 0; right: 0; top: 100%; margin-top: 0.25rem; z-index: 20; max-height: 12rem; overflow-y: auto; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); background: var(--ds-bg); box-shadow: var(--ds-shadow-hover); }
.form-link-create .link-results a, .form-link-create .link-results button { display: block; width: 100%; padding: 0.625rem 1rem; text-align: right; font-size: 0.875rem; color: var(--ds-text); border: none; background: none; cursor: pointer; font-family: inherit; border-bottom: 1px solid var(--ds-bg-subtle); }
.form-link-create .link-results a:last-child, .form-link-create .link-results button:last-child { border-bottom: none; }
.form-link-create .link-results a:hover, .form-link-create .link-results button:hover { background: var(--ds-bg-subtle); }
.form-link-create .link-results .empty { padding: 0.75rem 1rem; font-size: 0.8125rem; color: var(--ds-text-subtle); }
.form-link-create .link-chosen { display: flex; align-items: center; gap: 0.5rem; margin-top: 0.35rem; font-size: 0.8125rem; color: var(--ds-primary); font-weight: 500; }
.form-link-create .link-chosen button { padding: 0.2rem 0.5rem; font-size: 0.75rem; color: var(--ds-text-subtle); background: none; border: none; cursor: pointer; text-decoration: underline; }
.form-link-create .link-chosen button:hover { color: #b91c1c; }
.form-link-create .link-row { margin-bottom: 1rem; }
.form-link-create .link-row:last-of-type { margin-bottom: 0; }
.form-link-create .btn-generic { margin-bottom: 1rem; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #e0f2fe; color: #0369a1;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                {{ $form->title }}
            </h1>
            <p class="ds-page-subtitle">لینک بسازید و ارسال‌ها را ببینید.</p>
        </div>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('forms.edit', $form) }}" class="ds-btn ds-btn-outline">ویرایش فرم</a>
            <a href="{{ route('forms.inbox') }}" class="ds-btn ds-btn-outline">صندوق ورودی</a>
        </div>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('new_link'))
        @php $newLink = session('new_link'); @endphp
        <div class="ds-form-card" style="margin-bottom: 1.5rem; border-color: #059669; background: #ecfdf5;">
            <p style="font-weight: 600; margin-bottom: 0.5rem;">لینک جدید ساخته شد. این لینک را برای مشتری بفرستید:</p>
            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                <input type="text" readonly value="{{ $newLink->public_url }}" id="form-link-url" style="flex: 1; min-width: 12rem; padding: 0.5rem 0.75rem; border-radius: var(--ds-radius); border: 2px solid var(--ds-border); font-size: 0.875rem;">
                <button type="button" class="ds-btn ds-btn-primary" onclick="navigator.clipboard.writeText(document.getElementById('form-link-url').value); this.textContent='کپی شد!'; setTimeout(() => this.textContent='کپی لینک', 1500);">کپی لینک</button>
            </div>
        </div>
    @endif

    {{-- Create link --}}
    @if($form->status === 'active')
        <div class="ds-form-card form-link-create" style="margin-bottom: 1.5rem;">
            <h2 class="ds-form-card-title">ساخت لینک جدید</h2>
            <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-bottom: 1rem;">هر لینک برای یک ارسال است. برای لینک عمومی هیچ‌کدام را انتخاب نکنید؛ یا جستجو کنید و یکی را انتخاب کنید.</p>
            <form action="{{ route('forms.links.store', $form) }}" method="post" id="form-link-create-form">
                @csrf
                <input type="hidden" name="contact_id" id="link_contact_id" value="">
                <input type="hidden" name="lead_id" id="link_lead_id" value="">
                <input type="hidden" name="task_id" id="link_task_id" value="">
                <button type="button" class="ds-btn ds-btn-outline btn-generic" id="btn-generic-link">لینک عمومی (بدون مخاطب / سرنخ / وظیفه)</button>
                <div class="link-row">
                    <label for="link_contact_search" class="ds-label">مخاطب (اختیاری)</label>
                    <div class="link-field">
                        <input type="text" id="link_contact_search" class="ds-input" placeholder="جستجو نام مخاطب…" autocomplete="off">
                        <div id="link_contact_results" class="link-results hidden"></div>
                    </div>
                    <div id="link_contact_chosen" class="link-chosen hidden"></div>
                </div>
                <div class="link-row">
                    <label for="link_lead_search" class="ds-label">سرنخ (اختیاری)</label>
                    <div class="link-field">
                        <input type="text" id="link_lead_search" class="ds-input" placeholder="جستجو نام سرنخ…" autocomplete="off">
                        <div id="link_lead_results" class="link-results hidden"></div>
                    </div>
                    <div id="link_lead_chosen" class="link-chosen hidden"></div>
                </div>
                <div class="link-row">
                    <label for="link_task_search" class="ds-label">وظیفه (اختیاری)</label>
                    <div class="link-field">
                        <input type="text" id="link_task_search" class="ds-input" placeholder="جستجو عنوان وظیفه…" autocomplete="off">
                        <div id="link_task_results" class="link-results hidden"></div>
                    </div>
                    <div id="link_task_chosen" class="link-chosen hidden"></div>
                </div>
                <button type="submit" class="ds-btn ds-btn-primary" style="margin-top: 1rem;">ساخت لینک جدید</button>
            </form>
        </div>
        <script>
        (function(){
            var contactSearch = document.getElementById('link_contact_search');
            var contactId = document.getElementById('link_contact_id');
            var contactResults = document.getElementById('link_contact_results');
            var contactChosen = document.getElementById('link_contact_chosen');
            var leadSearch = document.getElementById('link_lead_search');
            var leadId = document.getElementById('link_lead_id');
            var leadResults = document.getElementById('link_lead_results');
            var leadChosen = document.getElementById('link_lead_chosen');
            var taskSearch = document.getElementById('link_task_search');
            var taskId = document.getElementById('link_task_id');
            var taskResults = document.getElementById('link_task_results');
            var taskChosen = document.getElementById('link_task_chosen');
            var tasksData = @json(($tasks ?? collect())->map(fn($t) => ['id' => $t->id, 'title' => $t->title ?? ''])->values()->all());
            var contactDebounce, leadDebounce;

            function showResults(el, show) { el.classList.toggle('hidden', !show); }
            function showChosen(el, text, show) { el.innerHTML = text; el.classList.toggle('hidden', !show); }

            document.getElementById('btn-generic-link').addEventListener('click', function(){
                contactId.value = ''; leadId.value = ''; taskId.value = '';
                contactSearch.value = ''; leadSearch.value = ''; taskSearch.value = '';
                showChosen(contactChosen, '', false); showChosen(leadChosen, '', false); showChosen(taskChosen, '', false);
                showResults(contactResults, false); showResults(leadResults, false); showResults(taskResults, false);
            });

            function bindSearch(input, hiddenId, resultsEl, chosenEl, fetchUrl, getLabel) {
                if (!input) return;
                input.addEventListener('input', function(){
                    var q = (input.value || '').trim();
                    if (q.length < 1) { showResults(resultsEl, false); return; }
                    if (fetchUrl) {
                        var isContact = fetchUrl.indexOf('contacts') !== -1;
                        clearTimeout(isContact ? contactDebounce : leadDebounce);
                        var debounce = setTimeout(function(){
                            fetch(fetchUrl + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                                .then(function(r){ return r.json(); })
                                .then(function(list){
                                    resultsEl.innerHTML = '';
                                    if (!list || list.length === 0) { resultsEl.innerHTML = '<span class="empty">نتیجه‌ای یافت نشد</span>'; }
                                    else list.forEach(function(item){
                                        var label = getLabel(item);
                                        var btn = document.createElement('button');
                                        btn.type = 'button';
                                        btn.textContent = label;
                                        btn.addEventListener('click', function(e){ e.preventDefault(); hiddenId.value = item.id; input.value = ''; showChosen(chosenEl, label + ' <button type="button">حذف</button>', true); showResults(resultsEl, false); bindChosenRemove(chosenEl, hiddenId, input); });
                                        resultsEl.appendChild(btn);
                                    });
                                    showResults(resultsEl, true);
                                });
                        }, 220);
                        if (isContact) contactDebounce = debounce; else leadDebounce = debounce;
                    } else {
                        var lower = q.toLowerCase();
                        var list = tasksData.filter(function(t){ return (t.title || '').toLowerCase().indexOf(lower) !== -1; }).slice(0, 20);
                        resultsEl.innerHTML = '';
                        if (list.length === 0) resultsEl.innerHTML = '<span class="empty">نتیجه‌ای یافت نشد</span>';
                        else list.forEach(function(t){
                            var btn = document.createElement('button');
                            btn.type = 'button';
                            btn.textContent = (t.title || 'وظیفه #' + t.id).substring(0, 50);
                            btn.addEventListener('click', function(e){ e.preventDefault(); taskId.value = t.id; taskSearch.value = ''; showChosen(taskChosen, btn.textContent + ' <button type="button">حذف</button>', true); showResults(taskResults, false); bindChosenRemove(taskChosen, taskId, taskSearch); });
                            resultsEl.appendChild(btn);
                        });
                        showResults(resultsEl, true);
                    }
                });
                input.addEventListener('blur', function(){ setTimeout(function(){ showResults(resultsEl, false); }, 180); });
            }
            function bindChosenRemove(chosenEl, hiddenInput, searchInput) {
                var rm = chosenEl.querySelector('button');
                if (rm) rm.addEventListener('click', function(e){ e.preventDefault(); hiddenInput.value = ''; chosenEl.classList.add('hidden'); chosenEl.innerHTML = ''; searchInput.value = ''; });
            }
            bindSearch(contactSearch, contactId, contactResults, contactChosen, '{{ route("contacts.search.api") }}?q=', function(c){ return c.name; });
            bindSearch(leadSearch, leadId, leadResults, leadChosen, '{{ route("tasks.search-taskable.api") }}?type=lead&q=', function(l){ return l.name || 'سرنخ #' + l.id; });
            bindSearch(taskSearch, taskId, taskResults, taskChosen, null, null);
            document.querySelectorAll('.link-results').forEach(function(el){ el.classList.add('hidden'); });
            document.getElementById('form-link-create-form').addEventListener('submit', function(){
                [contactId, leadId, taskId].forEach(function(input){ if (input && (input.value === '' || input.value == null)) input.removeAttribute('name'); });
            });
        })();
        </script>
    @else
        <p style="margin-bottom: 1.5rem; color: var(--ds-text-subtle);">برای ساخت لینک، وضعیت فرم را در <a href="{{ route('forms.edit', $form) }}">ویرایش فرم</a> روی «فعال» بگذارید.</p>
    @endif

    {{-- Submissions --}}
    <div class="ds-form-card">
        <h2 class="ds-form-card-title">ارسال‌های این فرم</h2>
        @if ($submissions->isEmpty())
            <p style="color: var(--ds-text-subtle);">هنوز ارسالی ثبت نشده.</p>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($submissions as $sub)
                    <a href="{{ route('forms.submissions.show', $sub) }}" class="ds-card" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 0.75rem;">
                        <div>
                            <span style="font-weight: 500;">ارسال #{{ $sub->id }}</span>
                            <span style="font-size: 0.8125rem; color: var(--ds-text-subtle); margin-right: 0.5rem;">
                                {{ $sub->submitted_at ? $sub->submitted_at->format('Y/m/d H:i') : 'پیش‌نویس' }}
                            </span>
                            @if($sub->contact)
                                <span style="font-size: 0.8125rem;">— {{ $sub->contact->name }}</span>
                            @endif
                        </div>
                        @include('components._icons', ['name' => 'chevron-right', 'class' => 'w-4 h-4 text-stone-400'])
                    </a>
                @endforeach
            </div>
            <div style="margin-top: 1rem;">{{ $submissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
