@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')

@section('title', $contact->name . ' — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="page-title break-words flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                {{ $contact->name }}
            </h1>
            @if ($contact->is_hamkar)
                <p class="mt-1 text-sm text-stone-500">
                    <span class="badge badge-amber">همکار</span>
                </p>
            @endif
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('invoices.create', ['contact_id' => $contact->id, 'type' => 'sell']) }}" class="btn-primary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #047857; box-shadow: 0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #047857 0%, #065f46 100%)';this.style.boxShadow='0 4px 8px rgba(5,150,105,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #059669 0%, #047857 100%)';this.style.boxShadow='0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4'])
                <span>فاکتور فروش</span>
            </a>
            <a href="{{ route('invoices.create', ['contact_id' => $contact->id, 'type' => 'buy']) }}" class="btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #0369a1; box-shadow: 0 2px 4px rgba(2,132,199,0.3), 0 1px 2px rgba(0,0,0,0.1); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #0369a1 0%, #075985 100%)';this.style.boxShadow='0 4px 8px rgba(2,132,199,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';this.style.boxShadow='0 2px 4px rgba(2,132,199,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                @include('components._icons', ['name' => 'buy', 'class' => 'w-4 h-4'])
                <span>فاکتور خرید</span>
            </a>
            <a href="{{ route('contacts.address-label', $contact) }}" class="btn-secondary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>چاپ برچسب آدرس</span>
            </a>
            <a href="{{ route('contacts.edit', $contact) }}" class="btn-secondary">
                @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                <span>ویرایش</span>
            </a>
            <a href="{{ route('contacts.index') }}" class="btn-secondary">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                <span>لیست مخاطبین</span>
            </a>
        </div>
    </div>

    {{-- Balance Section --}}
    @php
        $balance = (float) ($contact->balance ?? 0);
        $balanceColor = $balance > 0 ? '#047857' : ($balance < 0 ? '#b45309' : '#78716c');
        $balanceBg = $balance > 0 ? '#ecfdf5' : ($balance < 0 ? '#fffbeb' : '#fafaf9');
        $balanceBorder = $balance > 0 ? '#a7f3d0' : ($balance < 0 ? '#fde68a' : '#e7e5e4');
        $balanceLabel = $balance > 0 ? 'بستانکار از ما' : ($balance < 0 ? 'بدهکار به ما' : 'تسویه شده');
    @endphp
    <div class="card mb-6" style="border-right: 4px solid {{ $balanceBorder }}; background: {{ $balanceBg }};">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-sm font-semibold uppercase tracking-wider mb-2" style="color: #78716c;">{{ $balanceLabel }}</h2>
                <p class="font-vazir text-2xl font-bold" style="color: {{ $balanceColor }};">{{ FormatHelper::rial(abs($balance)) }}</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('contacts.receive-pay', $contact) }}" class="btn-primary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; background: linear-gradient(135deg, #059669 0%, #047857 100%); color: #fff; font-size: 0.875rem; font-weight: 600; border: 2px solid #047857; box-shadow: 0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1); text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='linear-gradient(135deg, #047857 0%, #065f46 100%)';this.style.boxShadow='0 4px 8px rgba(5,150,105,0.4)';this.style.transform='translateY(-1px)';" onmouseout="this.style.background='linear-gradient(135deg, #059669 0%, #047857 100%)';this.style.boxShadow='0 2px 4px rgba(5,150,105,0.3), 0 1px 2px rgba(0,0,0,0.1)';this.style.transform='translateY(0)';">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                    <span>دریافت / پرداخت</span>
                </a>
                <a href="{{ route('transactions.contact-detail', $contact) }}" class="btn-secondary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
                    <span>همه تراکنش‌ها</span>
                </a>
                <a href="{{ route('invoices.index', ['contact_id' => $contact->id]) }}" class="btn-secondary btn-touch" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1rem; border-radius: 0.75rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4'])
                    <span>همه فاکتورها</span>
                </a>
            </div>
        </div>
    </div>

    {{-- SERVQUAL Quality Index --}}
    @if($contact->relationLoaded('qualityIndex') && $contact->qualityIndex)
        @php
            $q = $contact->qualityIndex;
            $band = \App\Models\CustomerQualityIndex::bandForScore($q->overall_score);
            $bandLabel = $band ? (config('servqual.bands.' . $band . '.label_fa') ?? $band) : '—';
        @endphp
        <div class="card mb-6" style="border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem;">
            <h2 class="mb-3 text-base font-semibold text-stone-800" style="display: flex; align-items: center; gap: 0.5rem;">
                @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                شاخص کیفیت خدمات (SERVQUAL)
            </h2>
            <div class="flex flex-wrap gap-4 items-center">
                <div>
                    <span class="text-sm text-stone-500">امتیاز کلی</span>
                    <p class="text-xl font-bold" style="color: #047857;">{{ $q->overall_score !== null ? round($q->overall_score, 1) : '—' }}</p>
                </div>
                <div>
                    <span class="text-sm text-stone-500">دسته</span>
                    <p class="font-medium">{{ $bandLabel }}</p>
                </div>
                @if(!empty($q->risk_flags))
                    <div>
                        <span class="text-sm text-stone-500">هشدار</span>
                        <p class="text-amber-600 font-medium">{{ implode('، ', array_map(fn ($f) => $f === 'account_risk' ? 'خطر از دست دادن مشتری' : ($f === 'reputation_risk' ? 'ریسک اعتبار' : $f), $q->risk_flags)) }}</p>
                    </div>
                @endif
            </div>
            @if($q->last_calculated_at)
                <p class="mt-2 text-xs text-stone-400">آخرین به‌روزرسانی: {{ $q->last_calculated_at->diffForHumans() }}</p>
            @endif
        </div>
    @endif

    {{-- Tasks --}}
    <div class="card mb-6" style="border: 2px solid #e7e5e4; border-radius: 1rem; padding: 1.5rem;">
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
            وظایف
            <a href="{{ route('tasks.create', ['taskable_type' => 'contact', 'taskable_id' => $contact->id]) }}" style="margin-right: auto; display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #0369a1; color: #fff; font-size: 0.8125rem; font-weight: 500; text-decoration: none;">افزودن وظیفه</a>
        </h2>
        @if ($contact->tasks->isEmpty())
            <p class="text-sm text-stone-500">هنوز وظیفه‌ای ثبت نشده. <a href="{{ route('tasks.create', ['taskable_type' => 'contact', 'taskable_id' => $contact->id]) }}" class="font-medium text-stone-700 underline">افزودن وظیفه</a></p>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($contact->tasks as $t)
                    <a href="{{ route('tasks.show', $t) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.75rem; border-radius: 0.5rem; background: #f5f5f4; border: 1px solid #e7e5e4; text-decoration: none; color: #292524;">
                        <span style="font-weight: 600; font-size: 0.9375rem;">{{ $t->title }}</span>
                        <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; background: {{ \App\Models\Task::statusColors()[$t->status] }}20; color: {{ \App\Models\Task::statusColors()[$t->status] }};">{{ \App\Models\Task::statusLabels()[$t->status] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    @if ($contact->tags->isNotEmpty())
        <div class="mb-6 flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-stone-600">برچسب‌ها:</span>
            @foreach ($contact->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium hover:opacity-90 transition-opacity" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40; text-decoration: none;">
                    <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: {{ $tag->color }};"></span>
                    {{ $tag->name }}
                </a>
            @endforeach
        </div>
    @endif

    <div class="card">
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">اطلاعات مخاطب</h2>
        <dl class="grid gap-5 sm:grid-cols-1 md:grid-cols-2">
            @if ($contact->contactPhones->isNotEmpty())
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400 flex items-center gap-2">
                        تلفن‌ها
                    </dt>
                    <dd class="mt-1.5 flex flex-wrap gap-3">
                        @foreach ($contact->contactPhones as $ph)
                            <span dir="ltr" class="text-stone-800">
                                @if ($ph->label)
                                    <span class="text-stone-500">{{ $ph->label }}:</span>
                                @endif
                                <a href="tel:{{ $ph->phone }}" class="font-medium hover:underline" style="color: #047857;">{{ $ph->phone }}</a>
                            </span>
                        @endforeach
                    </dd>
                </div>
            @endif
            @if ($contact->address)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">آدرس</dt>
                    <dd class="mt-1.5 text-stone-800">{{ $contact->address }}</dd>
                </div>
            @endif
            @if ($contact->city)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">شهر</dt>
                    <dd class="mt-1.5 text-stone-800">{{ $contact->city }}</dd>
                </div>
            @endif
            @if ($contact->website)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">وب‌سایت</dt>
                    <dd class="mt-1.5" dir="ltr"><a href="{{ $contact->website }}" target="_blank" rel="noopener" class="hover:underline break-all" style="color: #0284c7;">{{ $contact->website }}</a></dd>
                </div>
            @endif
            @if ($contact->instagram || $contact->telegram || $contact->whatsapp)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">شبکه‌های اجتماعی</dt>
                    <dd class="mt-1.5 flex flex-wrap gap-3">
                        @if ($contact->instagram)
                            <a href="https://instagram.com/{{ ltrim($contact->instagram, '@') }}" target="_blank" rel="noopener" class="text-stone-700 hover:text-pink-600 hover:underline" dir="ltr">{{ $contact->instagram }}</a>
                        @endif
                        @if ($contact->telegram)
                            <a href="https://t.me/{{ ltrim($contact->telegram, '@') }}" target="_blank" rel="noopener" class="text-stone-700 hover:text-sky-600 hover:underline" dir="ltr">{{ $contact->telegram }}</a>
                        @endif
                        @if ($contact->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contact->whatsapp) }}" target="_blank" rel="noopener" class="text-stone-700 hover:text-emerald-600 hover:underline" dir="ltr">{{ $contact->whatsapp }}</a>
                        @endif
                    </dd>
                </div>
            @endif
            @if ($contact->referrer_name)
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">معرف</dt>
                    <dd class="mt-1.5 text-stone-800">{{ $contact->referrer_name }}</dd>
                </div>
            @endif
            @if ($contact->is_hamkar && $contact->linkedContact)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">مخاطب مرتبط</dt>
                    <dd class="mt-1.5"><a href="{{ route('contacts.show', $contact->linkedContact) }}" class="font-medium hover:underline" style="color: #047857;">{{ $contact->linkedContact->name }}</a></dd>
                </div>
            @endif
            @if ($contact->notes)
                <div class="sm:col-span-2">
                    <dt class="text-xs font-semibold uppercase tracking-wider text-stone-400">یادداشت</dt>
                    <dd class="mt-1.5 whitespace-pre-wrap text-stone-700">{{ $contact->notes }}</dd>
                </div>
            @endif
        </dl>
    </div>
@endsection
