<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name') }}</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    <style>
        body { font-family: 'Vazirmatn', sans-serif; padding: 1rem 1.25rem; color: #1c1917; font-size: 0.9375rem; line-height: 1.6; }
        table { width: 100%; border-collapse: collapse; margin-top: 0.75rem; }
        th, td { border: 1px solid #d6d3d1; padding: 0.5rem 0.65rem; text-align: right; }
        th { background: #f5f5f4; font-weight: 600; }
        .meta { color: #57534e; font-size: 0.875rem; margin-bottom: 0.5rem; }
        h1 { font-size: 1.25rem; margin: 0 0 0.5rem 0; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap; margin-bottom: 1rem;">
        <button type="button" onclick="window.print()" style="padding: 0.5rem 1rem; border-radius: 0.5rem; border: 1px solid #059669; background: #059669; color: #fff; cursor: pointer; font-family: inherit;">چاپ</button>
        <a href="javascript:history.back()" style="color: #0369a1;">← بازگشت</a>
    </div>
    @if (session('error'))
        <p style="color: #b91c1c; margin-bottom: 0.75rem;">{{ session('error') }}</p>
    @endif
    {!! $slot !!}
</body>
</html>
