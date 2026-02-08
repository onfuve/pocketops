@php use App\Helpers\FormatHelper; @endphp
@extends('layouts.app')
@section('title', 'تراکنش‌ها بر اساس مخاطب')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 class="mb-0"><i class="fas fa-users me-2"></i>تراکنش‌ها (بر اساس مخاطب)</h4>
        <a href="{{ route('transactions.by-date') }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-calendar-alt me-1"></i>بر اساس تاریخ</a>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <form method="get" class="row g-2">
                <div class="col-auto">
                    <select name="has_balance" class="form-select form-select-sm">
                        <option value="">همه مخاطبین</option>
                        <option value="yes" {{ request('has_balance') === 'yes' ? 'selected' : '' }}>با مانده</option>
                        <option value="zero" {{ request('has_balance') === 'zero' ? 'selected' : '' }}>بدون مانده</option>
                    </select>
                </div>
                <div class="col-auto">
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
                        <th>مخاطب</th>
                        <th>مانده (ریال)</th>
                        <th>تعداد فاکتور</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($contacts as $c)
                    <tr>
                        <td><a href="{{ route('contacts.show', $c) }}">{{ $c->name }}</a></td>
                        <td class="{{ $c->balance != 0 ? ($c->balance > 0 ? 'text-success' : 'text-danger') : '' }}">{{ FormatHelper::rial($c->balance) }}</td>
                        <td>{{ $c->invoices_count ?? 0 }}</td>
                        <td><a href="{{ route('transactions.contact-detail', $c) }}" class="btn btn-sm btn-outline-primary">تراکنش‌ها</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">مخاطبی یافت نشد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
