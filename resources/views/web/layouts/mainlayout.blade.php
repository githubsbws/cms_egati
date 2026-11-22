<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ \App\Helpers\WebTranslator::meta(app()->getLocale())['direction'] ?? 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#003d78">
    <meta name="description" content="@yield('description', __web('site.description'))">
    <title>@yield('title', __web('site.title'))</title>
    <link rel="canonical" href="{{ url()->current() }}">
    @foreach ($webLanguages as $language)
        <link rel="alternate" hreflang="{{ $language->code }}" href="{{ web_locale_url($language->code) }}">
    @endforeach
    @vite(['resources/css/web.css', 'resources/js/web.js'])
</head>
<body class="@yield('body_class')">
    <a class="skip" href="#main">{{ __web('site.skip') }}</a>

    @include('web.layouts.partials.header')

    <main id="main">
        @yield('content')
    </main>

    @include('web.layouts.partials.footer')

    {{-- Labels for front office scripts (js.* and news.* strings) --}}
    @php
        $webStrings = array_merge(
            \App\Helpers\WebTranslator::group('js.', app()->getLocale()),
            \App\Helpers\WebTranslator::group('news.', app()->getLocale()),
        );
    @endphp
    <script>window.WEB_I18N = @json($webStrings);</script>
    @stack('scripts')
</body>
</html>