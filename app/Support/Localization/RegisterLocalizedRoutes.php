<?php

namespace App\Support\Localization;

use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;
use RuntimeException;

final class RegisterLocalizedRoutes
{
    public static function register(): void
    {
        $files = app(TranslationFiles::class);
        foreach (array_keys(Locales::all()) as $locale) {
            $paths = $files->routes($locale);
            $errors = LocalizedRoutePaths::collisions($paths);
            if ($errors !== []) {
                throw new RuntimeException($locale.': '.implode("\n", $errors));
            }
            foreach (RouteDefinitions::all() as $definition) {
                if ($definition['feature'] !== null && ! Features::enabled($definition['feature'])) {
                    continue;
                }
                $path = $paths[$definition['pathKey']] ?? throw new RuntimeException("Missing route path [{$locale}:{$definition['pathKey']}].");
                $route = Route::match($definition['methods'], rtrim($locale.'/'.$path, '/'), $definition['action'])
                    ->middleware($definition['middleware'])->where($definition['constraints'])
                    ->defaults('_locale', $locale)->name('site.'.$locale.'.'.$definition['key']);
                if (array_key_exists('game', $definition['constraints'])) {
                    $route->setBindingFields(['game' => 'slug']);
                }
            }
        }
    }
}
