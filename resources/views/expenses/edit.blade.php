@extends('layouts.app')

@section('title', 'ویرایش هزینه — ' . config('app.name'))

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon" style="background: #fff7ed; color: #c2410c; border-color: #fed7aa;">
                    @include('components._icons', ['name' => 'pencil', 'class' => 'w-5 h-5'])
                </span>
                ویرایش هزینه
            </h1>
        </div>
        <a href="{{ route('expenses.index') }}" class="ds-btn ds-btn-outline">بازگشت</a>
    </div>

    <div class="ds-form-card max-w-2xl">
        <form action="{{ route('expenses.update', $expense) }}" method="post">
            @csrf
            @method('PUT')
            @include('expenses._form', ['expense' => $expense, 'tags' => $tags, 'paymentOptions' => $paymentOptions, 'defaultPaidAt' => $defaultPaidAt])
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="ds-btn ds-btn-primary">ذخیره</button>
                <a href="{{ route('expenses.index') }}" class="ds-btn ds-btn-secondary">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
