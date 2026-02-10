@extends('layouts.app')

@section('title', $form->title . ' — ' . config('app.name'))

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
        <div class="ds-form-card" style="margin-bottom: 1.5rem;">
            <h2 class="ds-form-card-title">ساخت لینک جدید</h2>
            <p style="font-size: 0.875rem; color: var(--ds-text-subtle); margin-bottom: 1rem;">هر لینک برای یک ارسال است. اختیاری: لینک را به مخاطب، سرنخ یا وظیفه وصل کنید.</p>
            <form action="{{ route('forms.links.store', $form) }}" method="post" style="display: flex; flex-direction: column; gap: 1rem;">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div>
                        <label for="link_contact_id" class="ds-label">مخاطب (اختیاری)</label>
                        <select name="contact_id" id="link_contact_id" class="ds-select">
                            <option value="">— انتخاب نکنید —</option>
                            @foreach($contacts ?? [] as $c)
                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="link_lead_id" class="ds-label">سرنخ (اختیاری)</label>
                        <select name="lead_id" id="link_lead_id" class="ds-select">
                            <option value="">— انتخاب نکنید —</option>
                            @foreach($leads ?? [] as $l)
                                <option value="{{ $l->id }}">{{ $l->name ?? 'سرنخ #' . $l->id }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="link_task_id" class="ds-label">وظیفه (اختیاری)</label>
                        <select name="task_id" id="link_task_id" class="ds-select">
                            <option value="">— انتخاب نکنید —</option>
                            @foreach($tasks ?? [] as $t)
                                <option value="{{ $t->id }}">{{ \Str::limit($t->title, 40) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="ds-btn ds-btn-primary" style="align-self: flex-start;">ساخت لینک جدید</button>
            </form>
        </div>
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
