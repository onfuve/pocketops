@php use App\Helpers\FormatHelper; use App\Models\Lead; @endphp
@extends('layouts.app')

@section('title', 'تغییر مرحله سرنخ — ' . config('app.name'))

@section('content')
<div style="max-width: 32rem; margin: 0 auto; padding: 0 1rem;">
    <div style="margin-bottom: 1.5rem;">
        <a href="{{ route('leads.show', $lead) }}" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #78716c; font-size: 0.875rem; text-decoration: none;">@include('components._icons', ['name' => 'arrow-left', 'class' => 'w-4 h-4']) بازگشت به سرنخ</a>
        <h1 style="font-size: 1.25rem; font-weight: 700; color: #292524; margin: 0.75rem 0 0.25rem 0;">تغییر مرحله سرنخ</h1>
        <p style="margin: 0; font-size: 0.875rem; color: #78716c;">{{ $lead->name ?? 'بدون نام' }}</p>
    </div>

    @php
        $newBg = Lead::statusBgColor($newStatus);
        $newText = Lead::statusTextColor($newStatus);
    @endphp
    <div style="padding: 1.5rem; border-radius: 1rem; border: 2px solid {{ $newText }}40; background: {{ $newBg }}; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1.25rem; padding: 0.75rem; border-radius: 0.5rem; background: {{ $newText }}; color: #fff;">
            <span style="display: inline-block; width: 0.75rem; height: 0.75rem; border-radius: 50%; background: #fff;"></span>
            <span style="font-weight: 600; font-size: 0.9375rem;">تغییر به: {{ Lead::statusLabels()[$newStatus] ?? $newStatus }}</span>
        </div>
        <form action="{{ route('leads.change-status.submit', $lead) }}" method="post">
            @csrf
            <input type="hidden" name="status" value="{{ $newStatus }}">
            <div style="margin-bottom: 1rem;">
                <label for="activity_date" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">تاریخ</label>
                <input type="text" name="activity_date" id="activity_date" value="{{ old('activity_date', $activityDate) }}" placeholder="۱۴۰۳/۱۱/۱۷"
                       style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; font-vazir;">
            </div>
            <div style="margin-bottom: 1.25rem;">
                <label for="comment" style="display: block; font-size: 0.875rem; font-weight: 500; color: #44403c; margin-bottom: 0.5rem;">نظر (اختیاری)</label>
                <textarea name="comment" id="comment" rows="4" placeholder="یادداشت درباره این تغییر مرحله…"
                          style="width: 100%; box-sizing: border-box; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-size: 1rem; resize: vertical;">{{ old('comment') }}</textarea>
            </div>
            <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
                <button type="submit" style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.625rem 1.25rem; border-radius: 0.5rem; background: {{ $newText }}; color: #fff; font-size: 0.875rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: opacity 0.2s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4'])
                    <span>ثبت تغییر مرحله</span>
                </button>
                <a href="{{ route('leads.show', $lead) }}" style="display: inline-flex; align-items: center; padding: 0.625rem 1.25rem; border-radius: 0.5rem; border: 2px solid #e7e5e4; background: #fff; color: #44403c; font-size: 0.875rem; font-weight: 500; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='#d6d3d1'; this.style.backgroundColor='#fafaf9'" onmouseout="this.style.borderColor='#e7e5e4'; this.style.backgroundColor='#fff'">انصراف</a>
            </div>
        </form>
    </div>
</div>
@endsection
