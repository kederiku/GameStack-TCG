<?php

namespace App\Http\Middleware;

use App\Support\Localization\AdminPanels;
use App\Support\Localization\Locales;
use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $previousLocale = app()->getLocale();
        $locale = AdminPanels::locale(Filament::getCurrentOrDefaultPanel()->getId());
        $request->attributes->set('site_locale', $locale);
        app()->setLocale(Locales::definition($locale)['laravelLocale']);

        try {
            return $next($request);
        } finally {
            app()->setLocale($previousLocale);
        }
    }
}
