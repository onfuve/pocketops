<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Fallback: ensure key colors and navigation always work --}}
    <style>
        .btn-primary, a.btn-primary { background-color: #059669 !important; color: #fff !important; }
        .btn-primary:hover, a.btn-primary:hover { background-color: #047857 !important; }
        .nav-link:hover { background-color: #ecfdf5 !important; color: #047857 !important; }
        .badge-primary { background-color: #d1fae5 !important; color: #065f46 !important; }
        .badge-amber { background-color: #fef3c7 !important; color: #92400e !important; }
        /* Nav always visible - no dependency on Tailwind breakpoints */
        .main-nav { display: flex !important; flex-wrap: wrap; align-items: center; gap: 0.25rem; }
        .main-nav a { min-height: 44px; padding: 0.5rem 0.75rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; color: #57534e; }
        .main-nav a:hover { background-color: #ecfdf5; color: #047857; }
    </style>
    @include('components._design-system')
    @stack('styles')
</head>
<body class="font-vazir min-h-screen text-stone-800 antialiased pb-[env(safe-area-inset-bottom)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)]" style="background: linear-gradient(to bottom, #fafaf9 0%, #f5f5f4 100%);">
    <header class="sticky top-0 z-10 border-b bg-white pt-[env(safe-area-inset-top)]" style="border-color: #e7e5e4; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="max-width: 56rem; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem 1rem;">
            <a href="{{ route('contacts.index') }}" class="flex items-center gap-2 shrink-0 rounded-xl px-2 py-1.5 text-base font-bold no-underline transition" style="color: #292524;" onmouseover="this.style.backgroundColor='#ecfdf5';this.style.color='#047857';" onmouseout="this.style.backgroundColor='';this.style.color='#292524';">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                <span class="hidden sm:inline">{{ config('app.name') }}</span>
            </a>
            {{-- Single nav: always visible (inline styles ensure it shows without Tailwind) --}}
            <nav class="main-nav nav-touch" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.25rem;">
                <a href="{{ route('contacts.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4 shrink-0'])
                    <span>مخاطبین</span>
                </a>
                <a href="{{ route('invoices.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                    <span>فاکتورها</span>
                </a>
                <a href="{{ route('leads.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4 shrink-0'])
                    <span>سرنخ‌ها</span>
                </a>
                <a href="{{ route('calendar.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0'])
                    <span>تقویم</span>
                </a>
                <a href="{{ route('tasks.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0'])
                    <span>وظایف</span>
                </a>
                <a href="{{ route('contacts.create') }}" style="display: inline-flex; align-items: center; gap: 0.5rem; min-height: 44px; padding: 0.5rem 1rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 600; color: #fff; background-color: #059669; text-decoration: none; margin-right: 0.5rem;">
                    @include('components._icons', ['name' => 'user-plus', 'class' => 'w-4 h-4'])
                    <span>مخاطب جدید</span>
                </a>
                <a href="{{ route('contacts.import') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4 shrink-0'])
                    <span>ورود CSV</span>
                </a>
                <span style="width: 1px; height: 1.5rem; background: #e7e5e4; margin: 0 0.5rem;" aria-hidden="true"></span>
                <a href="{{ route('settings.company') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>تنظیمات شرکت</span>
                </a>
                <a href="{{ route('settings.lead-channels') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'cog', 'class' => 'w-4 h-4 shrink-0'])
                    <span>کانال سرنخ</span>
                </a>
                <a href="{{ route('tags.index') }}" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                    @include('components._icons', ['name' => 'tag', 'class' => 'w-4 h-4 shrink-0'])
                    <span>برچسب‌ها</span>
                </a>
                @auth
                    <form action="{{ route('logout') }}" method="POST" class="inline" style="margin-right: 0.5rem;">
                        @csrf
                        <button type="submit" class="nav-link rounded-xl" style="display: inline-flex; align-items: center; gap: 0.5rem; background: none; border: none; cursor: pointer; font-family: inherit; font-size: inherit;">
                            @include('components._icons', ['name' => 'logout', 'class' => 'w-4 h-4 shrink-0'])
                            <span>خروج ({{ auth()->user()->name }})</span>
                        </button>
                    </form>
                @endauth
            </nav>
        </div>
    </header>

    <main class="mx-auto max-w-4xl px-4 py-6 sm:px-6 sm:py-8 pb-[max(2rem,env(safe-area-inset-bottom))]">
        @if (session('success'))
            <div class="mb-4 flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm" role="alert" style="border-color: #a7f3d0; background-color: #ecfdf5; color: #065f46;">
                @include('components._icons', ['name' => 'check', 'class' => 'w-5 h-5 shrink-0'])
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-4 flex items-center gap-3 rounded-2xl border px-4 py-3 text-sm shadow-sm" role="alert" style="border-color: #fecaca; background-color: #fef2f2; color: #b91c1c;">
                @include('components._icons', ['name' => 'x', 'class' => 'w-5 h-5 shrink-0'])
                {{ session('error') }}
            </div>
        @endif
        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
