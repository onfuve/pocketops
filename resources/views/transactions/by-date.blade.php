@php use App\Helpers\FormatHelper; use App\Models\Invoice; use App\Models\ContactTransaction; @endphp
@extends('layouts.app')
@section('title', 'تراکنش‌ها بر اساس تاریخ')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>تراکنش‌ها (بر اساس تاریخ)</h4>
        <a href="{{ route('transactions.by-contact') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-users me-1"></i>بر اساس مخاطب</a>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-auto">
                    <label class="form-label mb-0">از تاریخ</label>
                    <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
                </div>
                <div class="col-auto">
                    <label class="form-label mb-0">تا تاریخ</label>
                    <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
                </div>
                <div class="col-auto d-flex align-items-end">
                    <button type="submit" class="btn btn-secondary btn-sm"><i class="fas fa-filter"></i></button>
                </div>
            </form>
        </div>
    </div>
    {{-- Invoice-based transactions --}}
    <div class="card mb-4">
        <h5 class="card-header">پرداخت‌های مرتبط با فاکتور</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ</th>
                        <th>مبلغ</th>
                        <th>فاکتور</th>
                        <th>نوع فاکتور</th>
                        <th>مخاطب فاکتور</th>
                        <th>حساب بانکی / مخاطب پرداخت</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $t)
                    <tr>
                        <td>{{ FormatHelper::shamsi($t->paid_at) }}</td>
                        <td>{{ FormatHelper::rial($t->amount) }}</td>
                        <td><a href="{{ route('invoices.show', $t->invoice) }}">{{ $t->invoice->invoice_number ?? '#' . $t->invoice_id }}</a></td>
                        <td>@if($t->invoice->type === Invoice::TYPE_SELL)<span class="badge bg-success">فروش</span>@else<span class="badge bg-warning text-dark">خرید</span>@endif</td>
                        <td><a href="{{ route('contacts.show', $t->invoice->contact) }}">{{ $t->invoice->contact->name }}</a></td>
                        <td>
                            @if($t->paymentOption)
                                <i class="fas fa-university me-1"></i>{{ $t->paymentOption->label ?: ($t->paymentOption->holder_name ?? $t->paymentOption->bank_name ?? '—') }}
                            @elseif($t->bankAccount)
                                <i class="fas fa-university me-1"></i>{{ $t->bankAccount->name }}
                            @elseif($t->contact)
                                <i class="fas fa-user me-1"></i><a href="{{ route('contacts.show', $t->contact) }}">{{ $t->contact->name }}</a>
                            @else — @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">تراکنشی یافت نشد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="card-footer">{{ $transactions->links() }}</div>
        @endif
    </div>

    {{-- Contact receive/pay (no invoice) --}}
    <div class="card">
        <h5 class="card-header">دریافت / پرداخت (بدون فاکتور)</h5>
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>تاریخ</th>
                        <th>مبلغ</th>
                        <th>نوع</th>
                        <th>مخاطب</th>
                        <th>طرف معامله / حساب</th>
                        <th>برچسب‌ها</th>
                        <th>عملیات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contactTransactions as $t)
                    <tr>
                        <td>{{ FormatHelper::shamsi($t->paid_at) }}</td>
                        <td>{{ FormatHelper::rial($t->amount) }}</td>
                        <td>
                            @if($t->type === ContactTransaction::TYPE_RECEIVE)
                                <span class="badge bg-success">دریافت</span>
                            @else
                                <span class="badge bg-warning text-dark">پرداخت</span>
                            @endif
                        </td>
                        <td><a href="{{ route('contacts.show', $t->contact) }}">{{ $t->contact->name }}</a></td>
                        <td>
                            @if($t->paymentOption)
                                <i class="fas fa-university me-1"></i>{{ $t->paymentOption->label ?: ($t->paymentOption->holder_name ?? $t->paymentOption->bank_name ?? '—') }}
                            @elseif($t->counterpartyContact)
                                <i class="fas fa-user me-1"></i><a href="{{ route('contacts.show', $t->counterpartyContact) }}">{{ $t->counterpartyContact->name }}</a>
                            @else — @endif
                        </td>
                        <td>
                            @if($t->tags && $t->tags->isNotEmpty())
                                @foreach($t->tags as $tag)
                                    <span class="badge bg-light text-dark border" style="border-color: {{ $tag->color }}40; color: {{ $tag->color }}; background-color: {{ $tag->color }}15; margin-inline-start: 0.15rem;">{{ $tag->name }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('contacts.transactions.edit', [$t->contact, $t]) }}" class="btn btn-sm btn-outline-secondary">ویرایش</a>
                            <form method="post" action="{{ route('contacts.transactions.destroy', [$t->contact, $t]) }}" class="d-inline" onsubmit="return confirm('حذف این تراکنش؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center text-muted">تراکنش بدون فاکتور یافت نشد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($contactTransactions->hasPages())
            <div class="card-footer">{{ $contactTransactions->links() }}</div>
        @endif
    </div>
</div>
@endsection
