<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    {{-- PWA: install on home screen (Android & iOS) --}}
    <link rel="manifest" href="{{ url('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icons/icon-192.png') }}">
    {{-- Non-blocking font: Safari (and others) won't wait for 21 @font-face rules before first paint --}}
    <link rel="preload" href="{{ asset('vendor/fonts/vazirmatn/vazirmatn-arabic.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet"></noscript>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{-- Fallback: ensure key colors and navigation always work --}}
    <style>
        .btn-primary, a.btn-primary { background-color: #059669 !important; color: #fff !important; }
        .btn-primary:hover, a.btn-primary:hover { background-color: #047857 !important; }
        .nav-link:hover { background-color: #ecfdf5 !important; color: #047857 !important; }
        .badge-primary { background-color: #d1fae5 !important; color: #065f46 !important; }
        .badge-amber { background-color: #fef3c7 !important; color: #92400e !important; }
        /* Desktop nav - Redesigned: primary links, focus CTAs, more dropdown */
        .main-nav { display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; row-gap: 0.25rem; overflow: visible; }
        .nav-drawer-backdrop { display: none; }
        .nav-drawer-panel { display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; row-gap: 0.25rem; }
        .nav-group { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
        .nav-group + .nav-group { padding-right: 0.375rem; margin-right: 0.375rem; border-right: 1px solid #e7e5e4; }
        .nav-group + .nav-group:last-of-type { border-right: none; margin-right: 0; padding-right: 0; }
        .nav-group-label { display: none; }
        .main-nav a, .main-nav button { min-height: 36px; padding: 0.375rem 0.625rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; text-decoration: none; color: #57534e; transition: all 0.15s; }
        .main-nav a:hover, .main-nav button:hover { background-color: #ecfdf5; color: #047857; }
        .main-nav a.nav-link-icon-only { padding: 0.375rem; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; }
        .main-nav a.nav-link-icon-only span { display: none; }
        @media (min-width: 1024px) {
          .main-nav a.nav-link-icon-only span { display: inline; }
          .main-nav a.nav-link-icon-only { width: auto; padding: 0.375rem 0.625rem; }
        }
        .logo-text { display: none !important; }
        @media (min-width: 640px) {
          .logo-text { display: inline !important; }
        }
        @keyframes pulse-subtle { 0%, 100% { opacity: 1; } 50% { opacity: 0.95; } }
        .nav-link-new-lead { background: linear-gradient(135deg, #f59e0b, #d97706) !important; border-color: #d97706 !important; color: #fff !important; box-shadow: 0 2px 8px rgba(245, 158, 11, 0.3) !important; font-weight: 700 !important; font-size: 0.8125rem !important; padding: 0.375rem 0.75rem !important; min-height: 36px !important; animation: pulse-subtle 2s ease-in-out infinite; }
        .nav-link-new-lead:hover { background: linear-gradient(135deg, #d97706, #b45309) !important; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4) !important; color: #fff !important; }
        .nav-link-new-lead span { display: none; }
        @media (min-width: 1024px) {
          .nav-link-new-lead span { display: inline; }
        }
        /* Desktop (lg): rearranged layout — primary | CTAs (focus) | more dropdown | logout */
        @media (min-width: 1024px) {
          .nav-drawer-panel { flex-wrap: nowrap; gap: 0; align-items: center; }
          .nav-group { padding: 0; margin: 0; border: none; }
          .nav-group.nav-group-primary { display: flex; align-items: center; gap: 0.125rem; }
          .nav-group.nav-group-ctas { display: flex; align-items: center; gap: 0.5rem; margin-right: 0.75rem; padding-right: 0.75rem; border-right: 1px solid #e7e5e4; }
          .nav-group.nav-group-more { position: relative; }
          .nav-group.nav-group-end { display: flex; align-items: center; gap: 0.25rem; margin-right: auto; }
          .main-nav .nav-link { border-radius: 0.5rem; padding: 0.5rem 0.75rem; font-size: 0.8125rem; min-height: 38px; }
          .main-nav .nav-link-new-lead { padding: 0.5rem 1rem; min-height: 40px; border-radius: 0.5rem; margin: 0; }
          .main-nav .nav-cta { padding: 0.5rem 1rem; min-height: 40px; border-radius: 0.5rem; font-weight: 600; box-shadow: 0 2px 6px rgba(5,150,105,0.25); }
          .main-nav .nav-cta:hover { box-shadow: 0 4px 10px rgba(5,150,105,0.35); transform: translateY(-1px); }
          .nav-more-trigger { display: inline-flex; align-items: center; gap: 0.375rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8125rem; font-weight: 500; color: #57534e; background: transparent; border: none; cursor: pointer; font-family: inherit; min-height: 38px; }
          .nav-more-trigger:hover { background: #ecfdf5; color: #047857; }
          .nav-more-dropdown { display: none; position: absolute; top: 100%; right: 0; margin-top: 0.25rem; min-width: 12rem; background: #fff; border: 1px solid #e7e5e4; border-radius: 0.75rem; box-shadow: 0 10px 40px rgba(0,0,0,0.1); padding: 0.5rem; z-index: 100; }
          .nav-group-more:hover .nav-more-dropdown { display: block; }
          .nav-more-dropdown .nav-dropdown-section { padding: 0.25rem 0; }
          .nav-more-dropdown .nav-dropdown-section:not(:first-child) { border-top: 1px solid #e7e5e4; }
          .nav-more-dropdown .nav-dropdown-label { padding: 0.35rem 0.75rem; font-size: 0.7rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; color: #78716c; }
          .nav-more-dropdown a { display: flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.75rem; border-radius: 0.5rem; font-size: 0.8125rem; color: #44403c; text-decoration: none; }
          .nav-more-dropdown a:hover { background: #ecfdf5; color: #047857; }
          .nav-group-planning, .nav-group-products, .nav-group-settings { display: none !important; }
          .nav-group-more { display: flex !important; align-items: center; }
          .nav-more-trigger::after { content: ''; width: 0.4rem; height: 0.4rem; border-right: 2px solid currentColor; border-bottom: 2px solid currentColor; transform: rotate(45deg); margin-right: 0.25rem; }
        }
        @media (max-width: 1023px) {
          .nav-group-more { display: none !important; }
        }
        /* Mobile: hamburger + full-height drawer. Labels always visible for clarity. */
        .nav-hamburger { display: none; width: 44px; height: 44px; align-items: center; justify-content: center; border: none; border-radius: 0.75rem; background: transparent; cursor: pointer; color: #57534e; }
        body.mobile-nav .nav-hamburger { display: flex !important; }
        body.mobile-nav .main-nav { display: none !important; }
        body.mobile-nav .main-nav.mobile-open { display: flex !important; flex-direction: row; align-items: stretch; position: fixed; inset: 0; z-index: 50; background: rgba(15,23,42,0.35); padding: 0; gap: 0; backdrop-filter: blur(4px); }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-backdrop { display: block !important; flex: 1; min-width: 0; cursor: pointer; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel { display: flex !important; flex-direction: column; width: min(88%, 22rem); max-height: 100%; margin-right: auto; background: #fff; box-shadow: -4px 0 24px rgba(0,0,0,0.12); overflow-y: auto; padding: 0; gap: 0; border-radius: 1rem 0 0 1rem; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel a, body.mobile-nav .main-nav.mobile-open .nav-drawer-panel button { justify-content: flex-start; padding: 0.875rem 1.25rem; min-height: 52px; width: 100%; text-align: right; border: none; border-radius: 0; font-size: 0.9375rem; font-weight: 500; color: #292524; gap: 0.75rem; display: inline-flex; align-items: center; border-right: 3px solid transparent; transition: background 0.2s, border-color 0.2s; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel a:hover, body.mobile-nav .main-nav.mobile-open .nav-drawer-panel button:hover { background: #f0fdf4; border-right-color: #059669; color: #047857; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel a span, body.mobile-nav .main-nav.mobile-open .nav-drawer-panel button span { display: inline !important; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-link svg, body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-link .w-4 { flex-shrink: 0; width: 1.25rem; height: 1.25rem; opacity: 0.9; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-cta { margin: 1rem 1.25rem; border-radius: 0.75rem; justify-content: center; min-height: 48px; background: #059669 !important; color: #fff !important; border-right: none !important; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-cta:hover { background: #047857 !important; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group { flex-direction: column; align-items: stretch; padding: 0; margin: 0; border: none; gap: 0; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group + .nav-group { padding-top: 0.25rem; margin-top: 0.25rem; border-top: 1px solid #e7e5e4; }
        body.mobile-nav .main-nav.mobile-open .nav-drawer-panel .nav-group-label { display: block !important; padding: 0.5rem 1.25rem 0.15rem; font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; color: #78716c; }
        body.nav-drawer-open { overflow: hidden; }
    </style>
    @include('components._design-system')
    @stack('styles')
</head>
<body class="font-vazir min-h-screen text-stone-800 antialiased pb-[env(safe-area-inset-bottom)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)]" style="background: linear-gradient(to bottom, #fafaf9 0%, #f5f5f4 100%); font-family: 'Vazirmatn', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;">
    <script>
    (function(){var w=document.documentElement.clientWidth||window.innerWidth;document.body.classList.toggle('mobile-nav',w<=768);window.addEventListener('resize',function(){var w=document.documentElement.clientWidth||window.innerWidth;document.body.classList.toggle('mobile-nav',w<=768);});})();
    </script>
    <header class="sticky top-0 z-10 border-b bg-white pt-[env(safe-area-inset-top)]" style="border-color: #e7e5e4; box-shadow: 0 1px 3px rgba(0,0,0,0.06); overflow: visible;">
        <div class="header-inner" style="max-width: 72rem; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; padding: 0.5rem 0.75rem; min-height: 3rem;">
            <a href="{{ route('dashboard') }}" class="flex items-center gap-1.5 shrink-0 rounded-lg px-1.5 py-1 text-sm font-bold no-underline transition logo-link" style="color: #292524;" onmouseover="this.style.backgroundColor='#ecfdf5';this.style.color='#047857';" onmouseout="this.style.backgroundColor='';this.style.color='#292524';" title="{{ config('app.name') }}">
                <span class="flex h-7 w-7 items-center justify-center rounded-md" style="background-color: #d1fae5; color: #047857;">
                    @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4'])
                </span>
                <span class="logo-text" style="display: none;">{{ config('app.name') }}</span>
            </a>
            {{-- Hamburger: visible only on mobile --}}
            <button type="button" class="nav-hamburger nav-touch" id="nav-hamburger" aria-label="منو" title="منو">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            {{-- Nav: desktop = primary + CTAs (focus) + more dropdown | mobile = drawer --}}
            <nav class="main-nav nav-touch" id="main-nav" aria-label="منوی اصلی">
                <span class="nav-drawer-backdrop" id="nav-drawer-backdrop" aria-hidden="true"></span>
                <div class="nav-drawer-panel">
                    {{-- 1) Primary: Dashboard, Contacts, Invoices, Leads --}}
                    <div class="nav-group nav-group-primary">
                        <span class="nav-group-label">اصلی</span>
                        <a href="{{ route('dashboard') }}" class="nav-link nav-link-icon-only" title="داشبورد" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4 shrink-0'])
                            <span>داشبورد</span>
                        </a>
                        <a href="{{ route('contacts.index') }}" class="nav-link nav-link-icon-only" title="مخاطبین" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'users', 'class' => 'w-4 h-4 shrink-0'])
                            <span>مخاطبین</span>
                        </a>
                        <a href="{{ route('invoices.index') }}" class="nav-link nav-link-icon-only" title="فاکتورها" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>فاکتورها</span>
                        </a>
                        <a href="{{ route('leads.index') }}" class="nav-link nav-link-icon-only" title="سرنخ‌ها" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'lightbulb', 'class' => 'w-4 h-4 shrink-0'])
                            <span>سرنخ‌ها</span>
                        </a>
                    </div>
                    {{-- 2) Focus CTAs: New Lead, New Contact --}}
                    <a href="{{ route('leads.create') }}" class="nav-link nav-link-new-lead" title="سرنخ جدید" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                        @include('components._icons', ['name' => 'plus', 'class' => 'w-4 h-4 shrink-0'])
                        <span>سرنخ جدید</span>
                    </a>
                    <div class="nav-group nav-group-ctas">
                        <span class="nav-group-label">عملیات</span>
                        <a href="{{ route('contacts.create') }}" class="nav-link nav-cta" style="display: inline-flex; align-items: center; gap: 0.5rem; color: #fff !important; background-color: #059669 !important;">
                            @include('components._icons', ['name' => 'user-plus', 'class' => 'w-4 h-4'])
                            <span>مخاطب جدید</span>
                        </a>
                    </div>
                    {{-- 3) Desktop only: More dropdown (sub-items) --}}
                    <div class="nav-group nav-group-more" style="display: none;">
                        <span class="nav-group-label" style="display: none;"></span>
                        <span class="nav-more-trigger" aria-haspopup="true" aria-expanded="false">بیشتر</span>
                        <div class="nav-more-dropdown" role="menu">
                            <div class="nav-dropdown-section">
                                <div class="nav-dropdown-label">برنامه‌ریزی</div>
                                <a href="{{ route('calendar.index') }}" role="menuitem">@include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0']) تقویم</a>
                                @if(auth()->user()?->canModule('subscriptions', \App\Models\User::ABILITY_VIEW))
                                <a href="{{ route('subscriptions.index') }}" role="menuitem">@include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0']) اشتراک‌ها</a>
                                @endif
                                <a href="{{ route('tasks.index') }}" role="menuitem">@include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0']) وظایف</a>
                            </div>
                            <div class="nav-dropdown-section">
                                <div class="nav-dropdown-label">کالا و فروش</div>
                                <a href="{{ route('products.index') }}" role="menuitem">@include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0']) کالاها</a>
                                <a href="{{ route('price-lists.index') }}" role="menuitem">@include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0']) قیمت</a>
                                <a href="{{ route('product-landing-pages.index') }}" role="menuitem">@include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0']) فرود</a>
                            </div>
                            <div class="nav-dropdown-section">
                                <div class="nav-dropdown-label">گزارش‌ها</div>
                                <a href="{{ route('reports.servqual') }}" role="menuitem">@include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0']) کیفیت خدمات (SERVQUAL)</a>
                            </div>
                            <div class="nav-dropdown-section">
                                <div class="nav-dropdown-label">تنظیمات</div>
                                <a href="{{ route('tags.index') }}" role="menuitem">@include('components._icons', ['name' => 'tag', 'class' => 'w-4 h-4 shrink-0']) برچسب</a>
                                <a href="{{ route('contacts.import') }}" role="menuitem">@include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4 shrink-0']) CSV</a>
                                <a href="{{ route('settings.lead-channels') }}" role="menuitem">@include('components._icons', ['name' => 'cog', 'class' => 'w-4 h-4 shrink-0']) کانال</a>
                                <a href="{{ route('forms.index') }}" role="menuitem">@include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0']) فرم</a>
                                @if(auth()->user()?->isAdmin())
                                <a href="{{ route('settings.company') }}" role="menuitem"><svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg> شرکت</a>
                                @endif
                            </div>
                        </div>
                    </div>
                    {{-- 4) Mobile: Planning, Products, Settings (hidden on desktop) --}}
                    <div class="nav-group nav-group-planning">
                        <span class="nav-group-label">برنامه‌ریزی</span>
                        <a href="{{ route('calendar.index') }}" class="nav-link nav-link-icon-only" title="تقویم" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0'])
                            <span>تقویم</span>
                        </a>
                        @if(auth()->user()?->canModule('subscriptions', \App\Models\User::ABILITY_VIEW))
                        <a href="{{ route('subscriptions.index') }}" class="nav-link nav-link-icon-only" title="اشتراک‌ها" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'calendar', 'class' => 'w-4 h-4 shrink-0'])
                            <span>اشتراک‌ها</span>
                        </a>
                        @endif
                        <a href="{{ route('tasks.index') }}" class="nav-link nav-link-icon-only" title="وظایف" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0'])
                            <span>وظایف</span>
                        </a>
                    </div>
                    <div class="nav-group nav-group-products">
                        <span class="nav-group-label">کالا و فروش</span>
                        <a href="{{ route('products.index') }}" class="nav-link nav-link-icon-only" title="کالاها و خدمات" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0'])
                            <span>کالاها</span>
                        </a>
                        <a href="{{ route('price-lists.index') }}" class="nav-link nav-link-icon-only" title="لیست قیمت" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>قیمت</span>
                        </a>
                        <a href="{{ route('product-landing-pages.index') }}" class="nav-link nav-link-icon-only" title="صفحه فرود" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'sell', 'class' => 'w-4 h-4 shrink-0'])
                            <span>فرود</span>
                        </a>
                    </div>
                    <div class="nav-group nav-group-settings">
                        <span class="nav-group-label">گزارش و تنظیمات</span>
                        <a href="{{ route('reports.servqual') }}" class="nav-link nav-link-icon-only" title="گزارش کیفیت خدمات" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'check', 'class' => 'w-4 h-4 shrink-0'])
                            <span>کیفیت خدمات (SERVQUAL)</span>
                        </a>
                        <a href="{{ route('tags.index') }}" class="nav-link nav-link-icon-only" title="برچسب‌ها" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'tag', 'class' => 'w-4 h-4 shrink-0'])
                            <span>برچسب</span>
                        </a>
                        <a href="{{ route('contacts.import') }}" class="nav-link nav-link-icon-only" title="ورود CSV" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'file-import', 'class' => 'w-4 h-4 shrink-0'])
                            <span>CSV</span>
                        </a>
                        <a href="{{ route('settings.lead-channels') }}" class="nav-link nav-link-icon-only" title="کانال سرنخ" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'cog', 'class' => 'w-4 h-4 shrink-0'])
                            <span>کانال</span>
                        </a>
                        <a href="{{ route('forms.index') }}" class="nav-link nav-link-icon-only" title="فرم‌های جمع‌آوری" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            @include('components._icons', ['name' => 'document', 'class' => 'w-4 h-4 shrink-0'])
                            <span>فرم</span>
                        </a>
                        @if(auth()->user()?->isAdmin())
                        <a href="{{ route('settings.company') }}" class="nav-link nav-link-icon-only" title="تنظیمات شرکت" style="display: inline-flex; align-items: center; gap: 0.375rem;">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            <span>شرکت</span>
                        </a>
                        @endif
                    </div>
                    {{-- 5) Logout --}}
                    <div class="nav-group nav-group-end">
                        <span class="nav-group-label" style="display: none;"></span>
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
    {{-- PWA: register service worker for "Add to Home Screen" (Android) --}}
    <script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('{{ asset('sw.js') }}', { scope: '/' }).catch(function () {});
        });
    }
    </script>
    @stack('scripts')
</body>
</html>
