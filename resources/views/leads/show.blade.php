@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', ($lead->name ?? 'بدون نام') . ' — سرنخ — ' . config('app.name'))

@push('styles')
<style>
.lead-show { font-family: 'Vazirmatn', sans-serif; }
.lead-show .btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 2px solid transparent; cursor: pointer; transition: all 0.2s; }
.lead-show .btn-primary, .lead-show button.btn-primary { background: linear-gradient(135deg, #059669, #047857); color: #fff !important; border-color: #047857; box-shadow: 0 2px 8px rgba(5, 150, 105, 0.2); }
.lead-show .btn-primary:hover, .lead-show button.btn-primary:hover { background: linear-gradient(135deg, #047857, #065f46) !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3) !important; }
.lead-show .btn-secondary, .lead-show a.btn-secondary { background: #fff; color: #44403c; border-color: #d6d3d1; }
.lead-show .btn-secondary:hover, .lead-show a.btn-secondary:hover { background: #fafaf9; border-color: #a8a29e; }
.lead-show .btn-danger, .lead-show button.btn-danger { background: #fff; color: #b91c1c; border-color: #fecaca; }
.lead-show .btn-danger:hover, .lead-show button.btn-danger:hover { background: #fef2f2; border-color: #f87171; }
.lead-show .card-modern { background: #fff; border: 1px solid #e7e5e4; border-radius: 1.25rem; padding: 1.75rem; margin-bottom: 1.5rem; box-shadow: 0 2px 8px rgba(0,0,0,0.04); transition: all 0.2s; }
.lead-show .card-modern:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }
.lead-show .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem; padding-bottom: 1rem; border-bottom: 2px solid #f5f5f4; }
.lead-show .card-title { font-size: 1.125rem; font-weight: 700; color: #292524; margin: 0; display: flex; align-items: center; gap: 0.625rem; }
.lead-show .card-title-icon { display: flex; align-items: center; justify-content: center; width: 2.25rem; height: 2.25rem; border-radius: 0.625rem; }
.lead-show .page-header { background: linear-gradient(135deg, #fffbeb 0%, #fef3c7 50%, #fff 100%); border-radius: 1.5rem; padding: 2rem; margin-bottom: 2rem; border: 1px solid #fde68a; box-shadow: 0 4px 16px rgba(245, 158, 11, 0.1); }
.lead-show .header-top-row { display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem; }
.lead-show .header-icon { display: flex; align-items: center; justify-content: center; width: 4rem; height: 4rem; border-radius: 1.25rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: #fff; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3); flex-shrink: 0; }
.lead-show .header-name-section { flex: 1; min-width: 0; }
.lead-show .header-name { font-size: 1.5rem; font-weight: 700; color: #292524; margin: 0; line-height: 1.3; word-break: break-word; overflow-wrap: break-word; }
.lead-show .header-company { font-size: 0.9375rem; color: #78716c; margin: 0.5rem 0 0 0; line-height: 1.4; word-break: break-word; overflow-wrap: break-word; }
.lead-show .header-contact-row { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.5rem; padding-top: 1rem; border-top: 1px solid rgba(245, 158, 11, 0.2); }
.lead-show .header-contact-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; background: #fff; border: 2px solid #d6d3d1; border-radius: 0.75rem; text-decoration: none; font-weight: 600; font-size: 0.875rem; transition: all 0.2s; }
.lead-show .header-contact-btn:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.lead-show .header-contact-btn.phone { color: #047857; border-color: #a7f3d0; }
.lead-show .header-contact-btn.phone:hover { background: #f0fdf4; border-color: #047857; }
.lead-show .header-contact-btn.email { color: #1e40af; border-color: #bfdbfe; }
.lead-show .header-contact-btn.email:hover { background: #eff6ff; border-color: #1e40af; }
.lead-show .header-actions-row { display: flex; flex-wrap: wrap; gap: 0.75rem; padding-top: 1rem; border-top: 1px solid rgba(245, 158, 11, 0.2); }
.lead-show .toolbar { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; width: 100%; }
@media (min-width: 768px) {
  .lead-show .header-name { font-size: 1.75rem; }
  .lead-show .header-company { font-size: 1rem; }
}
@media (min-width: 1024px) {
  .lead-show .page-header { padding: 2.5rem; }
  .lead-show .header-name { font-size: 2rem; }
}
.lead-show .tag-pill { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.875rem; border-radius: 0.75rem; font-size: 0.8125rem; font-weight: 600; cursor: pointer; border: 1px solid transparent; transition: all 0.2s; text-decoration: none; }
.lead-show .tag-pill:hover { transform: translateY(-1px); box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.lead-show .tag-picker { max-height: 12rem; overflow-y: auto; padding: 0.75rem; border: 1px solid #e7e5e4; border-radius: 0.75rem; background: #fff; }
.lead-show .tag-picker-row { padding: 0.5rem 0.75rem; border-radius: 0.5rem; display: flex; align-items: center; gap: 0.5rem; transition: background 0.15s; }
.lead-show .tag-picker-row:hover { background: #f5f5f4; }
.lead-show .timeline-item { padding-bottom: 1.5rem; padding-right: 1.5rem; border-right: 3px solid #e7e5e4; margin-right: 0.875rem; position: relative; }
.lead-show .timeline-item:last-child { border-right: none; padding-bottom: 0; }
.lead-show .timeline-dot { position: absolute; right: -0.625rem; top: 0.25rem; width: 1.125rem; height: 1.125rem; border-radius: 50%; border: 3px solid #fff; box-shadow: 0 0 0 2px currentColor; }
.lead-show .info-grid { display: grid; gap: 1.25rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); }
.lead-show .info-item { padding: 0.875rem 1rem; background: #fafaf9; border-radius: 0.75rem; border: 1px solid #e7e5e4; }
.lead-show .info-label { font-size: 0.75rem; font-weight: 700; color: #78716c; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
.lead-show .info-value { font-size: 0.9375rem; font-weight: 600; color: #292524; margin: 0; }
.lead-show .info-value a { color: #047857; text-decoration: none; transition: color 0.2s; }
.lead-show .info-value a:hover { color: #059669; }
.lead-show .status-badge { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.75rem; font-weight: 700; font-size: 0.9375rem; }
.lead-show .pipeline-nav { display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; }
.lead-show .pipeline-step { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid; font-size: 0.875rem; font-weight: 600; text-decoration: none; transition: all 0.2s; }
.lead-show .pipeline-step:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.lead-show .call-form-box { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 50%, #fff 100%); border: 2px solid #3b82f6; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; }
.lead-show .comment-form-box { background: #fafaf9; border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.25rem; margin-bottom: 1.5rem; }
.lead-show .form-input { width: 100%; padding: 0.75rem 1rem; border: 2px solid #d6d3d1; border-radius: 0.75rem; font-size: 0.9375rem; transition: all 0.2s; box-sizing: border-box; }
.lead-show .form-input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.lead-show .form-textarea { width: 100%; padding: 0.75rem 1rem; border: 2px solid #d6d3d1; border-radius: 0.75rem; font-size: 0.9375rem; resize: vertical; transition: all 0.2s; box-sizing: border-box; min-height: 100px; }
.lead-show .form-textarea:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }
.lead-show .cards-grid { display: grid; grid-template-columns: 1fr; gap: 1.5rem; }
@media (min-width: 768px) {
  .lead-show .cards-grid { grid-template-columns: 1fr 1fr; }
  .lead-show .cards-grid .span-full { grid-column: 1 / -1; }
}
.lead-show .tasks-section { margin-top: 1.5rem; }
.lead-show .tasks-grid { display: grid; grid-template-columns: 1fr; gap: 0.75rem; }
@media (min-width: 768px) {
  .lead-show .tasks-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (min-width: 1024px) {
  .lead-show .tasks-grid { grid-template-columns: repeat(3, 1fr); }
}
@media (min-width: 1024px) {
  .lead-show .page-header { padding: 2.5rem; }
}
</style>
@endpush

@section('content')
<div class="lead-show" style="max-width: 64rem; margin: 0 auto; padding: 0 1rem 2rem;">
    {{-- Redesigned Header --}}
    <div class="page-header">
        {{-- Top Row: Icon + Name + Company --}}
        <div class="header-top-row">
            <div class="header-icon">
                @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-7 h-7'])
            </div>
            <div class="header-name-section">
                <h1 class="header-name">{{ $lead->name ?? 'بدون نام' }}</h1>
                @if ($lead->company)
                    <p class="header-company">{{ $lead->company }}</p>
                @endif
            </div>
        </div>

        {{-- Contact Info Row --}}
        @if ($lead->phone || $lead->email)
            <div class="header-contact-row">
                @if ($lead->phone)
                    <a href="tel:{{ $lead->phone }}" class="header-contact-btn phone">
                        @include('components._icons', ['name' => 'phone', 'class' => 'w-4 h-4'])
                        <span dir="ltr">{{ $lead->phone }}</span>
                    </a>
                @endif
                @if ($lead->email)
                    <a href="mailto:{{ $lead->email }}" class="header-contact-btn email">
                        @include('components._icons', ['name' => 'mail', 'class' => 'w-4 h-4'])
                        <span dir="ltr">{{ $lead->email }}</span>
                    </a>
                @endif
            </div>
        @endif

        {{-- Actions Row --}}
        <div class="header-actions-row">
            <div class="toolbar">
                <a href="{{ route('leads.edit', $lead) }}" class="btn btn-secondary">@include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4']) ویرایش</a>
                @if (!$lead->contact_id)
                    @if (isset($existingContacts) && $existingContacts->isNotEmpty())
                        @foreach ($existingContacts as $existing)
                            <a href="{{ route('contacts.show', $existing) }}" class="btn" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e; border-color: #fcd34d;">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) مخاطب مشابه</a>
                        @endforeach
                    @else
                        <form action="{{ route('leads.convert-to-contact', $lead) }}" method="post" style="display: inline;" onsubmit="return confirm('این سرنخ به مخاطب تبدیل شود؟');">
                            @csrf
                            <button type="submit" class="btn btn-primary">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) تبدیل به مخاطب</button>
                        </form>
                    @endif
                @else
                    <a href="{{ route('contacts.show', $lead->contact) }}" class="btn" style="background: linear-gradient(135deg, #d1fae5, #a7f3d0); color: #065f46; border-color: #6ee7b7;">@include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4']) مشاهده مخاطب</a>
                @endif
                <a href="{{ route('leads.create-invoice', $lead) }}" class="btn" style="background: linear-gradient(135deg, #dbeafe, #bfdbfe); color: #1e40af; border-color: #93c5fd;">@include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4']) فاکتور</a>
                @if (auth()->user()->canDeleteLead())
                    <form action="{{ route('leads.destroy', $lead) }}" method="post" style="display: inline;" onsubmit="return confirm('سرنخ حذف شود؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">@include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4']) حذف</button>
                    </form>
                @endif
                <a href="{{ route('leads.index') }}" class="btn btn-secondary">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4']) لیست</a>
            </div>
        </div>
    </div>

    {{-- Status Pipeline --}}
    <div class="card-modern">
        <div class="card-header">
            <h2 class="card-title">
                <span class="card-title-icon" style="background: {{ Lead::statusBgColor($lead->status) }}; color: {{ Lead::statusTextColor($lead->status) }};">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-5 h-5'])
                </span>
                مرحله سرنخ
            </h2>
            <span class="status-badge" style="background: {{ Lead::statusTextColor($lead->status) }}; color: #fff;">
                {{ Lead::statusLabels()[$lead->status] }}
            </span>
        </div>
        <p style="font-size: 0.8125rem; color: #78716c; margin: 0 0 1rem 0;">برای تغییر مرحله کلیک کنید</p>
        <div class="pipeline-nav">
            @foreach (Lead::pipelineStatuses() as $idx => $st)
                @php $isCurrent = $lead->status === $st; $textColor = Lead::statusTextColor($st); $bgColor = Lead::statusBgColor($st); @endphp
                <a href="{{ route('leads.change-status', ['lead' => $lead, 'status' => $st]) }}"
                   class="pipeline-step"
                   style="border-color: {{ $isCurrent ? $textColor : '#e7e5e4' }}; background: {{ $isCurrent ? $textColor : '#fff' }}; color: {{ $isCurrent ? '#fff' : $textColor }};"
                   onmouseover="if (!{{ $isCurrent ? 'true' : 'false' }}) { this.style.borderColor='{{ $textColor }}'; this.style.background='{{ $bgColor }}'; }"
                   onmouseout="if (!{{ $isCurrent ? 'true' : 'false' }}) { this.style.borderColor='#e7e5e4'; this.style.background='#fff'; }">
                    {{ Lead::statusLabels()[$st] }}
                </a>
                @if ($idx < count(Lead::pipelineStatuses()) - 1)
                    <span style="color: #d6d3d1; font-size: 1.25rem;">→</span>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Quick Actions --}}
    @php $canAssign = $lead->user_id === auth()->id() || auth()->user()->isAdmin() || $lead->assigned_to_id === auth()->id(); $shareUrl = route('leads.show', $lead); $shareText = 'سرنخ: ' . ($lead->name ?? 'بدون نام') . ($lead->company ? ' — ' . $lead->company : ''); @endphp
    <div class="cards-grid">
        <div class="card-modern">
            <h3 class="card-title" style="font-size: 1rem; margin-bottom: 1rem;">
                <span class="card-title-icon" style="background: #eff6ff; color: #1e40af;">
                    @include('components._icons', ['name' => 'share', 'class' => 'w-4 h-4'])
                </span>
                اشتراک و تقویم
            </h3>
            <div style="display: flex; flex-direction: column; gap: 1rem;">
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #78716c; margin-bottom: 0.5rem;">اشتراک</label>
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <a href="https://wa.me/?text={{ urlencode($shareText . ' ' . $shareUrl) }}" target="_blank" rel="noopener" class="btn" style="background: linear-gradient(135deg, #22c55e, #16a34a); color: #fff !important; border-color: #16a34a; padding: 0.5rem 1rem; font-size: 0.8125rem;">واتساپ</a>
                        <a href="https://t.me/share/url?url={{ urlencode($shareUrl) }}&text={{ urlencode($shareText) }}" target="_blank" rel="noopener" class="btn" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff !important; border-color: #0284c7; padding: 0.5rem 1rem; font-size: 0.8125rem;">تلگرام</a>
                    </div>
                </div>
                <div>
                    <label style="display: block; font-size: 0.8125rem; font-weight: 600; color: #78716c; margin-bottom: 0.5rem;">افزودن به تقویم</label>
                    <form action="{{ route('calendar.leads.task', $lead) }}" method="post" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                        @csrf
                        <input type="text" name="due_date" value="{{ FormatHelper::shamsi($lead->lead_date ?? now()) }}" placeholder="۱۴۰۳/۱۱/۱۵" required class="form-input" style="flex: 1; min-width: 150px;">
                        <button type="submit" class="btn" style="background: linear-gradient(135deg, #0ea5e9, #0284c7); color: #fff !important; border-color: #0284c7; padding: 0.5rem 1rem; font-size: 0.8125rem;">افزودن</button>
                    </form>
                </div>
            </div>
        </div>
        @if ($canAssign)
            <div class="card-modern" style="border-color: #bfdbfe; background: linear-gradient(to bottom, #f0f9ff 0%, #fff 100%);">
                <h3 class="card-title" style="font-size: 1rem; margin-bottom: 1rem;">
                    <span class="card-title-icon" style="background: #dbeafe; color: #1e40af;">
                        @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4'])
                    </span>
                    واگذاری به عضو تیم
                </h3>
                @if ($lead->assignedTo)
                    <div style="padding: 0.75rem 1rem; background: #fff; border-radius: 0.75rem; margin-bottom: 1rem; border: 1px solid #bfdbfe;">
                        <p style="font-size: 0.875rem; font-weight: 600; color: #1e40af; margin: 0;">{{ $lead->assignedTo->name }}</p>
                    </div>
                @endif
                <form action="{{ route('leads.assign', $lead) }}" method="post" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                    @csrf
                    <select name="assigned_to_id" class="form-input" style="flex: 1; min-width: 150px;">
                        <option value="">— واگذار نشده —</option>
                        @foreach ($users ?? [] as $u)
                            <option value="{{ $u->id }}" {{ $lead->assigned_to_id == $u->id ? 'selected' : '' }}>{{ $u->name }}{{ $u->id === auth()->id() ? ' (من)' : '' }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff !important; border-color: #2563eb; padding: 0.5rem 1rem; font-size: 0.8125rem;">واگذار</button>
                </form>
            </div>
        @endif
    </div>

    {{-- Information & Tags --}}
    <div class="cards-grid">
        <div class="card-modern">
            <h2 class="card-title">
                <span class="card-title-icon" style="background: #f0fdf4; color: #059669;">
                    @include('components._icons', ['name' => 'tag', 'class' => 'w-5 h-5'])
                </span>
                برچسب‌ها
            </h2>
            @if ($lead->tags->isNotEmpty())
                <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; margin-bottom: 1.25rem;">
                    @foreach ($lead->tags as $tag)
                        <a href="{{ route('tags.show', $tag) }}" class="tag-pill" style="background: {{ $tag->color }}15; color: {{ $tag->color }}; border-color: {{ $tag->color }}40;">
                            <span style="width: 0.625rem; height: 0.625rem; border-radius: 50%; background: {{ $tag->color }};"></span>
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
                    <button type="submit" class="btn btn-primary" style="margin-top: 1rem; padding: 0.5rem 1rem; font-size: 0.8125rem;">ذخیره برچسب‌ها</button>
                @endif
            </form>
            @endisset
        </div>

        <div class="card-modern">
            <h2 class="card-title">
                <span class="card-title-icon" style="background: #f5f5f4; color: #57534e;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                اطلاعات
            </h2>
            <div class="info-grid">
                @if ($lead->phone)
                    <div class="info-item">
                        <div class="info-label">تلفن</div>
                        <p class="info-value" dir="ltr"><a href="tel:{{ $lead->phone }}">{{ $lead->phone }}</a></p>
                    </div>
                @endif
                @if ($lead->email)
                    <div class="info-item">
                        <div class="info-label">ایمیل</div>
                        <p class="info-value" dir="ltr"><a href="mailto:{{ $lead->email }}">{{ $lead->email }}</a></p>
                    </div>
                @endif
                @if ($lead->leadChannel)
                    <div class="info-item">
                        <div class="info-label">کانال ورود</div>
                        <p class="info-value">{{ $lead->leadChannel->name }}</p>
                    </div>
                @endif
                @if ($lead->referrerContact)
                    <div class="info-item">
                        <div class="info-label">معرف</div>
                        <p class="info-value"><a href="{{ route('contacts.show', $lead->referrerContact) }}">{{ $lead->referrerContact->name }}</a></p>
                    </div>
                @endif
                @if ($lead->source)
                    <div class="info-item">
                        <div class="info-label">منبع</div>
                        <p class="info-value">{{ $lead->source }}</p>
                    </div>
                @endif
                @if ($lead->lead_date)
                    <div class="info-item">
                        <div class="info-label">تاریخ سرنخ</div>
                        <p class="info-value">{{ FormatHelper::shamsi($lead->lead_date) }}</p>
                    </div>
                @endif
                @if ($lead->value !== null && $lead->value > 0)
                    <div class="info-item">
                        <div class="info-label">ارزش</div>
                        <p class="info-value" style="color: #059669;">{{ FormatHelper::rial((int) $lead->value) }}</p>
                    </div>
                @endif
            </div>
            @if (!($lead->phone || $lead->email || $lead->leadChannel || $lead->referrerContact || $lead->source || $lead->lead_date || ($lead->value && $lead->value > 0)))
                <p style="margin: 1rem 0 0 0; font-size: 0.875rem; color: #a8a29e; text-align: center;">جزئیات ثبت نشده.</p>
            @endif
        </div>
    </div>

    {{-- Details --}}
    @if ($lead->details)
    <div class="card-modern">
        <h2 class="card-title">
            <span class="card-title-icon" style="background: #fef3c7; color: #92400e;">
                @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
            </span>
            جزئیات
        </h2>
        <div style="padding: 1.5rem; background: linear-gradient(to bottom, #fafaf9 0%, #fff 100%); border-radius: 1rem; border: 2px solid #e7e5e4; border-right: 4px solid #f59e0b; white-space: pre-wrap; font-size: 1rem; line-height: 1.8; color: #292524;">{{ $lead->details }}</div>
    </div>
    @endif

    {{-- Timeline & Tasks --}}
    <div class="cards-grid">
        <div class="card-modern span-full">
            <h2 class="card-title">
                <span class="card-title-icon" style="background: #f5f5f4; color: #57534e;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-5 h-5'])
                </span>
                تاریخچه و نظرات
            </h2>

            {{-- Call log form --}}
            <div class="call-form-box">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e40af; margin: 0 0 1rem 0; display: flex; align-items: center; gap: 0.5rem;">
                    📞 ثبت تماس
                </h3>
                <form action="{{ route('leads.call-log.store', $lead) }}" method="post">
                    @csrf
                    <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1rem;">
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.75rem;">
                            <div>
                                <label for="call_date_show" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">تاریخ تماس</label>
                                <input type="text" name="call_date" id="call_date_show" value="{{ \App\Helpers\FormatHelper::shamsi(now()) }}" placeholder="۱۴۰۳/۱۱/۱۵" required class="form-input" autocomplete="off">
                            </div>
                            <div style="display: flex; align-items: flex-end;">
                                <button type="button" id="call_date_today_show" class="btn" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff !important; border-color: #2563eb; padding: 0.5rem 1rem; font-size: 0.8125rem;" data-today="{{ \App\Helpers\FormatHelper::shamsi(now()) }}">امروز</button>
                            </div>
                        </div>
                        <div>
                            <label for="call_type_show" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">نوع تماس</label>
                            <select name="call_type" id="call_type_show" required class="form-input">
                                <option value="outgoing">خروجی (شما تماس گرفتید)</option>
                                <option value="incoming">ورودی (مشتری تماس گرفت)</option>
                            </select>
                        </div>
                        <div>
                            <label for="call_notes_show" style="display: block; font-size: 0.8125rem; font-weight: 600; color: #1e40af; margin-bottom: 0.5rem;">یادداشت تماس</label>
                            <textarea name="call_notes" id="call_notes_show" rows="3" placeholder="خلاصه مکالمه، نتیجه تماس، قرار بعدی، نیازهای مطرح شده…" required class="form-textarea"></textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn" style="background: linear-gradient(135deg, #3b82f6, #2563eb); color: #fff !important; border-color: #2563eb; padding: 0.625rem 1.25rem; font-size: 0.875rem; font-weight: 700;">ثبت تماس</button>
                </form>
            </div>

            {{-- Comment form --}}
            <div class="comment-form-box">
                <h3 style="font-size: 0.9375rem; font-weight: 600; color: #44403c; margin: 0 0 0.75rem 0;">افزودن نظر</h3>
                <form action="{{ route('leads.comments.store', $lead) }}" method="post">
                    @csrf
                    <textarea name="body" rows="3" placeholder="افزودن نظر یا رویداد…" required class="form-textarea" style="margin-bottom: 0.75rem;"></textarea>
                    <button type="submit" class="btn btn-primary" style="padding: 0.625rem 1.25rem; font-size: 0.875rem;">ثبت نظر</button>
                </form>
            </div>

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
                <p style="font-size: 0.875rem; color: #a8a29e; margin: 1.5rem 0 0 0; text-align: center; padding: 2rem;">هنوز فعالیت یا نظری ثبت نشده.</p>
            @else
                <div style="position: relative; margin-top: 1.5rem;">
                    @foreach ($timeline as $t)
                        <div class="timeline-item">
                            @if ($t['type'] === 'activity')
                                @php 
                                    $act = $t['item']; 
                                    $isCallLog = $act->from_status === $act->to_status && str_contains($act->comment ?? '', '📞');
                                    $toColor = $isCallLog ? '#3b82f6' : Lead::statusTextColor($act->to_status); 
                                    $toBg = $isCallLog ? '#dbeafe' : Lead::statusBgColor($act->to_status);
                                @endphp
                                <div class="timeline-dot" style="background: {{ $toColor }}; color: {{ $toColor }};"></div>
                                <div style="padding-right: 1.5rem;">
                                    @if ($isCallLog)
                                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                            <span style="font-size: 0.9375rem; font-weight: 700; color: #1e40af;">📞 تماس</span>
                                            <span style="font-size: 0.8125rem; color: #78716c; padding: 0.25rem 0.625rem; background: #f5f5f4; border-radius: 0.5rem;">{{ FormatHelper::shamsi($act->activity_date) }}</span>
                                        </div>
                                        @if ($act->comment)
                                            <div style="padding: 1rem 1.25rem; border-radius: 0.75rem; background: linear-gradient(to bottom, #eff6ff 0%, #dbeafe 100%); border-right: 3px solid #3b82f6; font-size: 0.9375rem; color: #1e3a8a; white-space: pre-wrap; line-height: 1.7;">{{ $act->comment }}</div>
                                        @endif
                                    @else
                                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                            @if ($act->from_status)
                                                <span style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: {{ Lead::statusBgColor($act->from_status) }}; color: {{ Lead::statusTextColor($act->from_status) }}; font-weight: 600;">{{ Lead::statusLabels()[$act->from_status] ?? $act->from_status }}</span>
                                            @else
                                                <span style="color: #a8a29e; font-size: 0.8125rem; padding: 0.375rem 0.75rem; background: #f5f5f4; border-radius: 0.5rem;">شروع</span>
                                            @endif
                                            <span style="color: #d6d3d1; font-size: 1.125rem;">→</span>
                                            <span style="font-size: 0.8125rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: {{ $toBg }}; color: {{ $toColor }}; font-weight: 700;">{{ Lead::statusLabels()[$act->to_status] ?? $act->to_status }}</span>
                                            <span style="font-size: 0.8125rem; color: #78716c; padding: 0.25rem 0.625rem; background: #f5f5f4; border-radius: 0.5rem;">{{ FormatHelper::shamsi($act->activity_date) }}</span>
                                        </div>
                                        @if ($act->comment)
                                            <div style="padding: 0.875rem 1rem; border-radius: 0.75rem; background: #fafaf9; border-right: 3px solid {{ $toColor }}40; font-size: 0.9375rem; color: #57534e; white-space: pre-wrap; line-height: 1.7;">{{ $act->comment }}</div>
                                        @endif
                                    @endif
                                </div>
                            @else
                                @php $c = $t['item']; @endphp
                                <div class="timeline-dot" style="background: #6366f1; color: #6366f1;"></div>
                                <div style="padding-right: 1.5rem;">
                                    <div style="font-size: 0.8125rem; color: #78716c; margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                                        <span style="font-weight: 600; color: #44403c;">{{ $c->user?->name ?? 'ناشناس' }}</span>
                                        <span>—</span>
                                        <span>{{ FormatHelper::shamsi($c->created_at) }}</span>
                                        <span>{{ $c->created_at->format('H:i') }}</span>
                                    </div>
                                    <div style="padding: 0.875rem 1rem; border-radius: 0.75rem; background: #fafaf9; border-right: 3px solid #6366f1; font-size: 0.9375rem; color: #44403c; white-space: pre-wrap; line-height: 1.7;">{{ $c->body }}</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Tasks -- Full Row --}}
    <div class="card-modern tasks-section">
        <div class="card-header">
            <h2 class="card-title">
                <span class="card-title-icon" style="background: #f0fdf4; color: #059669;">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5'])
                </span>
                وظایف
            </h2>
            <a href="{{ route('tasks.create', ['taskable_type' => 'lead', 'taskable_id' => $lead->id]) }}" class="btn" style="background: linear-gradient(135deg, #059669, #047857); color: #fff !important; border-color: #047857; padding: 0.5rem 1rem; font-size: 0.8125rem;">افزودن</a>
        </div>
        @if ($lead->tasks->isEmpty())
            <p style="font-size: 0.875rem; color: #78716c; margin: 0; text-align: center; padding: 2rem;">
                <a href="{{ route('tasks.create', ['taskable_type' => 'lead', 'taskable_id' => $lead->id]) }}" style="color: #059669; font-weight: 600; text-decoration: none;">افزودن وظیفه</a>
            </p>
        @else
            <div class="tasks-grid">
                @foreach ($lead->tasks as $t)
                    <a href="{{ route('tasks.show', $t) }}" style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; border-radius: 0.75rem; background: #fafaf9; border: 1px solid #e7e5e4; text-decoration: none; color: #292524; transition: all 0.2s;" onmouseover="this.style.background='#f5f5f4';this.style.borderColor='#d6d3d1';" onmouseout="this.style.background='#fafaf9';this.style.borderColor='#e7e5e4';">
                        <span style="font-weight: 600; font-size: 0.9375rem;">{{ $t->title }}</span>
                        <span style="font-size: 0.75rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: {{ \App\Models\Task::statusColors()[$t->status] }}20; color: {{ \App\Models\Task::statusColors()[$t->status] }}; font-weight: 600;">{{ \App\Models\Task::statusLabels()[$t->status] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Attachments / Images — for tracking customer requests --}}
    <div class="card-modern">
        <h2 class="card-title">
            <span class="card-title-icon" style="background: #f5f5f4; color: #57534e;">
                @include('components._icons', ['name' => 'attachment', 'class' => 'w-5 h-5'])
            </span>
            تصاویر و پیوست‌ها
        </h2>
        <p style="font-size: 0.8125rem; color: #78716c; margin: -0.5rem 0 1rem 0;">عکس درخواست مشتری، فاکتور پیشنهادی یا هر سند مرتبط را اینجا پیوست کنید.</p>

        @if(auth()->user()->canModule('leads', \App\Models\User::ABILITY_EDIT))
        <form id="lead-attachments-form" action="{{ route('leads.attachments.store', $lead) }}" method="post" enctype="multipart/form-data" style="margin-bottom: 1.5rem;">
            @csrf
            <input type="file" name="files[]" id="lead-attachments-input" accept="image/*,.pdf" multiple style="position: absolute; width: 0.1px; height: 0.1px; opacity: 0; overflow: hidden;">
            <div id="lead-upload-zone" class="lead-upload-zone" style="padding: 2rem; background: #fafaf9; border-radius: 1rem; border: 2px dashed #d6d3d1; text-align: center; cursor: pointer; transition: all 0.2s;">
                <div style="font-size: 2.5rem; margin-bottom: 0.75rem; color: #a8a29e;">📷</div>
                <p style="font-size: 0.9375rem; font-weight: 600; color: #57534e; margin: 0 0 0.25rem 0;">عکس یا فایل را اینجا بکشید یا کلیک کنید</p>
                <p style="font-size: 0.8125rem; color: #a8a29e; margin: 0;">عکس (JPG, PNG, WebP و…) یا PDF — حداکثر ۱۰ مگابایت برای هر فایل، چند فایل همزمان امکان‌پذیر است.</p>
            </div>
            <div id="lead-selected-files" style="display: none; margin-top: 1rem; padding: 1rem; background: #f5f5f4; border-radius: 0.75rem;">
                <p style="font-size: 0.875rem; font-weight: 600; color: #44403c; margin: 0 0 0.5rem 0;">فایل‌های انتخاب‌شده:</p>
                <ul id="lead-files-list" style="margin: 0; padding-right: 1.25rem; font-size: 0.8125rem; color: #57534e;"></ul>
                <div style="display: flex; gap: 0.75rem; margin-top: 0.75rem; flex-wrap: wrap;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.5rem 1.25rem; font-size: 0.875rem;">بارگذاری</button>
                    <button type="button" id="lead-clear-files" style="padding: 0.5rem 1rem; font-size: 0.875rem; background: #fff; border: 2px solid #d6d3d1; border-radius: 0.5rem; cursor: pointer; font-weight: 600; color: #57534e;">پاک کردن انتخاب</button>
                </div>
            </div>
        </form>
        <style>.lead-upload-zone:hover,.lead-upload-zone.dragover{ border-color:#059669 !important; background:#f0fdf4 !important; }</style>
        @endif

        @if ($lead->attachments->isEmpty())
            <p style="font-size: 0.875rem; color: #a8a29e; margin: 0; text-align: center; padding: 2rem;">هنوز پیوستی ثبت نشده. با استفاده از کادر بالا عکس یا سند درخواست مشتری را اضافه کنید.</p>
        @else
            <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));">
                @foreach ($lead->attachments as $att)
                    <div class="lead-attachment-card" style="padding: 0.75rem; border-radius: 0.75rem; background: #fafaf9; border: 1px solid #e7e5e4; transition: all 0.2s;">
                        @if ($att->isImage())
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" class="lead-attachment-thumb" style="display: block; aspect-ratio: 4/3; border-radius: 0.5rem; overflow: hidden; background: #e7e5e4; margin-bottom: 0.75rem;">
                                <img src="{{ $att->url() }}" alt="{{ $att->original_name }}" loading="lazy" style="width: 100%; height: 100%; object-fit: cover;">
                            </a>
                        @else
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" style="display: block; aspect-ratio: 4/3; border-radius: 0.5rem; background: linear-gradient(135deg, #e7e5e4, #d6d3d1); margin-bottom: 0.75rem; display: flex; align-items: center; justify-content: center; color: #78716c; font-size: 2rem; font-weight: 700;">PDF</a>
                        @endif
                        <p style="font-size: 0.75rem; color: #57534e; margin: 0 0 0.5rem 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 600;" title="{{ $att->original_name }}">{{ $att->original_name }}</p>
                        @if(auth()->user()->canModule('leads', \App\Models\User::ABILITY_EDIT))
                        <form action="{{ route('leads.attachments.destroy', [$lead, $att]) }}" method="post" onsubmit="return confirm('این پیوست حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" style="font-size: 0.75rem; color: #b91c1c; background: none; border: none; cursor: pointer; font-weight: 600; padding: 0.25rem 0.5rem; border-radius: 0.375rem; transition: all 0.2s;" onmouseover="this.style.background='#fef2f2';" onmouseout="this.style.background='none';">حذف</button>
                        </form>
                        @endif
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

    (function() {
        var zone = document.getElementById('lead-upload-zone');
        var input = document.getElementById('lead-attachments-input');
        var panel = document.getElementById('lead-selected-files');
        var list = document.getElementById('lead-files-list');
        var clearBtn = document.getElementById('lead-clear-files');
        if (!zone || !input) return;
        function updateFileList() {
            var files = input.files;
            if (!files || files.length === 0) {
                panel.style.display = 'none';
                list.innerHTML = '';
                return;
            }
            list.innerHTML = '';
            for (var i = 0; i < files.length; i++) {
                var li = document.createElement('li');
                li.textContent = files[i].name + ' (' + (files[i].size < 1024 ? files[i].size + ' B' : (files[i].size < 1024*1024 ? (files[i].size/1024).toFixed(1) + ' KB' : (files[i].size/1024/1024).toFixed(1) + ' MB') ) + ')';
                list.appendChild(li);
            }
            panel.style.display = 'block';
        }
        zone.addEventListener('click', function(e) { e.preventDefault(); input.click(); });
        input.addEventListener('change', updateFileList);
        zone.addEventListener('dragover', function(e) { e.preventDefault(); e.stopPropagation(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', function(e) { e.preventDefault(); zone.classList.remove('dragover'); });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('dragover');
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                updateFileList();
            }
        });
        if (clearBtn) clearBtn.addEventListener('click', function() {
            input.value = '';
            updateFileList();
        });
    })();

    var callDateBtn = document.getElementById('call_date_today_show');
    var callDateInput = document.getElementById('call_date_show');
    if (callDateBtn && callDateInput) {
        callDateBtn.addEventListener('click', function() {
            callDateInput.value = this.getAttribute('data-today') || '';
        });
    }
});
</script>
@endpush
@endsection
