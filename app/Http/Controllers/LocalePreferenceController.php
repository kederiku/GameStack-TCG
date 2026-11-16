<?php

namespace App\Http\Controllers;

use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class LocalePreferenceController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'locale' => ['required', 'string', Rule::in(array_keys(Locales::all()))],
            'route' => ['required', 'string', 'max:100'],
            'target' => ['required', 'string', 'max:4096'],
        ]);
        $target = $data['target'];
        $parts = parse_url($target);
        if ($parts === false || ! str_starts_with($target, '/') || str_starts_with($target, '//')
            || preg_match('/[\\\\\x00-\x20]/', $target) || isset($parts['host']) || isset($parts['scheme'])) {
            throw ValidationException::withMessages(['target' => __('validation.url', ['attribute' => 'target'])]);
        }
        $destination = Request::create($target);
        try {
            $route = Route::getRoutes()->match($destination);
        } catch (HttpExceptionInterface) {
            throw ValidationException::withMessages(['target' => __('validation.url', ['attribute' => 'target'])]);
        }
        if ($route->getName() !== 'site.'.$data['locale'].'.'.$data['route']
            || $destination->has('signature') || in_array('signed', $route->gatherMiddleware(), true)
            || ! LocalizedRoutes::isPage($data['route'])) {
            throw ValidationException::withMessages(['target' => __('validation.url', ['attribute' => 'target'])]);
        }
        // Rebuild from the matched identity, never redirect to caller-supplied arbitrary locations.
        $parameters = $route->parameters();
        unset($parameters['_locale']);
        $destination->setRouteResolver(fn () => $route);
        $url = LocalizedRoutes::url($data['route'], [...$parameters, ...LocalizedRoutes::query($destination)], $data['locale']);
        if (isset($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }
        $request->user()?->forceFill(['preferred_locale' => $data['locale']])->save();

        // Inertia replaces fragment redirects; queue onto the final response in the outer cookie middleware.
        Cookie::queue(cookie('site_locale', $data['locale'], 60 * 24 * 365, sameSite: 'lax'));

        return redirect($url, 303)
            ->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
