@extends('layouts.app-public')

@section('title', 'فرم در دسترس نیست — ' . config('app.name'))

@section('content')
<div style="max-width: 28rem; margin: 0 auto; padding: 2rem 1rem; text-align: center;">
    <p style="font-size: 1.125rem; font-weight: 500; color: var(--ds-text); margin-bottom: 0.5rem;">{{ $message ?? 'این فرم در دسترس نیست.' }}</p>
</div>
@endsection
