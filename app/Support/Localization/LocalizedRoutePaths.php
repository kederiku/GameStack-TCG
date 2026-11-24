<?php

namespace App\Support\Localization;

final class LocalizedRoutePaths
{
    /**
     * Compare concrete registrations per locale before Laravel overwrites a duplicate.
     * Parameter names distinguish identities, but do not distinguish incoming requests.
     *
     * @param  array<string, string>  $paths
     * @return list<string>
     */
    public static function collisions(array $paths): array
    {
        $registered = $errors = [];
        $register = static function (string $key, string $method, string $path) use (&$registered, &$errors): void {
            $pattern = preg_replace('/\{\w+(\??)\}/', '{$1}', trim($path, '/'));
            $signature = $method.' '.$pattern;
            if (isset($registered[$signature])) {
                $errors[] = "Route collision for {$method} /{$path}: {$registered[$signature]} and {$key}.";
            }
            $registered[$signature] = $key;
        };

        foreach (RouteDefinitions::all() as $route) {
            if (isset($paths[$route['pathKey']])) {
                foreach ($route['methods'] as $method) {
                    $register($route['key'], $method, $paths[$route['pathKey']]);
                }
            }
        }

        if (isset($paths['admin.dashboard'])) {
            $panel = $paths['admin.dashboard'];
            $register('admin.dashboard', 'GET', $panel);
            $register('admin.logout', 'POST', $panel.'/logout');
            foreach (['admin.login', 'admin.development-services'] as $key) {
                if (isset($paths[$key])) {
                    $register($key, 'GET', $panel.'/'.$paths[$key]);
                }
            }
            foreach (AdminPanels::resources() as $key => $resource) {
                foreach (array_keys($resource::getPages()) as $page) {
                    if (isset($paths['admin.resources.'.$key], $paths['admin.pages.'.$page])) {
                        $register('admin.'.$key.'.'.$page, 'GET', $panel.'/'.$paths['admin.resources.'.$key].'/'.$paths['admin.pages.'.$page]);
                    }
                }
            }
        }

        return $errors;
    }
}
