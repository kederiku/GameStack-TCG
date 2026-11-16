<?php

namespace App\Http\Middleware;

use App\Support\Localization\LocalizedRoutes;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class NormalizeLocalizedRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        if (! $response instanceof RedirectResponse) {
            return $response;
        }
        $target = $response->getTargetUrl();
        $parts = parse_url($target);
        $origin = ['host' => $request->getHost(), 'scheme' => $request->getScheme(), 'port' => parse_url($request->getSchemeAndHttpHost(), PHP_URL_PORT)];
        if ($parts === false || preg_match('/[\\\\\x00-\x20]/', $target)
            || isset($parts['user']) || isset($parts['pass'])
            || (isset($parts['host']) && ($parts['host'] !== $origin['host']
                || ($parts['scheme'] ?? null) !== $origin['scheme'] || ($parts['port'] ?? null) !== ($origin['port'] ?? null)))) {
            return $response;
        }
        $destination = Request::create($target);
        if ($destination->has('signature')) {
            return $response;
        }
        try {
            $route = Route::getRoutes()->match($destination);
        } catch (HttpExceptionInterface) {
            return $response;
        }
        $key = $route->getName();
        // Already canonical intended destinations keep their own URL locale.
        if (! is_string($key) || ! Route::has('site.'.LocalizedRoutes::locale($request).'.'.$key)
            || in_array('signed', $route->gatherMiddleware(), true)) {
            return $response;
        }
        $parameters = $route->parameters();
        unset($parameters['_locale']);
        $query = $destination->query();
        unset($query['lang']);
        $url = LocalizedRoutes::url($key, [...$query, ...$parameters], LocalizedRoutes::locale($request));
        if (isset($parts['fragment'])) {
            $url .= '#'.$parts['fragment'];
        }
        $response->setTargetUrl($url);

        return $response;
    }
}
