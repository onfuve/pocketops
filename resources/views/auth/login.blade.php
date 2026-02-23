<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>ورود — {{ config('app.name') }}</title>
    <link href="{{ asset('vendor/fonts/vazirmatn/vazirmatn.css') }}" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --login-bg-start: #0f172a;
            --login-bg-end: #1e293b;
            --login-card-bg: #ffffff;
            --login-primary: #059669;
            --login-primary-hover: #047857;
            --login-accent: #38bdf8;
            --login-text: #0f172a;
            --login-muted: #64748b;
            --login-border: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            font-family: 'Vazirmatn', sans-serif;
            min-height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            background: linear-gradient(145deg, var(--login-bg-start) 0%, var(--login-bg-end) 50%, #0c4a6e 100%);
            position: relative;
            overflow-x: hidden;
        }
        body::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% -20%, rgba(56, 189, 248, 0.15), transparent),
                        radial-gradient(ellipse 60% 40% at 100% 50%, rgba(5, 150, 105, 0.08), transparent);
            pointer-events: none;
        }
        .login-wrap {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 28rem;
        }
        .login-card {
            background: var(--login-card-bg);
            border-radius: 1.25rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(255, 255, 255, 0.05) inset;
            padding: 2.25rem 2rem;
        }
        .login-brand {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin-bottom: 1.75rem;
        }
        .login-brand-icon {
            width: 3rem;
            height: 3rem;
            border-radius: 1rem;
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: var(--login-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        .login-brand-icon svg { width: 1.75rem; height: 1.75rem; }
        .login-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--login-text);
            margin: 0;
            text-align: center;
        }
        .login-subtitle {
            font-size: 0.875rem;
            color: var(--login-muted);
            text-align: center;
            margin: 0.5rem 0 0 0;
        }
        .login-alert {
            padding: 0.875rem 1rem;
            margin-bottom: 1.25rem;
            border-radius: 0.75rem;
            font-size: 0.875rem;
            display: flex;
            align-items: flex-start;
            gap: 0.5rem;
        }
        .login-alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            color: #b91c1c;
        }
        .login-alert svg { flex-shrink: 0; margin-top: 0.125rem; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--login-text);
            margin-bottom: 0.5rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 2px solid var(--login-border);
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 1rem;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: var(--login-primary);
            box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15);
        }
        .form-group input::placeholder { color: #94a3b8; }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.5rem; }
        .checkbox-group input { width: 1.125rem; height: 1.125rem; accent-color: var(--login-primary); }
        .checkbox-group label { margin-bottom: 0; font-weight: 500; color: var(--login-muted); font-size: 0.875rem; }
        .btn-login {
            width: 100%;
            padding: 0.875rem 1.25rem;
            background: linear-gradient(135deg, var(--login-primary) 0%, var(--login-primary-hover) 100%);
            color: #fff;
            border: none;
            border-radius: 0.75rem;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
            box-shadow: 0 4px 14px rgba(5, 150, 105, 0.35);
        }
        .btn-login:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 20px rgba(5, 150, 105, 0.4);
        }
        .btn-login:active { transform: translateY(0); }
    </style>
</head>
<body>
    <div class="login-wrap">
        <div class="login-card">
            <div class="login-brand">
                <div class="login-brand-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
            </div>
            <h1 class="login-title">ورود به {{ config('app.name') }}</h1>
            <p class="login-subtitle">برای ادامه وارد حساب خود شوید</p>

            @if (session('error'))
                <div class="login-alert login-alert-error" role="alert">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            @if ($errors->any())
                <div class="login-alert login-alert-error" role="alert">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p style="margin: 0;">{{ $error }}</p>
                        @endforeach
                    </div>
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
                    <input type="password" name="password" id="password" required autocomplete="current-password" placeholder="••••••••">
                </div>
                <div class="checkbox-group">
                    <input type="checkbox" name="remember" id="remember">
                    <label for="remember">مرا به خاطر بسپار</label>
                </div>
                <button type="submit" class="btn-login">ورود</button>
            </form>
        </div>
    </div>
</body>
</html>
