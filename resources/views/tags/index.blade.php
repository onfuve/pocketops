@extends('layouts.app')

@section('title', 'برچسب‌ها — ' . config('app.name'))

@push('styles')
<style>
.ds-page .ds-page-title-icon { background: #dbeafe; color: #1e40af; border-color: #93c5fd; }
</style>
@endpush

@section('content')
<div class="ds-page">
    <div class="ds-page-header">
        <div>
            <h1 class="ds-page-title">
                <span class="ds-page-title-icon">
                    @include('components._icons', ['name' => 'tag', 'class' => 'w-5 h-5'])
                </span>
                برچسب‌ها
            </h1>
            <p class="ds-page-subtitle">برچسب‌ها را برای دسته‌بندی سرنخ‌ها، مخاطبین و فاکتورها استفاده کنید.</p>
        </div>
        <a href="{{ route('tags.create') }}" class="ds-btn ds-btn-primary">
            @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4'])
            برچسب جدید
        </a>
    </div>

    @if (session('success'))
        <div class="ds-alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="ds-alert-error" style="margin-bottom: 1rem;">{{ session('error') }}</div>
    @endif

    @if ($tags->isEmpty())
        <div class="ds-empty">
            <p style="margin: 0 0 0.5rem 0; color: var(--ds-text-subtle);">هنوز برچسبی ثبت نشده است.</p>
            <p style="margin: 0; font-size: 0.875rem;"><a href="{{ route('tags.create') }}" style="font-weight: 600; color: var(--ds-primary);">اولین برچسب را اضافه کنید</a></p>
        </div>
    @else
        <div style="display: grid; gap: 0.75rem;">
            @foreach ($tags as $tag)
                <div class="ds-card ds-card-static" style="display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 1rem;">
                    <a href="{{ route('tags.show', $tag) }}" style="display: flex; align-items: center; gap: 0.75rem; min-width: 0; flex: 1; text-decoration: none; color: inherit;">
                        <span style="display: inline-block; width: 1.5rem; height: 1.5rem; border-radius: 0.375rem; background: {{ $tag->color }}; border: 2px solid rgba(0,0,0,0.1);"></span>
                        <span style="font-weight: 600; color: var(--ds-text); font-size: 1rem;">{{ $tag->name }}</span>
                    </a>
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <a href="{{ route('tags.edit', $tag) }}" class="ds-btn ds-btn-outline">
                            @include('components._icons', ['name' => 'pencil', 'class' => 'w-4 h-4'])
                            ویرایش
                        </a>
                        <form action="{{ route('tags.destroy', $tag) }}" method="post" style="display: inline;" onsubmit="return confirm('این برچسب حذف شود؟');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="ds-btn ds-btn-danger">
                                @include('components._icons', ['name' => 'trash', 'class' => 'w-4 h-4'])
                                حذف
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
