@php
    use App\Helpers\FormatHelper;
    use Illuminate\Support\Facades\URL;
    $isBuy = $invoice->type === 'buy';
    $docLabel = $isBuy ? 'رسید خرید' : 'فاکتور';
    $printUrl = route('invoices.print', $invoice);
    $publicPrintUrl = $invoice->status !== \App\Models\Invoice::STATUS_DRAFT
        ? URL::temporarySignedRoute('invoices.public.print', now()->addHours(48), ['invoice' => $invoice])
        : null;
    $shareText = $docLabel . ' ' . ($invoice->invoice_number ?: $invoice->id) . ' — ' . $invoice->contact->name . ' — مبلغ ' . FormatHelper::rial($invoice->total);
@endphp
@extends('layouts.app')

@section('title', $docLabel . ' ' . ($invoice->invoice_number ?: $invoice->id) . ' — ' . config('app.name'))

@push('styles')
<style>
.invoice-show { font-family: 'Vazirmatn', sans-serif; }
.invoice-show .invoice-section { border: 2px solid #d6d3d1; border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.06); }
.invoice-show .invoice-section-title { border-bottom: 2px solid #d6d3d1; padding-bottom: 0.75rem; margin-bottom: 1rem; font-size: 1.125rem; font-weight: 600; color: #292524; }
.invoice-show .btn-action { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0.625rem 1rem; border-radius: 0.75rem; font-family: 'Vazirmatn', sans-serif; font-size: 0.875rem; font-weight: 600; text-decoration: none; border: 2px solid transparent; transition: all 0.2s; }
.invoice-show .btn-action-primary { background: #059669; color: #fff !important; border-color: #047857; }
.invoice-show .btn-action-primary:hover { background: #047857; color: #fff !important; }
.invoice-show .btn-action-secondary { background: #fff; color: #44403c; border-color: #d6d3d1; }
.invoice-show .btn-action-secondary:hover { background: #fafaf9; color: #292524; }
.invoice-show table th, .invoice-show table td { font-family: 'Vazirmatn', sans-serif; }
</style>
@endpush

@section('content')
<div class="invoice-show font-vazir" style="max-width: 52rem; margin: 0 auto; padding: 0 1rem; box-sizing: border-box; font-family: 'Vazirmatn', sans-serif;">
    @php
        $isDraft = in_array($invoice->status, [\App\Models\Invoice::STATUS_DRAFT, null], true);
        $canEditOrDelete = $isDraft || ($invoice->status === \App\Models\Invoice::STATUS_FINAL && $invoice->canEditOrDelete());
    @endphp
    <div class="mb-8 flex flex-col gap-6 sm:flex-row sm:items-start sm:justify-between" style="gap: 1.5rem;">
        <div class="min-w-0">
            <h1 style="display: flex; align-items: center; gap: 0.75rem; margin: 0 0 0.25rem 0; font-size: 1.5rem; font-weight: 700; color: #292524; font-family: 'Vazirmatn', sans-serif;">
                <span style="display: flex; align-items: center; justify-content: center; width: 2.5rem; height: 2.5rem; border-radius: 0.75rem; {{ $isBuy ? 'background: #e0f2fe; color: #0369a1; border: 2px solid #bae6fd;' : 'background: #d1fae5; color: #047857; border: 2px solid #a7f3d0;' }}">
                    @include('components._icons', ['name' => $isBuy ? 'buy' : 'sell', 'class' => 'w-5 h-5'])
                </span>
                {{ $isBuy ? 'رسید خرید' : 'فاکتور فروش' }}
                @if ($invoice->invoice_number)
                    <span style="color: #78716c; font-weight: 400;">#{{ $invoice->invoice_number }}</span>
                @endif
            </h1>
            <p style="margin: 0.25rem 0 0 0; font-size: 0.875rem; color: #78716c; font-family: 'Vazirmatn', sans-serif;">{{ $isBuy ? 'فروشنده: ' : '' }}{{ $invoice->contact->name }} · {{ FormatHelper::shamsi($invoice->date) }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if ($isDraft)
                <form action="{{ route('invoices.mark-final', $invoice) }}" method="post" class="inline-flex" onsubmit="return confirm('{{ $isBuy ? "رسید" : "فاکتور" }} نهایی شود؟ پس از نهایی شدن و ثبت پرداخت، امکان ویرایش و حذف نخواهد بود.');">
                    @csrf
                    <button type="submit" class="btn-action btn-action-primary"><span style="color:#fff;">نهایی کردن {{ $isBuy ? 'رسید' : 'فاکتور' }}</span></button>
                </form>
            @endif
            @if ($canEditOrDelete)
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn-action" style="background:#44403c;color:#fff!important;border-color:#44403c;min-height:36px;padding:0.5rem;width:2.25rem;height:2.25rem;" title="ویرایش" aria-label="ویرایش">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                </a>
                @if (auth()->user()->canDeleteInvoice())
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="post" class="inline-flex" onsubmit="return confirm('{{ $isBuy ? "رسید" : "فاکتور" }} حذف شود؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-action-secondary" style="border-color:#fecaca;color:#b91c1c;min-height:36px;padding:0.5rem;width:2.25rem;height:2.25rem;" title="حذف" aria-label="حذف">
                            @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                        </button>
                    </form>
                @endif
            @endif
            <a href="{{ $printUrl }}" target="_blank" class="btn-action btn-action-secondary">نسخه چاپ</a>
            @if ($publicPrintUrl)
            <span style="display: inline-flex; align-items: center; gap: 0.25rem; font-size: 0.8125rem; color: #78716c; margin: 0 0.25rem;">اشتراک‌گذاری</span>
            <a href="https://wa.me/?text={{ rawurlencode($shareText . ' ' . $publicPrintUrl) }}" target="_blank" rel="noopener noreferrer" class="btn-action btn-action-secondary" style="min-width: 2.5rem; min-height: 2.5rem; padding: 0.5rem; color: #25D366 !important;" title="واتساپ" aria-label="اشتراک در واتساپ">
                @include('components._icons', ['name' => 'whatsapp', 'class' => 'w-5 h-5'])
            </a>
            <a href="https://t.me/share/url?url={{ rawurlencode($publicPrintUrl) }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener noreferrer" class="btn-action btn-action-secondary" style="min-width: 2.5rem; min-height: 2.5rem; padding: 0.5rem; color: #0088cc !important;" title="تلگرام" aria-label="اشتراک در تلگرام">
                @include('components._icons', ['name' => 'telegram', 'class' => 'w-5 h-5'])
            </a>
            @endif
            @if (!$isDraft)
                @php $totalPaid = $invoice->totalPaid(); $remaining = (float)$invoice->total - $totalPaid; @endphp
                @if ($remaining > 0)
                    <a href="{{ route('invoices.set-paid', $invoice) }}" class="btn-action btn-action-primary"><span style="color:#fff;">ثبت پرداخت</span></a>
                @endif
            @endif
            <a href="{{ route('invoices.index') }}" class="btn-action btn-action-secondary">لیست فاکتورها</a>
        </div>
    </div>

    @if ($isDraft)
        <div class="invoice-section mb-6" style="border-color:#f59e0b;background:#fffbeb;">
            <p class="mb-4 font-medium" style="font-family:'Vazirmatn',sans-serif;color:#92400e;font-size:1rem;">این {{ $isBuy ? 'رسید' : 'فاکتور' }} در وضعیت <strong>پیش‌نویس</strong> است. برای ثبت پرداخت، ابتدا آن را نهایی کنید.</p>
            <div class="flex flex-wrap items-center gap-3">
                <form action="{{ route('invoices.mark-final', $invoice) }}" method="post" class="inline-block" onsubmit="return confirm('{{ $isBuy ? "رسید" : "فاکتور" }} نهایی شود؟ پس از نهایی شدن و ثبت پرداخت، امکان ویرایش و حذف نخواهد بود.');">
                    @csrf
                    <button type="submit" class="btn-action btn-action-primary px-6 py-3" style="font-size:1rem;"><span style="color:#fff;">نهایی کردن {{ $isBuy ? 'رسید' : 'فاکتور' }}</span></button>
                </form>
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn-action btn-action-secondary" style="min-height:36px;padding:0.5rem;width:2.25rem;height:2.25rem;" title="ویرایش" aria-label="ویرایش">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                </a>
            </div>
        </div>
    @elseif ($canEditOrDelete)
        <div class="invoice-section mb-6" style="border-color:#a7f3d0;background:#ecfdf5;padding:0.75rem 1rem;">
            <div class="flex flex-wrap items-center gap-2" style="font-family:'Vazirmatn',sans-serif;">
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.25rem 0.5rem;border-radius:0.5rem;font-size:0.75rem;font-weight:600;background:#05966920;color:#047857;">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                    <span>نهایی</span>
                </span>
                <span style="display:inline-flex;align-items:center;gap:0.35rem;padding:0.25rem 0.5rem;border-radius:0.5rem;font-size:0.75rem;font-weight:600;background:#0369a120;color:#0369a1;">
                    @include('components._icons', ['name' => 'credit-card', 'class' => 'w-4 h-4'])
                    <span>بدون پرداخت</span>
                </span>
                <span style="width:1px;height:1rem;background:#a7f3d0;margin:0 0.25rem;"></span>
                <a href="{{ route('invoices.edit', $invoice) }}" class="btn-action" style="background:#44403c;color:#fff!important;border-color:#44403c;min-height:36px;padding:0.5rem;width:2.25rem;height:2.25rem;" title="ویرایش" aria-label="ویرایش">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                </a>
                @if (auth()->user()->canDeleteInvoice())
                    <form action="{{ route('invoices.destroy', $invoice) }}" method="post" class="inline-flex" onsubmit="return confirm('{{ $isBuy ? "رسید" : "فاکتور" }} حذف شود؟');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-action btn-action-secondary" style="border-color:#fecaca;color:#b91c1c;min-height:36px;padding:0.5rem;width:2.25rem;height:2.25rem;" title="حذف" aria-label="حذف">
                            @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endif

    @if (!$isDraft)
        @php $totalPaid = $invoice->totalPaid(); $remaining = (float)$invoice->total - $totalPaid; @endphp
        <div class="invoice-section mb-6" style="{{ $remaining <= 0 ? 'border-color:#10b981;background:#ecfdf5;' : 'border-color:#f59e0b;background:#fffbeb;' }}">
            <h2 class="invoice-section-title" style="font-family:'Vazirmatn',sans-serif;">وضعیت پرداخت</h2>
            <div class="grid gap-6 sm:grid-cols-3 mb-6" style="gap:1.5rem;margin-bottom:1.5rem;">
                <div class="p-4 rounded-xl border-2 border-stone-200 bg-white" style="padding:1rem;border-radius:0.75rem;border:2px solid #e7e5e4;">
                    <p class="text-xs font-semibold text-stone-500 mb-1" style="font-family:'Vazirmatn',sans-serif;">مبلغ فاکتور</p>
                    <p class="font-vazir font-semibold text-stone-800 text-lg" style="font-family:'Vazirmatn',sans-serif;">{{ FormatHelper::rial($invoice->total) }}</p>
                </div>
                <div class="p-4 rounded-xl border-2 border-stone-200 bg-white" style="padding:1rem;border-radius:0.75rem;border:2px solid #e7e5e4;">
                    <p class="text-xs font-semibold text-stone-500 mb-1" style="font-family:'Vazirmatn',sans-serif;">پرداخت شده</p>
                    <p class="font-vazir font-semibold text-lg" style="font-family:'Vazirmatn',sans-serif;color:#047857;">{{ FormatHelper::rial($totalPaid) }}</p>
                </div>
                <div class="p-4 rounded-xl border-2 border-stone-200 bg-white" style="padding:1rem;border-radius:0.75rem;border:2px solid #e7e5e4;">
                    <p class="text-xs font-semibold text-stone-500 mb-1" style="font-family:'Vazirmatn',sans-serif;">باقیمانده</p>
                    <p class="font-vazir font-semibold text-lg" style="font-family:'Vazirmatn',sans-serif;{{ $remaining > 0 ? 'color:#b45309;' : 'color:#047857;' }}">{{ FormatHelper::rial($remaining) }} {{ $remaining <= 0 ? '— تسویه شده' : '' }}</p>
                </div>
            </div>
            @if ($invoice->payments->isNotEmpty())
                <div class="overflow-x-auto mb-6 rounded-xl border-2 border-stone-300" style="border:2px solid #d6d3d1;border-radius:0.75rem;">
                    <table class="min-w-full text-right text-sm border-collapse" style="font-family:'Vazirmatn',sans-serif;">
                        <thead class="bg-stone-200" style="background:#e7e5e4;">
                            <tr>
                                <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-b-2 border-stone-400 border-l border-stone-300" style="padding:0.75rem 1rem;">تاریخ</th>
                                <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-b-2 border-stone-400 border-l border-stone-300" style="padding:0.75rem 1rem;">مبلغ</th>
                                <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-b-2 border-stone-400 border-l border-stone-300" style="padding:0.75rem 1rem;">حساب / {{ $isBuy ? 'فروشنده' : 'مشتری' }}</th>
                                <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-b-2 border-stone-400" style="padding:0.75rem 1rem;">عملیات</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-stone-200">
                            @foreach ($invoice->payments as $pay)
                                <tr>
                                    <td class="px-4 py-3 text-stone-700 border-l border-stone-200">{{ FormatHelper::shamsi($pay->paid_at) }}</td>
                                    <td class="px-4 py-3 font-medium text-stone-800 border-l border-stone-200">{{ FormatHelper::rial($pay->amount) }}</td>
                                    <td class="px-4 py-3 border-l border-stone-200">
                                        @if ($pay->paymentOption)
                                            <span>{{ $pay->paymentOption->label ?: ($pay->paymentOption->holder_name ?? $pay->paymentOption->bank_name ?? '—') }}</span>
                                        @elseif ($pay->bankAccount)
                                            <span>{{ $pay->bankAccount->name }}</span>
                                        @elseif ($pay->contact)
                                            <a href="{{ route('contacts.show', $pay->contact) }}" class="text-stone-700 hover:underline">{{ $pay->contact->name }}</a>
                                        @else — @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <form action="{{ route('invoices.payments.destroy', [$invoice, $pay]) }}" method="post" class="inline" onsubmit="return confirm('این پرداخت حذف شود؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-sm font-medium hover:underline" style="color:#b91c1c;font-family:'Vazirmatn',sans-serif;">حذف</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
            @if ($remaining > 0)
                <div class="mt-6 pt-4 border-t-2 border-stone-200" style="border-top:2px solid #e7e5e4;padding-top:1rem;">
                    <a href="{{ route('invoices.set-paid', $invoice) }}" class="btn-action btn-action-primary px-6 py-3" style="font-size:1rem;"><span style="color:#fff;">ثبت پرداخت</span></a>
                </div>
            @endif
        </div>
    @endif

    @if ($invoice->tags->isNotEmpty())
        <div class="invoice-section mb-6">
            <div class="flex flex-wrap items-center gap-2" style="gap:0.5rem;">
            <span class="text-sm font-medium text-stone-600">برچسب‌ها:</span>
            @foreach ($invoice->tags as $tag)
                <a href="{{ route('tags.show', $tag) }}" class="inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-medium hover:opacity-90 transition-opacity" style="background: {{ $tag->color }}20; color: {{ $tag->color }}; border: 1px solid {{ $tag->color }}40; text-decoration: none;">
                    <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: {{ $tag->color }};"></span>
                    {{ $tag->name }}
                </a>
            @endforeach
            </div>
        </div>
    @endif

    <div class="invoice-section invoice-paper overflow-hidden" style="border:2px solid {{ $isBuy ? '#7dd3fc' : '#a8a29e' }};">
        <div class="border-b-2 border-stone-400 px-4 py-5 sm:px-8" style="background: {{ $isBuy ? '#f0f9ff' : '#f5f5f4' }};">
            <div class="grid gap-6 grid-cols-1 sm:grid-cols-2">
                <div class="text-right">
                    <p class="text-xs font-semibold uppercase tracking-wider text-stone-500">{{ $isBuy ? 'فروشنده' : 'مشتری' }}</p>
                    <a href="{{ route('contacts.show', $invoice->contact) }}" class="mt-0.5 block text-lg font-bold text-stone-800 hover:underline">{{ $invoice->contact->name }}</a>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-xs font-semibold text-stone-500">شماره فاکتور</p>
                    <p class="mt-0.5 font-vazir text-lg font-semibold text-stone-800">{{ $invoice->invoice_number ?: $invoice->id }}</p>
                    <p class="mt-2 text-xs font-semibold text-stone-500">تاریخ</p>
                    <p class="mt-0.5 font-vazir font-semibold text-stone-800">{{ FormatHelper::shamsi($invoice->date) }}</p>
                </div>
            </div>
        </div>
        <div class="overflow-x-auto -mx-4 sm:mx-0">
            <table class="min-w-full text-right text-sm sm:text-base border-collapse border border-stone-300">
                <thead class="bg-stone-200 border-b-2 border-stone-400">
                    <tr>
                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-stone-700 border-l border-stone-300 sm:px-6">ردیف</th>
                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-stone-700 border-l border-stone-300 sm:px-6">شرح کالا / خدمات</th>
                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-stone-700 border-l border-stone-300 sm:px-6">تعداد</th>
                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-stone-700 border-l border-stone-300 sm:px-6">قیمت واحد (ریال)</th>
                        <th scope="col" class="px-3 py-3 text-xs font-semibold text-stone-700 sm:px-6">مبلغ (ریال)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-300">
                    @foreach ($invoice->items as $i => $item)
                        <tr class="bg-white">
                            <td class="px-3 py-3 font-vazir text-stone-600 border-l border-stone-200 sm:px-6 sm:py-4">{{ FormatHelper::englishToPersian((string)($i + 1)) }}</td>
                            <td class="px-3 py-3 font-medium text-stone-800 border-l border-stone-200 sm:px-6 sm:py-4">{{ $item->description }}</td>
                            <td class="px-3 py-3 font-vazir text-stone-700 border-l border-stone-200 sm:px-6 sm:py-4">{{ FormatHelper::numberFormat($item->quantity) }}</td>
                            <td class="px-3 py-3 font-vazir text-stone-700 border-l border-stone-200 sm:px-6 sm:py-4">{{ FormatHelper::numberFormat($item->unit_price) }}</td>
                            <td class="px-3 py-3 font-vazir font-semibold text-stone-800 sm:px-6 sm:py-4">{{ FormatHelper::numberFormat($item->amount) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="border-t-2 border-stone-300 bg-stone-50/50 px-6 py-5 sm:px-8">
            <dl class="flex flex-col gap-2 sm:mr-auto sm:max-w-sm sm:gap-3">
                <div class="flex justify-between text-sm">
                    <dt class="text-stone-500">جمع کل (ریال)</dt>
                    <dd class="font-vazir font-medium">{{ FormatHelper::rial($invoice->subtotal) }}</dd>
                </div>
                @if ($invoice->effectiveDiscount() > 0)
                    <div class="flex justify-between text-sm">
                        <dt class="text-stone-500">تخفیف</dt>
                        <dd class="font-vazir text-red-600">−{{ FormatHelper::numberFormat($invoice->effectiveDiscount()) }} ریال @if($invoice->discount_percent)({{ FormatHelper::numberFormat($invoice->discount_percent) }}٪)@endif</dd>
                    </div>
                @endif
                <div class="flex justify-between border-t-2 border-stone-300 pt-3 text-base font-bold">
                    <dt>مبلغ قابل پرداخت (ریال)</dt>
                    <dd class="font-vazir">{{ FormatHelper::rial($invoice->total) }}</dd>
                </div>
            </dl>
        </div>
        @if ($invoice->notes)
            <div class="border-t-2 border-stone-300 px-6 py-4 sm:px-8">
                <p class="text-xs font-semibold text-stone-500">یادداشت</p>
                <p class="mt-1 whitespace-pre-wrap text-sm text-stone-700">{{ $invoice->notes }}</p>
            </div>
        @endif
        @if (!$isBuy)
            <div class="border-t-2 border-stone-300 px-6 py-5 sm:px-8 flex justify-end" style="border-top:2px solid #d6d3d1;padding:1.25rem 2rem;">
                <div class="flex items-center gap-4">
                    <span class="text-sm font-semibold text-stone-500 whitespace-nowrap">نام و امضا:</span>
                    <span class="inline-block w-48 h-[4.5rem] border border-stone-300 rounded bg-stone-50/80"></span>
                </div>
            </div>
        @endif
    </div>

    @if ($invoice->type === 'sell')
    <div class="invoice-section mt-6">
        <h2 class="invoice-section-title" style="font-family:'Vazirmatn',sans-serif;">حساب و کارت برای این فاکتور</h2>
        <p class="mb-4 text-sm text-stone-500">حساب‌ها یا کارت‌هایی که در چاپ نمایش داده می‌شوند را انتخاب کنید. برای هر مورد می‌توانید جداگانه شماره کارت، شبا و شماره حساب را در چاپ لحاظ کنید.</p>
        @if ($paymentOptions->isEmpty())
            <p class="text-sm text-stone-500">هنوز حسابی تعریف نشده. از <a href="{{ route('settings.payment-options') }}" class="font-medium text-stone-700 underline">تنظیمات کارت و حساب</a> اضافه کنید.</p>
        @else
            <form action="{{ route('invoices.payment-options.update', $invoice) }}" method="post" class="space-y-4">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    @foreach ($paymentOptions as $opt)
                        @php
                            $selected = in_array($opt->id, $selectedIds);
                            $fields = $paymentOptionFields[$opt->id ?? ''] ?? [];
                            $printCard = $fields['print_card_number'] ?? $opt->print_card_number ?? true;
                            $printIban = $fields['print_iban'] ?? $opt->print_iban ?? true;
                            $printAccount = $fields['print_account_number'] ?? $opt->print_account_number ?? true;
                        @endphp
                        <div class="rounded-xl border-2 border-stone-200 bg-stone-50/50 p-4">
                            <label class="inline-flex cursor-pointer items-center gap-2 mb-3">
                                <input type="checkbox" name="payment_option_ids[]" value="{{ $opt->id }}" {{ $selected ? 'checked' : '' }} class="payment-opt-include rounded border-stone-300 text-stone-700 focus:ring-stone-500" data-opt-id="{{ $opt->id }}">
                                <span class="font-medium text-stone-700">{{ $opt->label }}</span>
                                @if ($opt->holder_name)<span class="text-stone-500 text-sm">— {{ $opt->holder_name }}</span>@endif
                                @if ($opt->bank_name)<span class="text-stone-500 text-sm">· {{ $opt->bank_name }}</span>@endif
                            </label>
                            <div class="payment-opt-fields mr-6 flex flex-wrap gap-x-6 gap-y-2 {{ $selected ? '' : 'opacity-60 pointer-events-none' }}" data-opt-id="{{ $opt->id }}">
                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_card_number]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_card_number]" value="1" {{ $printCard ? 'checked' : '' }} class="rounded border-stone-300">
                                    <span>شماره کارت</span>
                                </label>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_iban]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_iban]" value="1" {{ $printIban ? 'checked' : '' }} class="rounded border-stone-300">
                                    <span>شبا</span>
                                </label>
                                <label class="inline-flex cursor-pointer items-center gap-1.5 text-sm">
                                    <input type="hidden" name="payment_option_fields[{{ $opt->id }}][print_account_number]" value="0">
                                    <input type="checkbox" name="payment_option_fields[{{ $opt->id }}][print_account_number]" value="1" {{ $printAccount ? 'checked' : '' }} class="rounded border-stone-300">
                                    <span>شماره حساب</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="btn-action mt-4" style="background:#44403c;color:#fff!important;border-color:#44403c;"><span style="color:#fff;">ذخیره انتخاب</span></button>
            </form>
            <script>
            document.querySelectorAll('.payment-opt-include').forEach(function(cb){
                cb.addEventListener('change', function(){
                    var id = this.getAttribute('data-opt-id');
                    var block = document.querySelector('.payment-opt-fields[data-opt-id="'+id+'"]');
                    if (block) {
                        block.classList.toggle('opacity-60', !this.checked);
                        block.classList.toggle('pointer-events-none', !this.checked);
                    }
                });
            });
            </script>
        @endif
    </div>
    @endif

    {{-- Tasks --}}
    <div class="invoice-section mt-6">
        <h2 class="invoice-section-title" style="font-family:'Vazirmatn',sans-serif; display: flex; align-items: center; gap: 0.5rem;">
            @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
            وظایف
            <a href="{{ route('tasks.create', ['taskable_type' => 'invoice', 'taskable_id' => $invoice->id]) }}" style="margin-right: auto; display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; background: #0369a1; color: #fff; font-size: 0.8125rem; font-weight: 500; text-decoration: none;">افزودن وظیفه</a>
        </h2>
        @if ($invoice->tasks->isEmpty())
            <p class="text-sm text-stone-500">هنوز وظیفه‌ای ثبت نشده. <a href="{{ route('tasks.create', ['taskable_type' => 'invoice', 'taskable_id' => $invoice->id]) }}" class="font-medium text-stone-700 underline">افزودن وظیفه</a></p>
        @else
            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                @foreach ($invoice->tasks as $t)
                    <a href="{{ route('tasks.show', $t) }}" style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.75rem; border-radius: 0.5rem; background: #f5f5f4; border: 1px solid #e7e5e4; text-decoration: none; color: #292524;">
                        <span style="font-weight: 600; font-size: 0.9375rem;">{{ $t->title }}</span>
                        <span style="font-size: 0.75rem; padding: 0.2rem 0.5rem; border-radius: 0.25rem; background: {{ \App\Models\Task::statusColors()[$t->status] }}20; color: {{ \App\Models\Task::statusColors()[$t->status] }};">{{ \App\Models\Task::statusLabels()[$t->status] }}</span>
                    </a>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Attachments (screenshots / photos) --}}
    <div class="invoice-section mt-6">
        <h2 class="invoice-section-title" style="font-family:'Vazirmatn',sans-serif;">تصاویر و پیوست‌ها</h2>
        <form action="{{ route('invoices.attachments.store', $invoice) }}" method="post" enctype="multipart/form-data" class="mb-4">
            @csrf
            <div class="flex flex-wrap items-end gap-2">
                <div>
                    <input type="file" name="file" accept="image/*,.pdf,image/jpeg,image/png,image/gif,image/webp" required class="text-sm text-stone-600 file:mr-2 file:rounded-lg file:border-0 file:bg-emerald-50 file:px-4 file:py-2 file:text-emerald-700">
                    <p class="mt-1 text-xs text-stone-500">JPG, PNG, GIF, WebP یا PDF — حداکثر ۱۰ مگابایت</p>
                </div>
                <button type="submit" class="btn-action btn-action-primary"><span style="color:#fff;">افزودن پیوست</span></button>
            </div>
        </form>
        @if ($invoice->attachments->isEmpty())
            <p class="text-sm text-stone-500">هنوز پیوستی ثبت نشده است.</p>
        @else
            <div class="grid gap-3 sm:grid-cols-2 md:grid-cols-3">
                @foreach ($invoice->attachments as $att)
                    <div class="rounded-xl border border-stone-200 bg-stone-50/50 p-2">
                        @if ($att->isImage())
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" class="block rounded-lg overflow-hidden bg-stone-200 mb-2" style="aspect-ratio: 4/3;">
                                <img src="{{ $att->url() }}" alt="{{ $att->original_name }}" class="w-full h-full object-cover">
                            </a>
                        @else
                            <a href="{{ $att->url() }}" target="_blank" rel="noopener" class="block rounded-lg bg-stone-200 p-4 mb-2 text-center text-sm text-stone-600">PDF</a>
                        @endif
                        <p class="text-xs text-stone-600 truncate" title="{{ $att->original_name }}">{{ $att->original_name }}</p>
                        <form action="{{ route('invoices.attachments.destroy', [$invoice, $att]) }}" method="post" class="mt-1" onsubmit="return confirm('این پیوست حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">حذف</button>
                        </form>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="invoice-section mt-6">
        <h2 class="invoice-section-title" style="font-family:'Vazirmatn',sans-serif;">اشتراک‌گذاری</h2>
        <p class="mb-4 text-sm text-stone-500">{{ $isBuy ? 'لینک رسید یا تصویر را برای بایگانی یا حسابدار به‌اشتراک بگذارید.' : 'لینک فاکتور یا تصویر را با مشتری به‌اشتراک بگذارید.' }}</p>
        <div class="flex flex-wrap items-center gap-3" style="gap:0.75rem;">
            @if ($publicPrintUrl)
            <a href="https://wa.me/?text={{ rawurlencode($shareText . ' ' . $publicPrintUrl) }}" target="_blank" rel="noopener" class="btn-action" style="background:#22c55e;color:#fff!important;border-color:#16a34a;" title="واتساپ" aria-label="اشتراک در واتساپ">@include('components._icons', ['name' => 'whatsapp', 'class' => 'w-5 h-5'])</a>
            <a href="https://t.me/share/url?url={{ rawurlencode($publicPrintUrl) }}&text={{ rawurlencode($shareText) }}" target="_blank" rel="noopener" class="btn-action" style="background:#0ea5e9;color:#fff!important;border-color:#0284c7;" title="تلگرام" aria-label="اشتراک در تلگرام">@include('components._icons', ['name' => 'telegram', 'class' => 'w-5 h-5'])</a>
            @endif
            <a href="{{ $printUrl }}" target="_blank" rel="noopener" class="btn-action btn-action-secondary">نسخه چاپ / تصویر</a>
        </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-3" style="gap:0.75rem;">
        <a href="{{ route('invoices.create', ['contact_id' => $invoice->contact_id, 'type' => 'sell']) }}" class="btn-action btn-action-secondary" style="border-color:#a7f3d0;background:#ecfdf5;color:#065f46;">فاکتور فروش برای همین مشتری</a>
        <a href="{{ route('invoices.create', ['contact_id' => $invoice->contact_id, 'type' => 'buy']) }}" class="btn-action btn-action-secondary" style="border-color:#bae6fd;background:#e0f2fe;color:#0369a1;">فاکتور خرید برای همین فروشنده</a>
    </div>
</div>
@endsection
