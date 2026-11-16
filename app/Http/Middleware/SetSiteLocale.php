<?php

namespace App\Http\Middleware;

use App\Support\Localization\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Symfony\Component\HttpFoundation\Response;

class SetSiteLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $canonical = $route instanceof Route ? $route->parameter('_locale') : null;
        if ($route instanceof Route) {
            $route->forgetParameter('_locale');
        }
        $requested = $request->query('lang');
        $stored = $request->cookie('site_locale');
        $preference = $request->user()?->preferred_locale;
        if (is_string($preference) && ! Locales::supports($preference)) {
            $preference = Locales::default();
        }
        $locale = Locales::default();
        foreach ([$canonical, $requested, $preference, $stored] as $candidate) {
            if (is_string($candidate) && Locales::supports($candidate)) {
                $locale = $candidate;
                break;
            }
        }
        $request->attributes->set('site_locale', $locale);
        $previous = app()->getLocale();
        app()->setLocale(Locales::definition($locale)['laravelLocale']);
        try {
            $response = $next($request);
            // Historical lang links may retain their cookie behavior, never an account write.
            if ($canonical === null && is_string($requested) && Locales::supports($requested)) {
                $response->headers->setCookie(cookie('site_locale', $locale, 60 * 24 * 365, sameSite: 'lax'));
            }

            return $response;
        } finally {
            app()->setLocale($previous);
        }
    }
}
