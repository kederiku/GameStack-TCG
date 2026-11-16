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
            return $next($request);
        } finally {
            app()->setLocale($previous);
        }
    }
}
