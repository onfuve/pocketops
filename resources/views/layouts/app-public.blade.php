<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>
    <link rel="manifest" href="{{ url('manifest.json') }}">
    <meta name="theme-color" content="#059669">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="{{ config('app.name') }}">
    <link rel="apple-touch-icon" href="{{ asset('pwa/icons/icon-192.png') }}">
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    @include('components._design-system')
    @stack('styles')
</head>
<body class="min-h-screen antialiased pb-[env(safe-area-inset-bottom)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)]" style="font-family: var(--ds-font); color: var(--ds-text); background: #f8faf8;">
    <main style="padding: 1rem 0.75rem 1.5rem; box-sizing: border-box; max-width: 28rem; margin: 0 auto;">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
