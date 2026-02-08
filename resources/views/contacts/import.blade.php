@extends('layouts.app')

@section('title', 'ورود CSV — ' . config('app.name'))

@section('content')
    <h1 class="mb-6 text-xl font-semibold text-stone-800 sm:text-2xl">ورود مخاطبین از CSV</h1>

    <div class="rounded-xl border border-stone-200 bg-white p-6 shadow-sm sm:p-8">
        <p class="mb-4 text-sm text-stone-600">
            فایل CSV باید ستون اول با عنوان <strong>نام</strong> باشد. برای نمونه، یک بار «خروجی CSV» را از لیست مخاطبین بگیرید و همان فرمت را برای ورود استفاده کنید.
        </p>
        <form action="{{ route('contacts.import.store') }}" method="post" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <div>
                <label for="file" class="mb-1 block text-sm font-medium text-stone-700">فایل CSV</label>
                <input type="file" name="file" id="file" accept=".csv,.txt" required
                       class="w-full rounded-lg border border-stone-300 px-3 py-2 text-sm text-stone-800 file:mr-4 file:rounded file:border-0 file:bg-stone-100 file:px-3 file:py-1.5 file:text-stone-700">
                @error('file')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2.5 text-sm font-medium text-white hover:bg-stone-700 btn-touch">
                    ورود فایل
                </button>
                <a href="{{ route('contacts.index') }}" class="rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-medium text-stone-700 hover:bg-stone-50 btn-touch inline-flex items-center">
                    انصراف
                </a>
            </div>
        </form>
    </div>
@endsection
