@php use App\Helpers\FormatHelper; use App\Models\Invoice; @endphp
@extends('layouts.app')

@section('title', 'تراکنش‌های ' . $contact->name . ' — ' . config('app.name'))

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="min-w-0">
            <h1 class="page-title break-words flex items-center gap-2">
                <span class="flex h-10 w-10 items-center justify-center rounded-xl" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                تراکنش‌های {{ $contact->name }}
            </h1>
            <p class="page-subtitle">تاریخچه پرداخت‌ها و فاکتورهای این مخاطب</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('contacts.show', $contact) }}" class="btn-secondary">
                @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4'])
                <span>مشاهده مخاطب</span>
            </a>
            <a href="{{ route('transactions.by-contact') }}" class="btn-secondary">
                @include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4'])
                <span>بازگشت به لیست</span>
            </a>
        </div>
    </div>

    {{-- Balance Summary --}}
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
            <div class="text-sm text-stone-600">
                <p>{{ $paymentsAsCounterparty->count() }} پرداخت در نقش طرف معامله</p>
                <p>{{ $invoices->count() }} فاکتور</p>
            </div>
        </div>
    </div>

    {{-- Payments as Counterparty --}}
    <div class="card mb-6">
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">پرداخت‌هایی که این مخاطب در آن طرف معامله است</h2>
        <p class="mb-4 text-sm text-stone-500">فروش: مشتری به این مخاطب پرداخت کرده. خرید: از طریق این مخاطب پرداخت شده.</p>
        @if ($paymentsAsCounterparty->isEmpty())
            <div class="empty-state">
                <p class="mb-3" style="color: #57534e;">تراکنشی در نقش طرف معامله ثبت نشده است.</p>
            </div>
        @else
            <div class="overflow-x-auto -mx-5 sm:mx-0">
                <table class="min-w-full text-right text-sm border-collapse">
                    <thead>
                        <tr class="bg-stone-100 border-b-2 border-stone-300">
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">تاریخ</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">مبلغ</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">فاکتور</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700">نوع</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach($paymentsAsCounterparty as $p)
                            <tr class="hover:bg-stone-50 transition">
                                <td class="px-4 py-3 text-stone-700 border-l border-stone-200">{{ FormatHelper::shamsi($p->paid_at) }}</td>
                                <td class="px-4 py-3 font-medium text-stone-800 border-l border-stone-200 font-vazir">{{ FormatHelper::rial($p->amount) }}</td>
                                <td class="px-4 py-3 border-l border-stone-200">
                                    <a href="{{ route('invoices.show', $p->invoice) }}" class="font-medium hover:underline" style="color: #047857;">{{ $p->invoice->invoice_number ?? '#' . $p->invoice_id }}</a>
                                    <span class="text-stone-500 text-xs block mt-1">مخاطب فاکتور: {{ $p->invoice->contact->name }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($p->invoice->type === Invoice::TYPE_SELL)
                                        <span class="badge badge-primary">فروش</span>
                                    @else
                                        <span class="badge badge-accent">خرید</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    {{-- Invoices --}}
    <div class="card">
        <h2 class="mb-4 border-b pb-3 text-base font-semibold text-stone-800" style="border-color: #e7e5e4;">فاکتورهای این مخاطب</h2>
        @if ($invoices->isEmpty())
            <div class="empty-state">
                <p class="mb-3" style="color: #57534e;">فاکتوری ثبت نشده است.</p>
                <a href="{{ route('invoices.create', ['contact_id' => $contact->id]) }}" class="font-semibold underline" style="color: #059669;">اولین فاکتور را ثبت کنید</a>
            </div>
        @else
            <div class="overflow-x-auto -mx-5 sm:mx-0">
                <table class="min-w-full text-right text-sm border-collapse">
                    <thead>
                        <tr class="bg-stone-100 border-b-2 border-stone-300">
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">نوع</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">شماره</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">مبلغ</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">پرداخت شده</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">باقیمانده</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700 border-l border-stone-200">تاریخ</th>
                            <th class="px-4 py-3 text-xs font-semibold text-stone-700">عملیات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-200">
                        @foreach($invoices as $inv)
                            @php
                                $totalPaid = $inv->totalPaid();
                                $remaining = (float)$inv->total - $totalPaid;
                            @endphp
                            <tr class="hover:bg-stone-50 transition">
                                <td class="px-4 py-3 border-l border-stone-200">
                                    @if($inv->type === Invoice::TYPE_SELL)
                                        <span class="badge badge-primary">فروش</span>
                                    @else
                                        <span class="badge badge-accent">خرید</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 font-medium text-stone-800 border-l border-stone-200">{{ $inv->invoice_number ?? '—' }}</td>
                                <td class="px-4 py-3 font-medium text-stone-800 border-l border-stone-200 font-vazir">{{ FormatHelper::rial($inv->total) }}</td>
                                <td class="px-4 py-3 border-l border-stone-200 font-vazir" style="color: #047857;">{{ FormatHelper::rial($totalPaid) }}</td>
                                <td class="px-4 py-3 border-l border-stone-200 font-vazir" style="{{ $remaining > 0 ? 'color: #b45309;' : 'color: #047857;' }}">
                                    {{ FormatHelper::rial($remaining) }}
                                    @if ($remaining <= 0)
                                        <span class="text-xs text-stone-500">— تسویه شده</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-stone-700 border-l border-stone-200">{{ FormatHelper::shamsi($inv->date) }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('invoices.show', $inv) }}" class="btn-secondary text-sm py-1.5 px-3" style="display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.375rem 0.75rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#d6d3d1';this.style.backgroundColor='#fafaf9';this.style.boxShadow='0 2px 4px rgba(0,0,0,0.1)';" onmouseout="this.style.borderColor='#e7e5e4';this.style.backgroundColor='#fff';this.style.boxShadow='0 1px 2px rgba(0,0,0,0.05)';">
                                        مشاهده
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
