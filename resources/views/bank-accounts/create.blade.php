@extends('layouts.app')
@section('title', 'حساب بانکی جدید')
@section('content')
<div class="container py-4">
    <div class="d-flex align-items-center gap-2 mb-4">
        <a href="{{ route('bank-accounts.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-right"></i></a>
        <h4 class="mb-0">حساب بانکی جدید</h4>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="{{ route('bank-accounts.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label">نام حساب <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">شماره حساب</label>
                    <input type="text" name="account_number" class="form-control" value="{{ old('account_number') }}" dir="ltr">
                </div>
                <div class="mb-3">
                    <label class="form-label">نام بانک</label>
                    <input type="text" name="bank_name" class="form-control" value="{{ old('bank_name') }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">شماره شبا (۲۶ کاراکتر)</label>
                    <input type="text" name="shaba" class="form-control" value="{{ old('shaba') }}" dir="ltr" maxlength="26" placeholder="IR...">
                    @error('shaba')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label class="form-label">یادداشت</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
                <button type="submit" class="btn btn-primary">ذخیره</button>
                <a href="{{ route('bank-accounts.index') }}" class="btn btn-secondary">انصراف</a>
            </form>
        </div>
    </div>
</div>
@endsection
