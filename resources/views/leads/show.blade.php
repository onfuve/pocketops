@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', ($lead->name ?? 'بدون نام') . ' — سرنخ — ' . config('app.name'))

@push('styles')
<style>
.lead-show .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 2px solid transparent; cursor: pointer; transition: all 0.2s; font-family: 'Vazirmatn', sans-serif; }
.lead-show .btn-primary, .lead-show button.btn-primary { background: #059669; color: #fff !important; border-color: #047857; }
.lead-show .btn-primary:hover, .lead-show button.btn-primary:hover { background: #047857 !important; }
.lead-show .btn-secondary, .lead-show a.btn-secondary { background: #fff; color: #44403c; border-color: #d6d3d1; }
.lead-show .btn-secondary:hover, .lead-show a.btn-secondary:hover { background: #fafaf9; border-color: #a8a29e; }
.lead-show .btn-danger, .lead-show button.btn-danger { background: #fff; color: #b91c1c; border-color: #fecaca; }
.lead-show .btn-danger:hover, .lead-show button.btn-danger:hover { background: #fef2f2; border-color: #f87171; }
.lead-show .card-flat { background: #fff; border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
.lead-show .page-title { font-size: 1.5rem; font-weight: 700; color: #292524; }
.lead-show .page-subtitle { font-size: 0.9375rem; color: #78716c; }
.lead-show .toolbar { display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center; }
.lead-show .tag-pill { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.25rem 0.5rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; cursor: pointer; border: 1px solid transparent; transition: opacity 0.2s; }
.lead-show .tag-pill:hover { opacity: 0.9; }
.lead-show .tag-picker { max-height: 12rem; overflow-y: auto; padding: 0.5rem; border: 1px solid #e7e5e4; border-radius: 0.5rem; background: #fff; }
.lead-show .tag-picker-row { padding: 0.375rem 0.5rem; border-radius: 0.375rem; display: flex; align-items: center; gap: 0.5rem; }
.lead-show .tag-picker-row:hover { background: #f5f5f4; }
.lead-show .timeline-item { padding-bottom: 1rem; padding-right: 1.25rem; border-right: 2px solid #e7e5e4; margin-right: 0.75rem; position: relative; }
.lead-show .timeline-item:last-child { border-right: none; padding-bottom: 0; }
.lead-show .cards-grid-wide { display: grid; grid-template-columns: 1fr; gap: 1.5rem; align-items: stretch; }
@media (min-width: 768px) {
  .lead-show .tags-card-half { max-width: none; }
  .lead-show .cards-grid-wide { grid-template-columns: 1fr 1fr; }
  .lead-show .cards-grid-wide .span-full { grid-column: 1 / -1; }
  .lead-show .cards-grid-wide .card-flat { min-height: 100%; }
}
</style>
@endpush

@section('content')
<div class="lead-show" style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; font-family: 'Vazirmatn', sans-serif;">
    {{-- Header: title + primary actions --}}
    <div style="margin-bottom: 1.5rem;">
        <div style="display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 1rem;">
            <div>
                <h1 class="page-title break-words flex items-center gap-2" style="margin: 0 0 0.25rem 0;">
                    <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; background: #fef3c7; color: #b45309;">
                        @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                    </span>
                    {{ $lead->name ?? 'بدون نام' }}
                </h1>
                @if ($lead->company)
                    <p class="page-subtitle" style="margin: 0;">{{ $lead->company }}</p>
                @endif
            </div>
            <div class="toolbar">
                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-secondary">@include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4']) ویرایش</a>
                @if (!$lead->contact_id)
                    @if (isset($existingContacts) && $existingContacts->isNotEmpty())
                        @foreach ($existingContacts as $existing)
                            <a href="{{ route('contacts.show', $existing) }}" class="btn" style="background: #fef3c7; color: #92400e; border-color: #fcd34d;">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) مخاطب مشابه: {{ $existing->name }}</a>
                        @endforeach
                    @else
                        <form action="{{ route('leads.convert-to-contact', $lead) }}" method="post" style="display: inline;" onsubmit="return confirm('این سرنخ به مخاطب تبدیل شود؟');">
                            @csrf
                            <button type="submit" class="btn btn-primary">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) تبدیل به مخاطب</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('contacts.show', $lead->contact) }}" class="btn" style="background: #d1fae5; color: #065f46; border-color: #a7f3d0;">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) مشاهده مخاطب</a>
                @endif
                <a href="{{ route('leads.create-invoice', $lead) }}" class="btn" style="background: #dbeafe; color: #1e40af; border-color: #93c5fd;">@include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4']) فاکتور پیش‌نویس</a>
                @if (auth()->user()->canDeleteLead())
                    <form action="{{ route('leads.destroy', $lead) }}" method="post" style="display: inline;" onsubmit="return confirm('سرنخ حذف شود؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">حذف</button>
                    </form>
                @endif
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4']) لیست</a>
            </div>
        </div>
    </div>

    {{-- Status + Pipeline --}}
    <div class="card-flat" style="margin-bottom: 1.5rem;">
        <div style="display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; margin-bottom: 1rem;">
            <span style="font-size: 0.8125rem; font-weight: 600; color: #78716c; text-transform: uppercase; letter-spacing: 0.05em;">مرحله فعلی</span>
            <span style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 1rem; border-radius: 0.5rem; background: {{ Lead::statusTextColor($lead->status) }}; color: #fff; font-weight: 600; font-size: 0.9375rem;">
                {{ Lead::statusLabels()[$lead->status] }}
            </span>
        </div>
        <p style="font-size: 0.8125rem; color: #78716c; margin: 0 0 0.75rem 0;">برای تغییر مرحله کلیک کنید</p>
        <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
            @foreach (Lead::pipelineStatuses() as $idx => $st)
                @php $isCurrent = $lead->status === $st; $textColor = Lead::statusTextColor($st); @endphp
                <a href="{{ route('leads.change-status', ['lead' => $lead, 'status' => $st]) }}"
                   style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; border-radius: 0.5rem; border: 2px solid {{ $isCurrent ? $textColor : '#e7e5e4' }}; background: {{ $isCurrent ? $textColor : '#fff' }}; color: {{ $isCurrent ? '#fff' : $textColor }}; font-size: 0.8125rem; font-weight: {{ $isCurrent ? '600' : '500' }}; text-decoration: none; transition: all 0.2s;"
                   onmouseover="if (!{{ $isCurrent ? 'true' : 'false' }}) { this.style.borderColor='{{ $textColor }}'; this.style.background='{{ Lead::statusBgColor($st) }}'; }"
                   onmouseout="if (!{{ $isCurrent ? 'true' : 'false' }}) { this.style.borderColor='#e7e5e4'; this.style.background='#fff'; }">
                    {{ Lead::statusLabels()[$st] }}
                </a>
                @if ($idx < count(Lead::pipelineStatuses()) - 1)
                    <span style="color: #d6d3d1; font-size: 0.75rem;">→</span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Quick actions: Share + Calendar, Assign --}}
    @php $canAssign = $lead->user_id === auth()->id() || auth()->user()->isAdmin() || $lead->assigned_to_id === auth()->id(); $shareUrl = route('leads.show', $lead); $shareText = 'سرنخ: ' . ($lead->name ?? 'بدون نام') . ($lead->company ? ' — ' . $lead->company : ''); @endphp
    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); margin-bottom: 1.5rem;">
        <div style="padding: 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff;">
            <h3 style="font-size: 0.875rem; font-weight: 600; color: #44403c; margin: 0 0 0.75rem 0;">اشتراک و تقویم</h3>
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span style="font-size: 0.8125rem; color: #78716c;">اشتراک:</span>
                    <a href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="btn" style="background: #22c55e; color: #fff !important; border-color: #16a34a; padding: 0.375rem 0.75rem; font-size: 0.8125rem;">واتساپ</a>
                    <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="btn" style="background: #0ea5e9; color: #fff !important; border-color: #0284c7; padding: 0.375rem 0.75rem; font-size: 0.8125rem;">تلگرام</a>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <span style="font-size: 0.8125rem; color: #78716c;">تقویم:</span>
                    <form action="{{ route('calendar.leads.task', $lead) }}" method="post" style="display: flex; gap: 0.5rem; align-items: center;">
                        @csrf
                        <input type="text" name="due_date" value="{{ FormatHelper::shamsi($lead->lead_date ?? now()) }}" placeholder="۱۴۰۳/۱۱/۱۵" required style="width: 7rem; padding: 0.4rem 0.6rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 0.8125rem;">
                        <button type="submit" class="btn" style="background: #0ea5e9; color: #fff !important; border-color: #0284c7; padding: 0.4rem 0.75rem; font-size: 0.8125rem;">افزودن</button>
                    </form>
                </div>
            </div>
        </div>
        @if ($canAssign)
            <div style="padding: 1rem; border-radius: 0.75rem; border: 2px solid #e0f2fe; background: #f0f9ff;">
                <h3 style="font-size: 0.875rem; font-weight: 600; color: #0369a1; margin: 0 0 0.5rem 0;">واگذاری به عضو تیم</h3>
                @if ($lead->assignedTo)
                    <p style="font-size: 0.8125rem; color: #0369a1; margin: 0 0 0.5rem 0;">{{ $lead->assignedTo->name }}</p>
                @endif
                <form action="{{ route('leads.assign', $lead) }}" method="post" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    @csrf
                    <select name="assigned_to_id" style="padding: 0.4rem 0.6rem; border: 2px solid #bae6fd; border-radius: 0.5rem; font-size: 0.8125rem; min-width: 10rem;">
                        <option value="">— واگذار نشده —</option>
                        @foreach ($users ?? [] as $u)
                            <option value="{{ $u->id }}" {{ $lead->assigned_to_id == $u->id ? 'selected' : '' }}>{{ $u->name }}{{ $u->id === auth()->id() ? ' (من)' : '' }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn" style="background: #0284c7; color: #fff !important; border-color: #0369a1; padding: 0.4rem 0.75rem; font-size: 0.8125rem;">واگذار</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Tags + اطلاعات: side by side on wide --}}
    <div class="cards-grid-wide" style="margin-bottom: 1.5rem;">
    {{-- Tags section --}}
    <div class="card-flat tags-card-half" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'tag', 'class' => 'w-4 h-4'])
            برچسب‌ها
        </h2>
        @if ($lead->tags->isNotEmpty())
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-bottom: 1rem;">
                @foreach ($lead->tags as $tag)
                    <a href="{{ route('tags.show', $tag) }}" class="tag-pill" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border-color: {{ $tag->color }}40; text-decoration: none;">
                        <span style="width: 0.5rem; height: 0.5rem; border-radius: 50%; background: {{ $tag->color }};"></span>
                        {{ $tag->name }}
                    </a>
                @endforeach
            </div>
        @endif
        @isset($tags)
        <form action="{{ route('leads.tags.update', $lead) }}" method="post">
            @csrf
            @include('components._tag-section', ['tags' => $tags, 'entity' => $lead, 'accentColor' => '#059669', 'embedded' => true])
            @if ($tags->isNotEmpty())
                <button type="submit" class="btn btn-primary" style="margin-top: 0.75rem; padding: 0.375rem 0.75rem; font-size: 0.8125rem;">ذخیره برچسب‌ها</button>
            @endif
        </form>
        @endisset
    </div>

    {{-- اطلاعات card --}}
    <div class="card-flat" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
            اطلاعات
        </h2>
        <dl style="display: grid; gap: 0.75rem 2rem; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); font-size: 0.9375rem;">
            @if ($lead->phone)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">تلفن</dt><dd style="margin: 0;" dir="ltr"><a href="tel:{{ $lead->phone }}" style="color: #047857; font-weight: 500;">{{ $lead->phone }}</a></dd></div>
            @endif
            @if ($lead->email)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">ایمیل</dt><dd style="margin: 0;" dir="ltr"><a href="mailto:{{ $lead->email }}" style="color: #44403c;">{{ $lead->email }}</a></dd></div>
            @endif
            @if ($lead->leadChannel)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">کانال ورود</dt><dd style="margin: 0;">{{ $lead->leadChannel->name }}</dd></div>
            @endif
            @if ($lead->referrerContact)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">معرف</dt><dd style="margin: 0;"><a href="{{ route('contacts.show', $lead->referrerContact) }}" style="color: #047857;">{{ $lead->referrerContact->name }}</a></dd></div>
            @endif
            @if ($lead->source)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">منبع</dt><dd style="margin: 0;">{{ $lead->source }}</dd></div>
            @endif
            @if ($lead->lead_date)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">تاریخ</dt><dd style="margin: 0;">{{ FormatHelper::shamsi($lead->lead_date) }}</dd></div>
            @endif
            @if ($lead->value !== null && $lead->value > 0)
                <div><dt style="font-size: 0.75rem; font-weight: 600; color: #78716c; margin-bottom: 0.25rem;">ارزش</dt><dd style="margin: 0;">{{ FormatHelper::rial((int) $lead->value) }}</dd></div>
            @endif
        </dl>
        @if (!($lead->phone || $lead->email || $lead->leadChannel || $lead->referrerContact || $lead->source || $lead->lead_date || ($lead->value && $lead->value > 0) || $lead->details))
            <p style="margin: 0; font-size: 0.875rem; color: #a8a29e;">جزئیات ثبت نشده.</p>
        @endif
    </div>
    </div>{{-- /cards-grid-wide --}}

    {{-- جزئیات: full-width card --}}
    @if ($lead->details)
    <div class="card-flat" style="margin-bottom: 1.5rem; width: 100%; box-sizing: border-box;">
        <h2 style="font-size: 1rem; font-weight: 700; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
            جزئیات
        </h2>
        <div style="padding: 1.25rem; background: #fafaf9; border-radius: 0.75rem; border: 2px solid #e7e5e4; border-right: 4px solid #059669; white-space: pre-wrap; font-size: 1rem; line-height: 1.7; color: #292524;">{{ $lead->details }}</div>
    </div>
    @endif

    {{-- History + Tasks: side by side on wide --}}
    <div class="cards-grid-wide" style="margin-bottom: 1.5rem;">
    {{-- History + Comments timeline --}}
    <div class="card-flat" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
            تاریخچه و نظرات
        </h2>

        {{-- Add comment form --}}
        <form action="{{ route('leads.comments.store', $lead) }}" method="post" style="margin-bottom: 1.5rem;">
            @csrf
            <textarea name="body" rows="3" placeholder="افزودن نظر یا رویداد…" required style="width: 100%; padding: 0.625rem 0.75rem; border: 2px solid #e7e5e4; border-radius: 0.5rem; font-size: 0.9375rem; resize: vertical; box-sizing: border-box;"></textarea>
            <button type="submit" class="btn btn-primary" style="margin-top: 0.5rem; padding: 0.5rem 1rem; font-size: 0.875rem;">ثبت نظر</button>
        </form>

        @php
            $timeline = collect();
            foreach ($lead->activities as $act) {
                $timeline->push(['type' => 'activity', 'item' => $act, 'date' => $act->created_at->format('Y-m-d H:i')]);
            }
            foreach ($lead->comments as $c) {
                $timeline->push(['type' => 'comment', 'item' => $c, 'date' => $c->created_at->format('Y-m-d H:i')]);
            }
            $timeline = $timeline->sortByDesc('date')->values();
        @endphp

        @if ($timeline->isEmpty())
            <p style="font-size: 0.875rem; color: #a8a29e; margin: 0;">هنوز فعالیت یا نظری ثبت نشده.</p>
        @else
            <div style="position: relative;">
                @foreach ($timeline as $t)
                    <div class="timeline-item">
                        @if ($t['type'] === 'activity')
                            @php $act = $t['item']; $toColor = Lead::statusTextColor($act->to_status); $toBg = Lead::statusBgColor($act->to_status); @endphp
                            <div style="position: absolute; right: -0.5rem; top: 0.25rem; width: 0.875rem; height: 0.875rem; border-radius: 50%; background: {{ $toColor }}; border: 2px solid #fff; box-shadow: 0 0 0 1px {{ $toColor }}40;"></div>
                            <div style="padding-right: 1.25rem;">
                                <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    @if ($act->from_status)
                                        <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; background: {{ Lead::statusBgColor($act->from_status) }}; color: {{ Lead::statusTextColor($act->from_status) }};">{{ Lead::statusLabels()[$act->from_status] ?? $act->from_status }}</span>
                                    @else
                                        <span style="color: #a8a29e; font-size: 0.75rem;">شروع</span>
                                    @endif
                                    <span style="color: #d6d3d1;">→</span>
                                    <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; background: {{ $toBg }}; color: {{ $toColor }}; font-weight: 600;">{{ Lead::statusLabels()[$act->to_status] ?? $act->to_status }}</span>
                                    <span style="font-size: 0.75rem; color: #78716c;">{{ FormatHelper::shamsi($act->activity_date) }}</span>
                                </div>
                                @if ($act->comment)
                                    <div style="margin-top: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.375rem; background: #fafaf9; border-right: 3px solid {{ $toColor }}40; font-size: 0.875rem; color: #57534e; white-space: pre-wrap;">{{ $act->comment }}</div>
                                @endif
                            </div>
                        @else
                            @php $c = $t['item']; @endphp
                            <div style="position: absolute; right: -0.5rem; top: 0.25rem; width: 0.875rem; height: 0.875rem; border-radius: 50%; background: #6366f1; border: 2px solid #fff; box-shadow: 0 0 0 1px #a5b4fc;"></div>
                            <div style="padding-right: 1.25rem;">
                                <div style="font-size: 0.75rem; color: #78716c; margin-bottom: 0.25rem;">{{ $c->user?->name ?? 'ناشناس' }} — {{ FormatHelper::shamsi($c->created_at) }} {{ $c->created_at->format('H:i') }}</div>
                                <div style="font-size: 0.9375rem; color: #44403c; white-space: pre-wrap;">{{ $c->body }}</div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Tasks --}}
    <div class="card-flat" style="margin-bottom: 0;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem;">
            <span style="display: flex; align-items: center; gap: 0.5rem;">@include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4']) وظایف</span>
            <a href="{{ route('tasks.create', ['taskable_type' => 'lead', 'taskable_id' => $lead->id]) }}" style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #0369a1; color: #fff; text-decoration: none;">افزودن وظیفه</a>
        </h2>
        @if ($lead->tasks->isEmpty())
            <p style="font-size: 0.875rem; color: #78716c; margin: 0;"><a href="{{ route('tasks.create', ['taskable_type' => 'lead', 'taskable_id' => $lead->id]) }}" style="color: #0369a1;">افزودن وظیفه</a></p>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($lead->tasks as $t)
                    <a href="{{ route('tasks.show', $t) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 0.75rem; border-radius: 0.5rem; background: #fafaf9; border: 1px solid #e7e5e4; text-decoration: none; color: #292524;">
                        <span style="font-weight: 600; font-size: 0.9375rem;">{{ $t->title }}</span>
                        <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; background: {{ \App\Models\Task::statusColors()[$t->status] }}20; color: {{ \App\Models\Task::statusColors()[$t->status] }};">{{ \App\Models\Task::statusLabels()[$t->status] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>
    </div>{{-- /cards-grid-wide --}}

    {{-- Attachments --}}
    <div class="card-flat" style="margin-bottom: 1.5rem;">
        <h2 style="font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.75rem 0;">تصاویر و پیوست‌ها</h2>
        <form action="{{ route('leads.attachments.store', $lead) }}" method="post" enctype="multipart/form-data" style="margin-bottom: 1rem;">
            @csrf
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: flex-end;">
                <input type="file" name="file" accept="image/*,.pdf" required style="font-size: 0.875rem;">
                <button type="submit" class="btn btn-primary" style="padding: 0.375rem 0.75rem; font-size: 0.8125rem;">افزودن</button>
            </div>
        </form>
        @if ($lead->attachments->isEmpty())
            <p style="font-size: 0.875rem; color: #a8a29e; margin: 0;">پیوستی ثبت نشده.</p>
        @else
            <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));">
                @foreach ($lead->attachments as $att)
                    <div style="padding: 0.5rem; border-radius: 0.5rem; background: #fafaf9; border: 1px solid #e7e5e4;">
                        @if ($att->isImage())
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" style="display: block; aspect-ratio: 4/3; border-radius: 0.375rem; overflow: hidden; background: #e7e5e4; margin-bottom: 0.5rem;">
                                <img src="{{ $att->url() }}" alt="{{ $att->original_name }}" style="width: 100%; height: 100%; object-fit: cover;">
                            </a>
                        @else
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" style="display: block; aspect-ratio: 4/3; border-radius: 0.375rem; background: #e7e5e4; margin-bottom: 0.5rem; display: flex; align-items: center; justify-content: center; color: #78716c; font-size: 0.75rem;">PDF</a>
                        @endif
                        <p style="font-size: 0.75rem; color: #57534e; margin: 0 0 0.25rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $att->original_name }}</p>
                        <form action="{{ route('leads.attachments.destroy', [$lead, $att]) }}" method="post" onsubmit="return confirm('حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer;">حذف</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var search = document.getElementById('tagSearch');
    var rows = document.querySelectorAll('.tag-row');
    if (search && rows.length) {
        search.addEventListener('input', function() {
            var q = this.value.trim().toLowerCase();
            rows.forEach(function(r) {
                var name = (r.getAttribute('data-name') || '').toLowerCase();
                r.style.display = q === '' || name.indexOf(q) !== -1 ? '' : 'none';
            });
        });
    }
});
</script>
@endpush
@endsection
