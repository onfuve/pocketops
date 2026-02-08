<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, viewport-fit=cover">
    <title>@yield('title', config('app.name'))</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    @include('components._design-system')
    @stack('styles')
</head>
<body class="min-h-screen antialiased pb-[env(safe-area-inset-bottom)] pl-[env(safe-area-inset-left)] pr-[env(safe-area-inset-right)]" style="font-family: var(--ds-font); color: var(--ds-text); background: linear-gradient(to bottom, #fafaf9 0%, #f5f5f4 100%);">
    <main style="padding: 1.5rem 0 2.5rem; box-sizing: border-box;">
        @yield('content')
    </main>
    @stack('scripts')
</body>
</html>
