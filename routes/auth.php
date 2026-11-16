<?php

use App\Http\Controllers\LegacyAuthController;
use App\Support\Localization\AuthRouteDefinitions;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\RoutePath;

foreach (AuthRouteDefinitions::all() as $key => $definition) {
    $pathKey = match ($key) {
        'password.confirm.store' => 'password.confirm',
        'two-factor.login.store' => 'two-factor.login',
        'two-factor.regenerate-recovery-codes' => 'two-factor.recovery-codes',
        default => $key,
    };
    Route::match($definition['methods'], RoutePath::for($pathKey, AuthRouteDefinitions::legacyPaths()[$key]),
        in_array($key, AuthRouteDefinitions::views(), true) ? LegacyAuthController::class : $definition['action'])
        ->middleware($definition['middleware'])->name($key);
}
