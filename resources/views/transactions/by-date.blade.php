@php use App\Helpers\FormatHelper; use App\Models\Invoice; @endphp
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
    <div class="card">
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
                            @if($t->bankAccount)
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
</div>
@endsection
