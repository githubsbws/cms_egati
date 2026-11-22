<?php

namespace App\Http\Middleware;

use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Front office language: read from the {locale} URL segment (route group prefix),
 * never from session. Each URL always renders the same language for anyone, which
 * is what SEO needs (clean per-language URLs, hreflang, sitemap per language).
 * This is separate from the back office, which uses SetAdminLocale + session.
 */
class SetWebLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        abort_unless(in_array($locale, self::activeLocales(), true), 404);

        app()->setLocale($locale);

        return $next($request);
    }

    /**
     * Active language codes, cached briefly (clear Cache key "web.active_locales" when languages change).
     *
     * @return array<int, string>
     */
    public static function activeLocales(): array
    {
        return Cache::remember('web.active_locales', 300, fn (): array => Language::active()->pluck('code')->all());
    }
}
