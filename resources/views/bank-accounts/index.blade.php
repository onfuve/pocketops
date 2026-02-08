@extends('layouts.app')
@section('title', 'حساب‌های بانکی')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <h4 class="mb-0"><i class="fas fa-university me-2"></i>حساب‌های بانکی</h4>
        <a href="{{ route('bank-accounts.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i>حساب جدید</a>
    </div>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif
    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>نام</th>
                        <th>شماره حساب</th>
                        <th>بانک</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bankAccounts as $b)
                    <tr>
                        <td>{{ $b->name }}</td>
                        <td dir="ltr">{{ $b->account_number ?? '—' }}</td>
                        <td>{{ $b->bank_name ?? '—' }}</td>
                        <td>
                            <a href="{{ route('bank-accounts.edit', $b) }}" class="btn btn-sm btn-outline-secondary">ویرایش</a>
                            <form action="{{ route('bank-accounts.destroy', $b) }}" method="post" class="d-inline" onsubmit="return confirm('حذف شود؟');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center text-muted">حساب بانکی تعریف نشده.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
