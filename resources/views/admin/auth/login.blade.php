<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>{{ __('auth.login.title') }} - {{ config('app.name') }}</title>
    @vite(['resources/sass/admin.scss', 'resources/js/admin.js'])
</head>
<body class="login-page">
    <div class="login-wrapper">
        <div class="login-box">
            <div class="card login-card shadow-sm">
                <div class="login-card-header">
                    <h1 class="login-brand-title">ระบบผู้ดูแลเว็บไซต์</h1>
                    <p class="login-brand-subtitle">บริษัท กฟผ. อินเตอร์เนชั่นแนล จำกัด</p>
                </div>

                <div class="card-body login-card-body">
                    <div class="login-intro">
                        <div class="login-icon" aria-hidden="true">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <div class="login-intro-text">
                            <h2 class="login-heading">เข้าสู่ระบบ</h2>
                            <p class="login-subheading">กรุณาป้อนบัญชีผู้ใช้และรหัสผ่านเพื่อดำเนินการต่อ</p>
                        </div>
                    </div>

                    @if (session('idle_timeout_message'))
                        <div class="alert alert-warning" role="alert">{{ session('idle_timeout_message') }}</div>
                    @endif

                    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label login-label">Email</label>
                            <input type="email"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $email ?? '') }}"
                                   class="form-control login-input @error('email') is-invalid @enderror"
                                   autocomplete="username"
                                   required
                                   autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label login-label">Password</label>
                            <div class="input-group login-password-group">
                                <input type="password"
                                       id="password"
                                       name="password"
                                       class="form-control login-input @error('password') is-invalid @enderror"
                                       autocomplete="current-password"
                                       required>
                                <button type="button"
                                        class="btn login-password-toggle"
                                        data-password-toggle="#password"
                                        aria-label="แสดง/ซ่อนรหัสผ่าน">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check login-remember mb-4">
                            <input class="form-check-input" type="checkbox" value="1" id="remember" name="remember">
                            <label class="form-check-label" for="remember">จดจำฉันไว้</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn login-submit">
                                {{ __('auth.login.submit') }}
                                <i class="bi bi-arrow-right ms-1"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <p class="login-footer-text">
                ระบบบริหารจัดการเว็บไซต์ &middot; สงวนสิทธิ์สำหรับผู้ดูแลระบบ
            </p>
        </div>
    </div>
</body>
</html>
