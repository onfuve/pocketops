@extends('layouts.app-public')

@section('title', 'مهلت ویرایش تمام شده — ' . config('app.name'))

@section('content')
<div style="max-width: 28rem; margin: 0 auto; padding: 2rem 1rem; text-align: center;">
    <p style="font-size: 1.125rem; font-weight: 500; color: var(--ds-text); margin-bottom: 0.5rem;">مهلت ویرایش این فرم تمام شده است.</p>
    <p style="font-size: 0.875rem; color: var(--ds-text-subtle);">در صورت نیاز با ارسال‌کننده تماس بگیرید.</p>
</div>
@endsection
