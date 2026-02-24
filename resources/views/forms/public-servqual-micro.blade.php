@extends('layouts.app-public')

@section('title', ($form->title ?? 'نظرسنجی') . ' — ' . config('app.name'))

@push('styles')
<style>
.sq-wrap { padding: 0 0.125rem; }
.sq-hero { margin-bottom: 1.25rem; }
.sq-hero h1 { font-size: 1.125rem; font-weight: 600; color: #1c1917; margin: 0 0 0.25rem; line-height: 1.35; }
.sq-hero .hint { font-size: 0.75rem; color: #78716c; line-height: 1.4; margin: 0; }
.sq-alert { padding: 0.625rem 0.75rem; border-radius: 0.5rem; margin-bottom: 0.75rem; font-size: 0.8125rem; line-height: 1.35; }
.sq-alert-success { background: #ecfdf5; color: #047857; }
.sq-alert-error { background: #fef2f2; color: #b91c1c; }
.sq-alert ul { margin: 0.25rem 0 0 1rem; padding: 0; }
.sq-q { background: #fff; border: 1px solid #e7e5e4; border-radius: 0.5rem; padding: 0.75rem 0.875rem; margin-bottom: 0.5rem; }
.sq-q .q-text { font-size: 0.875rem; font-weight: 500; color: #292524; margin: 0 0 0.5rem; line-height: 1.4; }
.sq-scale { display: flex; gap: 0.25rem; align-items: stretch; margin-bottom: 0.125rem; }
.sq-scale-opt { position: relative; flex: 1; min-width: 2.25rem; height: 2.25rem; display: flex; align-items: center; justify-content: center; border: 1px solid #e7e5e4; border-radius: 0.375rem; background: #fafaf9; font-weight: 600; font-size: 0.875rem; color: #78716c; cursor: pointer; transition: all 0.12s; }
.sq-scale-opt:hover { border-color: #a7f3d0; background: #ecfdf5; color: #047857; }
.sq-scale-opt input { position: absolute; opacity: 0; pointer-events: none; }
.sq-scale-opt:has(input:checked) { border-color: #059669; background: #d1fae5; color: #047857; }
.sq-scale-caption { font-size: 0.625rem; color: #a8a29e; display: flex; justify-content: space-between; padding: 0 0.1rem; margin-top: 0.125rem; }
.sq-actions { margin-top: 1rem; }
.sq-btn { width: 100%; min-height: 44px; padding: 0.625rem 1rem; font-size: 0.9375rem; font-weight: 600; border-radius: 0.5rem; border: none; background: #059669; color: #fff; cursor: pointer; font-family: inherit; transition: background 0.15s; }
.sq-btn:hover { background: #047857; }
.sq-done { text-align: center; padding: 1.5rem 1rem; background: #fff; border-radius: 0.5rem; border: 1px solid #d1fae5; }
.sq-done .ico { width: 2.5rem; height: 2.5rem; margin: 0 auto 0.75rem; border-radius: 50%; background: #d1fae5; color: #047857; display: flex; align-items: center; justify-content: center; font-size: 1.25rem; font-weight: 700; }
.sq-done h2 { font-size: 1rem; font-weight: 600; color: #292524; margin: 0 0 0.25rem; }
.sq-done p { font-size: 0.8125rem; color: #78716c; margin: 0; line-height: 1.4; }
.sq-empty { padding: 0.75rem; text-align: center; font-size: 0.8125rem; color: #78716c; background: #fff; border-radius: 0.5rem; border: 1px solid #e7e5e4; }
</style>
@endpush

@section('content')
<div class="sq-wrap">
    <div class="sq-hero">
        <h1>{{ $form->title ?? 'نظرسنجی' }}</h1>
        @if($questions->count() > 0 && !$submission->submitted_at)
            <p class="hint">۱ = کاملاً مخالف &nbsp; · &nbsp; ۵ = کاملاً موافق</p>
        @endif
    </div>

    @if (session('success'))
        <div class="sq-alert sq-alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="sq-alert sq-alert-error">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="sq-alert sq-alert-error">
            <ul>
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if ($submission->submitted_at)
        <div class="sq-done">
            <div class="ico">✓</div>
            <h2>متشکریم</h2>
            <p>نظر شما ثبت شد.</p>
        </div>
    @elseif($questions->isEmpty())
        <div class="sq-empty">سؤالی برای نمایش وجود ندارد.</div>
    @else
        <form action="{{ route('forms.public.submit', $link->code) }}" method="post">
            @csrf
            <input type="hidden" name="final" value="1">

            @foreach($questions as $q)
                <div class="sq-q">
                    <p class="q-text" id="q-{{ $q->id }}">{{ $q->text_fa ?: $q->text }}</p>
                    <div class="sq-scale" role="group" aria-labelledby="q-{{ $q->id }}" aria-label="۱ تا ۵">
                        @for($v = 1; $v <= 5; $v++)
                            <label class="sq-scale-opt">
                                <input type="radio" name="servqual_{{ $q->id }}" value="{{ $v }}" {{ old('servqual_' . $q->id) == $v ? 'checked' : '' }} required>
                                <span>{{ $v }}</span>
                            </label>
                        @endfor
                    </div>
                    <div class="sq-scale-caption">
                        <span>کاملاً مخالف</span>
                        <span>کاملاً موافق</span>
                    </div>
                </div>
            @endforeach

            <div class="sq-actions">
                <button type="submit" class="sq-btn">ارسال</button>
            </div>
        </form>
    @endif
</div>
@endsection
