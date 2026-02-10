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
        /* Desktop nav */
        .main-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; row-gap: 0.25rem; }
        .nav-drawer-backdrop { display: none; }
        .nav-drawer-panel { display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem; row-gap: 0.25rem; }
        .nav-group { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .nav-group + .nav-group { padding-right: 0.5rem; margin-right: 0.5rem; border-right: 1px solid #e7e5e4; }
        .nav-group + .nav-group:last-of-type { border-right: none; margin-right: 0; padding-right: 0; }
        .nav-group-label { display: none; }
        .main-nav a, .main-nav button { min-height: 44px; padding: 0.5rem 0.75rem; border-radius: 0.75rem; font-size: 0.875rem; font-weight: 500; text-decoration: none; color: #57534e; }
        .main-nav a:hover, .main-nav button:hover { background-color: #ecfdf5; color: #047857; }
        /* Mobile: hamburger + drawer. JS adds body.mobile-nav when width <= 768 (see inline script). */
        .nav-hamburger { display: none; width: 44px; height: 44px; align-items: center; justify-content: center; border: none; border-radius: 0.75rem; background: transparent; cursor: pointer; color: #57534e; }
        body.mobile-nav .nav-hamburger { display: flex !important; }
        body.mobile-nav .main-nav { display: none !important; }
        body.mobile-nav .main-nav.mobile-open { display: flex !important; flex-direction: row; align-items: stretch; position: fixed; inset: 0; z-index: 50; background: rgba(0,0,0,0.4); padding: 0; gap: 0; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-backdrop { display: block !important; flex: 1; min-width: 0; cursor: pointer; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel { display: flex !important; flex-direction: column; width: min(85%, 20rem); max-height: 100%; margin-right: auto; background: #fff; box-shadow: 4px 0 20px rgba(0,0,0,0.15); overflow-y: auto; padding: 1rem 0; gap: 0; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel a, body.mobile-nav .main-nav.mobile-open .nav-drawer-panel button { justify-content: flex-start; padding: 0.875rem 1.25rem; border-radius: 0; min-height: 48px; width: 100%; text-align: right; border-right: none; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-cta { margin: 0.5rem 1.25rem; border-radius: 0.75rem; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group { flex-direction: column; align-items: stretch; padding: 0; margin: 0; border: none; gap: 0; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group + .nav-group { padding-top: 0.5rem; margin-top: 0.5rem; border-top: 1px solid #e7e5e4; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group-label { display: block !important; padding: 0.75rem 1.25rem 0.25rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #a8a29e; }
        body.nav-drawer-open { overflow: hidden; }
    </style>
    @include('components._design-system')
    @stack('styles')
</head>
<body class="font-vazir min-h-screen text-stone-800 antialiased pb-[env(safe-area-inset-bottom)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)]" style="background: linear-gradient(to bottom, #fafaf9 0%, #f5f5f4 100%);">
    <script>
    (function(){var w=document.documentElement.clientWidth||window.innerWidth;document.body.classList.toggle('mobile-nav',w<=768);window.addEventListener('resize',function(){var w=document.documentElement.clientWidth||window.innerWidth;document.body.classList.toggle('mobile-nav',w<=768);});})();
    </script>
    <header class="sticky top-0 z-10 border-b bg-white pt-[env(safe-area-inset-top)]" style="border-color: #e7e5e4; box-shadow: 0 1px 3px rgba(0,0,0,0.06);">
        <div style="max-width: 64rem; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; padding: 0.75rem 1rem;">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-2 shrink-0 rounded-xl px-2 py-1.5 text-base font-bold no-underline transition" style="color: #292524;" onmouseover="this.style.backgroundColor='#ecfdf5';this.style.color='#047857';" onmouseout="this.style.backgroundColor='';this.style.color='#292524';">
                <span class="flex h-9 w-9 items-center justify-center rounded-lg" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-5 h-5'])
                </span>
                <span>{{ config('app.name') }}</span>
            </a>
            {{-- Hamburger: visible only on mobile --}}
            <button type="button" class="nav-hamburger nav-touch" id="nav-hamburger" aria-label="منو" title="منو">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            {{-- Nav: desktop = inline, mobile = drawer --}}
            <nav class="main-nav nav-touch" id="main-nav" aria-label="منوی اصلی">
                <span class="nav-drawer-backdrop" id="nav-drawer-backdrop" aria-hidden="true"></span>
                <div class="nav-drawer-panel">
                    {{-- Core: Dashboard, Contacts, Invoices, Leads --}}
                    <div class="nav-group">
                        <span class="nav-group-label">اصلی</span>
                        <a href="{{ route('dashboard') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4 shrink-0'])
                            <span>داشبورد</span>
                        </a>
                        <a href="{{ route('contacts.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4 shrink-0'])
                            <span>مخاطبین</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>فاکتورها</span>
                        </a>
                        <a href="{{ route('leads.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4 shrink-0'])
                            <span>سرنخ‌ها</span>
                        </a>
                    </div>
                    {{-- Planning --}}
                    <div class="nav-group">
                        <span class="nav-group-label">برنامه‌ریزی</span>
                        <a href="{{ route('calendar.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0'])
                            <span>تقویم</span>
                        </a>
                        <a href="{{ route('tasks.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0'])
                            <span>وظایف</span>
                        </a>
                    </div>
                    {{-- Products --}}
                    <div class="nav-group">
                        <span class="nav-group-label">کالا و فروش</span>
                        <a href="{{ route('products.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0'])
                            <span>کالاها و خدمات</span>
                        </a>
                        <a href="{{ route('price-lists.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>لیست قیمت</span>
                        </a>
                        <a href="{{ route('product-landing-pages.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0'])
                            <span>صفحه فرود</span>
                        </a>
                    </div>
                    {{-- Settings --}}
                    <div class="nav-group">
                        <span class="nav-group-label">تنظیمات</span>
                        <a href="{{ route('tags.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'tag', 'class' => 'w-4 h-4 shrink-0'])
                            <span>برچسب‌ها</span>
                        </a>
                        <a href="{{ route('contacts.import') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4 shrink-0'])
                            <span>ورود CSV</span>
                        </a>
                        <a href="{{ route('settings.lead-channels') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'cog', 'class' => 'w-4 h-4 shrink-0'])
                            <span>کانال سرنخ</span>
                        </a>
                        <a href="{{ route('forms.index') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>فرم‌های جمع‌آوری</span>
                        </a>
                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('settings.company') }}" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>تنظیمات شرکت</span>
                        </a>
                        @endif
                    </div>
                    {{-- Actions --}}
                    <div class="nav-group">
                        <span class="nav-group-label">عملیات</span>
                        <a href="{{ route('contacts.create') }}" class="nav-link nav-cta" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #fff !important; background-color: #059669 !important;">
                            @include('components._icons', ['name' => 'user-plus', 'class' => 'w-4 h-4'])
                            <span>مخاطب جدید</span>
                        </a>
                        @auth
                        <form action="{{ route('logout') }}" method="POST" style="margin: 0; display: inline-flex;">
                            @csrf
                            <button type="submit" class="nav-link" style="display: inline-flex; align-items: center; gap: 0.5rem; width: 100%; background: none; border: none; cursor: pointer; font-family: inherit; font-size: inherit; text-align: right; padding: 0.5rem 0.75rem;">
                                @include('components._icons', ['name' => 'logout', 'class' => 'w-4 h-4 shrink-0'])
                                <span>خروج ({{ auth()->user()->name }})</span>
                            </button>
                        </form>
                        @endauth
                    </div>
                </div>
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

    <script>
    (function() {
        var hamburger = document.getElementById('nav-hamburger');
        var nav = document.getElementById('main-nav');
        var backdrop = document.getElementById('nav-drawer-backdrop');
        function openNav() {
            nav.classList.add('mobile-open');
            document.body.classList.add('nav-drawer-open');
        }
        function closeNav() {
            nav.classList.remove('mobile-open');
            document.body.classList.remove('nav-drawer-open');
        }
        if (hamburger) hamburger.addEventListener('click', openNav);
        if (backdrop) backdrop.addEventListener('click', closeNav);
    })();
    </script>
    @stack('scripts')
</body>
</html>
