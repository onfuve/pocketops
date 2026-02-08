<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>ورود — {{ config('app.name') }}</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Vazirmatn', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(to bottom, #fafaf9 0%, #f5f5f4 100%); }
        .login-card { max-width: 24rem; width: 100%; padding: 2rem; border-radius: 1rem; background: #fff; border: 2px solid #e7e5e4; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .login-title { font-size: 1.25rem; font-weight: 700; color: #292524; margin-bottom: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { display: block; font-size: 0.875rem; font-weight: 600; color: #44403c; margin-bottom: 0.5rem; }
        .form-group input { width: 100%; padding: 0.625rem 0.75rem; border: 2px solid #d6d3d1; border-radius: 0.5rem; font-family: inherit; font-size: 1rem; }
        .form-group input:focus { outline: none; border-color: #059669; }
        .form-group input::placeholder { color: #a8a29e; }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input { width: auto; }
        .btn-login { width: 100%; padding: 0.75rem 1rem; background: #059669; color: #fff; border: none; border-radius: 0.5rem; font-family: inherit; font-size: 1rem; font-weight: 600; cursor: pointer; }
        .btn-login:hover { background: #047857; }
        .error-text { color: #b91c1c; font-size: 0.875rem; margin-top: 0.25rem; }
    </style>
</head>
<body>
    <div class="login-card">
        <h1 class="login-title">ورود به {{ config('app.name') }}</h1>

        @if ($errors->any())
            <div style="padding: 0.75rem; margin-bottom: 1rem; background: #fef2f2; border: 1px solid #fecaca; border-radius: 0.5rem; color: #b91c1c; font-size: 0.875rem;">
                @foreach ($errors->all() as $error)
                    <p style="margin: 0;">{{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="form-group">
                <label for="email">ایمیل</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="example@domain.com">
            </div>
            <div class="form-group">
                <label for="password">رمز عبور</label>
                <input type="password" name="password" id="password" required autocomplete="current-password">
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" name="remember" id="remember">
                <label for="remember" style="margin-bottom:0;">مرا به خاطر بسپار</label>
            </div>
            <button type="submit" class="btn-login">ورود</button>
        </form>
    </div>
</body>
</html>
