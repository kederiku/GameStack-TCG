<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $requested = $request->query('lang');
        $stored = $request->cookie('site_locale');
        $locale = in_array($requested, ['fr', 'en'], true)
            ? $requested : (in_array($stored, ['fr', 'en'], true) ? $stored : 'fr');
        app()->setLocale($locale);
        $response = $next($request);
        if (in_array($requested, ['fr', 'en'], true)) {
            $response->headers->setCookie(cookie('site_locale', $locale, 60 * 24 * 365, sameSite: 'lax'));
        }

        return $response;
    }
}
