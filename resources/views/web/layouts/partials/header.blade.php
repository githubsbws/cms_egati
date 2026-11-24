@php
    $locale = app()->getLocale();
    $chevron = '<svg class="header-chevron" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 8 5 5 5-5"/></svg>';
@endphp
<header id="site-header">
    {{-- Logo --}}
    <a href="{{ web_url('') }}" class="brand" aria-label="EGATi Home">
        <img src="{{ web_media('site.logo', 'logo.png') }}" alt="EGATi">
    </a>

    {{-- Language dropdown (switches by URL: /th/... <-> /en/...) --}}
    <div class="site-language">
        <button id="lang" type="button" aria-expanded="false" aria-controls="language-options" aria-label="{{ __web('header.language_english_choose_language') }}">
            @if ($currentLanguage?->flag)
                    <img class="language-flag" src="{{ web_file_url($currentLanguage->flag) }}" alt="">
                @endif
            <span class="language-code">{{ strtoupper($locale) }}</span>
            {!! $chevron !!}
        </button>
        <div class="language-options" id="language-options" aria-label="{{ __web('header.choose_language') }}">
            <span class="language-heading">{{ __web('header.language') }}</span>
            @foreach ($webLanguages as $language)
                <button type="button" data-language="{{ $language->code }}" data-href="{{ web_locale_url($language->code) }}" aria-pressed="{{ $language->code === $locale ? 'true' : 'false' }}">
                    @if ($language->flag)
                        <img class="language-flag" src="{{ web_file_url($language->flag) }}" alt="">
                    @endif
                    <span>{{ $language->native_name }}</span>
                    <span class="language-check" aria-hidden="true">{{ $language->code === $locale ? '✓' : '' }}</span>
                </button>
            @endforeach
        </div>
    </div>

    {{-- Main menu --}}
    <nav id="nav" aria-label="Main navigation">
        @include('web.layouts.partials.nav')
    </nav>

    {{-- Mobile menu button --}}
    <button id="responsive-menu-toggle" class="responsive-menu-toggle" type="button" aria-controls="nav" aria-expanded="false" data-label-open="{{ __web('header.open_main_menu') }}" data-label-close="{{ __web('header.close_main_menu') }}" aria-label="{{ __web('header.open_main_menu') }}">
        <span class="responsive-menu-icon" aria-hidden="true"><i></i><i></i></span>
        <span class="responsive-menu-label">{{ __web('header.menu') }}</span>
    </button>
</header>