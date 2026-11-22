<?php

use App\Helpers\WebTranslator;

if (! function_exists('__web')) {
    /**
     * Front office translation helper. Separate from __(), which only reads
     * lang/th and lang/en (back office). Falls back to the default locale,
     * then to the key itself, so a missing translation never breaks a page.
     */
    function __web(string $key, ?string $fallback = null): string
    {
        return WebTranslator::get($key, app()->getLocale(), $fallback);
    }
}
