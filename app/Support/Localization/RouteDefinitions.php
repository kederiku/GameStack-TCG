<?php

namespace App\Support\Localization;

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocalePreferenceController;
use App\Http\Controllers\PublicGameController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Support\GameSlug;
use Illuminate\Auth\Middleware\RequirePassword;

/** @phpstan-type LocalizedRouteDefinition array{key: string, pathKey: string, methods: list<string>, action: class-string|array{class-string, string}, middleware: list<string>, constraints: array<string, string>, feature: ?string} */
final class RouteDefinitions
{
    /** @return array<string, LocalizedRouteDefinition> */
    public static function all(): array
    {
        return array_column([
            self::definition('home', 'home', ['GET'], [PublicGameController::class, 'index']),
            self::definition('dashboard', 'dashboard', ['GET'], DashboardController::class, ['auth', 'verified']),
            self::definition('settings', 'account.settings', ['GET'], [ProfileController::class, 'index'], ['auth']),
            self::definition('profile.edit', 'account.profile', ['GET'], [ProfileController::class, 'edit'], ['auth']),
            self::definition('profile.update', 'account.profile', ['PATCH'], [ProfileController::class, 'update'], ['auth']),
            self::definition('profile.destroy', 'account.profile', ['DELETE'], [ProfileController::class, 'destroy'], ['auth', 'verified']),
            self::definition('security.edit', 'account.security', ['GET'], [SecurityController::class, 'edit'], ['auth', 'verified', RequirePassword::class]),
            self::definition('user-password.update', 'account.password', ['PUT'], [SecurityController::class, 'update'], ['auth', 'verified', 'throttle:6,1']),
            self::definition('appearance.edit', 'account.appearance', ['GET'], [ProfileController::class, 'appearance'], ['auth', 'verified']),
            self::definition('locale.update', 'locale', ['POST'], LocalePreferenceController::class),
            ...array_values(AuthRouteDefinitions::all()),
            self::definition('games.expansions.show', 'games.expansion', ['GET'], [PublicGameController::class, 'expansion'], [], [
                'game' => GameSlug::routePattern(), 'catalogKey' => GameSlug::routePattern(), 'expansionKey' => GameSlug::routePattern(),
            ]),
            self::definition('games.show', 'games.show', ['GET'], [PublicGameController::class, 'show'], [], ['game' => GameSlug::routePattern()]),

        ], null, 'key');
    }

    /**
     * @param  list<string>  $methods
     * @param  class-string|array{class-string, string}  $action
     * @param  list<string>  $middleware
     * @param  array<string, string>  $constraints
     * @return LocalizedRouteDefinition
     */
    private static function definition(string $key, string $pathKey, array $methods, string|array $action, array $middleware = [], array $constraints = []): array
    {
        return compact('key', 'pathKey', 'methods', 'action', 'middleware', 'constraints') + ['feature' => null];
    }
}
