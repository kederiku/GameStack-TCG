<?php

namespace App\Support\Localization;

use App\Models\Expansion;
use Illuminate\Http\Request;
use Illuminate\Routing\Route;

final class LocalizedRoutes
{
    /** @param array<string, mixed> $parameters */
    public static function url(string $key, array $parameters = [], ?string $locale = null, bool $absolute = false): string
    {
        $locale ??= self::locale(request());
        Locales::definition($locale);

        return route('site.'.$locale.'.'.$key, $parameters, $absolute);
    }

    public static function locale(Request $request): string
    {
        $code = $request->attributes->get('site_locale');

        return is_string($code) && Locales::supports($code) ? $code : Locales::default();
    }

    public static function key(Request $request): ?string
    {
        $route = $request->route();
        if (! $route instanceof Route) {
            return null;
        }
        $prefix = 'site.'.self::locale($request).'.';
        $name = $route->getName();

        return is_string($name) && str_starts_with($name, $prefix) ? substr($name, strlen($prefix)) : null;
    }

    /** @return array<string, string> */
    public static function alternates(Request $request): array
    {
        $key = self::key($request);
        $route = $request->route();
        if ($key === null || ! $route instanceof Route || $request->has('signature') || in_array('signed', $route->gatherMiddleware(), true)) {
            return [];
        }
        $parameters = $route->parameters();
        unset($parameters['_locale']);
        $urls = [];
        foreach (array_keys(Locales::all()) as $locale) {
            $urls[$locale] = self::url($key, [...$parameters, ...self::query($request)], $locale);
        }

        return $urls;
    }

    /** Preserve only public navigation values, never arbitrary redirect/signature fields.
     * @return array<string, string>
     */
    public static function query(Request $request): array
    {
        $query = [];
        foreach ($request->query() as $key => $value) {
            if (! is_string($value)) {
                continue;
            }
            $valid = match ($key) {
                'q' => mb_strlen($value) <= 150,
                'catalog', 'language', 'edition', 'series' => strlen($value) <= 255 && preg_match('/\A(?:[a-zA-Z0-9]+(?:-[a-zA-Z0-9]+)*|\*|)\z/', $value) === 1,
                'type' => in_array($value, ['all', ...Expansion::TYPES], true),
                'sort' => in_array($value, ['recent', 'oldest', 'name'], true),
                'page' => preg_match('/\A[1-9][0-9]{0,8}\z/', $value) === 1,
                default => false,
            };
            if ($valid) {
                $query[$key] = $value;
            }
        }

        return $query;
    }
}
