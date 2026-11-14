<?php

namespace App\Support;

use Illuminate\Support\Facades\Route;

class GameSlug
{
    public const array RESERVED = [
        'admin', 'api', 'build', 'css', 'dashboard', 'email', 'filament', 'fonts',
        'forgot-password', 'js', 'livewire', 'login', 'logout', 'passkeys',
        'register', 'reset-password', 'sanctum', 'settings', 'storage',
        'two-factor-challenge', 'up', 'user',
    ];

    /** @return list<string> */
    public static function reserved(): array
    {
        $reserved = self::RESERVED;

        // Include configured package prefixes, such as Livewire's hashed path.
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $prefix = explode('/', $route->uri())[0];
            if (preg_match('/\A'.self::routePattern().'\z/', $prefix)) {
                $reserved[] = $prefix;
            }
        }

        return array_values(array_unique($reserved));
    }

    public static function routePattern(): string
    {
        return '[a-z0-9]+(?:-[a-z0-9]+)*';
    }
}
