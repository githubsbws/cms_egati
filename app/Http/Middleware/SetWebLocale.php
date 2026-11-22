<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
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

        app()->setLocale($locale);

        return $next($request);
    }
}
