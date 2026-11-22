<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Helpers\WebTranslator::meta(app()->getLocale())['direction'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name', 'Laravel'))</title>
    @vite(['resources/sass/web.scss', 'resources/js/web.js'])
</head>
<body>    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="{{ url(app()->getLocale()) }}">{{ config('app.name', 'Laravel') }}</a>

            <ul class="navbar-nav flex-row ms-auto">
                <li class="nav-item"><a class="nav-link" href="{{ url(app()->getLocale()) }}">{{ __web('nav.home') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#">{{ __web('nav.news') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#">{{ __web('nav.about') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="#">{{ __web('nav.contact') }}</a></li>
            </ul>

            <div class="ms-3 dropdown">
                <button type="button" class="btn btn-outline-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                    {{ $currentLanguage?->native_name ?? strtoupper(app()->getLocale()) }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    @foreach ($webLanguages as $language)
                        <li>
                            <a class="dropdown-item {{ app()->getLocale() === $language->code ? 'active' : '' }}" href="{{ url($language->code) }}">
                                {{ $language->native_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </nav>
    <main class="container py-5">
        @yield('content')
    </main>

    <footer class="container py-4 text-center text-muted border-top">
        &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}
    </footer>
</body>
</html>