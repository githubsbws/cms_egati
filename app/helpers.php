<?php

use App\Helpers\WebMediaResolver;
use App\Helpers\WebTranslator;

if (! function_exists('__web')) {
    /**
     * Front office translation helper. Separate from __(), which only reads
     * lang/th and lang/en (back office). Falls back to the default locale,
     * then to the key itself, so a missing translation never breaks a page.
     */
    function __web(string $key, ?string $fallback = null, array $replace = []): string
    {
        $text = WebTranslator::get($key, app()->getLocale(), $fallback);

        return $replace === [] ? $text : strtr($text, array_combine(array_map(fn (string $name): string => ':'.$name, array_keys($replace)), array_values($replace)));
    }
}

if (! function_exists('web_media')) {
    /**
     * URL of a front office image/video slot: the file chosen in the back office
     * if there is one, otherwise the default shipped in public/web-assets.
     */
    function web_media(string $key, string $default): string
    {
        return WebMediaResolver::url($key, $default);
    }
}

if (! function_exists('web_file_url')) {
    /**
     * URL for a stored file path (web-assets/... or a public disk path), null when empty.
     */
    function web_file_url(?string $path): ?string
    {
        return WebMediaResolver::fileUrl($path);
    }
}

if (! function_exists('web_url')) {
    /**
     * Front office URL in the current language, e.g. web_url('news/my-slug') gives /th/news/my-slug.
     */
    function web_url(string $path = '', ?string $locale = null): string
    {
        return url(trim(($locale ?? app()->getLocale()).'/'.ltrim($path, '/'), '/'));
    }
}

if (! function_exists('web_locale_url')) {
    /**
     * The current page in another language: swaps the first URL segment. A page can
     * set $alternateUrls (locale => url) when the slug differs per language.
     */
    function web_locale_url(string $locale): string
    {
        $alternates = view()->shared('alternateUrls', []);
        if (isset($alternates[$locale])) {
            return $alternates[$locale];
        }
        $segments = request()->segments();
        $segments[0] = $locale;

        return url(implode('/', $segments));
    }
}
