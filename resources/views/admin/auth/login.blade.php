<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('auth.login.title') }} - {{ config('app.name') }}</title>
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
</head>
<body class="login-page bg-body-secondary">
    <div class="login-box">
        <div class="login-logo">
            <span class="fw-light">Egati</span>
        </div>

        <div class="card">
            <div class="card-body login-card-body">
                <p class="login-box-msg">เข้าสู่ระบบ Admin</p>

                @if (session('idle_timeout_message'))
                    <div class="alert alert-warning" role="alert">{{ session('idle_timeout_message') }}</div>
                @endif
                <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                    @csrf

                    <div class="input-group mb-3">
                        <input type="email"
                               name="email"
                               value="{{ old('email', $email ?? '') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               placeholder="{{ __('auth.login.email') }}"
                               autocomplete="username"
                               required
                               autofocus>
                        <div class="input-group-text"><span class="bi bi-envelope"></span></div>
                    </div>
                    @error('email')
                        <div class="text-danger small mb-3" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="input-group mb-3">
                        <input type="password"
                               name="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="{{ __('auth.login.password') }}"
                               autocomplete="current-password"
                               required>
                        <div class="input-group-text"><span class="bi bi-lock-fill"></span></div>
                    </div>
                    @error('password')
                        <div class="text-danger small mb-3" role="alert">{{ $message }}</div>
                    @enderror

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary">{{ __('auth.login.submit') }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
