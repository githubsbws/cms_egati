<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Back office language: only th and en, chosen by the user and kept in the session.
 * The public site will pick its language from the URL in its own middleware, which runs later.
 */
class SetAdminLocale
{
    public const SUPPORTED = ['th', 'en'];

    public const DEFAULT = 'th';

    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->session()->get('locale');

        app()->setLocale(in_array($locale, self::SUPPORTED, true) ? $locale : self::DEFAULT);

        return $next($request);
    }
}
