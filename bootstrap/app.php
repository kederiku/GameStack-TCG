<?php

use App\Http\Middleware\HandleAppearance;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\NormalizeLocalizedRedirects;
use App\Http\Middleware\SetSiteLocale;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Routing\Middleware\ValidateSignature;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn (Request $request) => LocalizedRoutes::url('login'));
        $middleware->redirectUsersTo(fn (Request $request) => LocalizedRoutes::url('dashboard'));

        $middleware->encryptCookies(except: ['appearance', 'sidebar_state']);

        $middleware->prependToPriorityList(AuthenticatesRequests::class, SetSiteLocale::class);

        $middleware->prependToPriorityList(AuthenticatesRequests::class, ValidateSignature::class);

        $middleware->web(append: [
            SetSiteLocale::class,
            HandleAppearance::class,
            HandleInertiaRequests::class,
            NormalizeLocalizedRedirects::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
