<?php

use App\Http\Controllers\LegacyRouteController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\SecurityController;
use App\Support\GameSlug;
use Illuminate\Support\Facades\Route;

// Explicit historical GET/HEAD inventory. Auth and admin aliases are owned by their modules.
foreach ([
    '/' => 'home',
    'dashboard' => 'dashboard',
    'settings' => 'settings',
    'settings/profile' => 'profile.edit',
    'settings/security' => 'security.edit',
    'settings/appearance' => 'appearance.edit',
] as $path => $key) {
    Route::get($path, [LegacyRouteController::class, 'page'])->name($key);
}
Route::get('{game:slug}/{catalogKey}/{expansionKey}', [LegacyRouteController::class, 'expansion'])
    ->where(['game' => GameSlug::routePattern(), 'catalogKey' => GameSlug::routePattern(), 'expansionKey' => GameSlug::routePattern()])
    ->name('games.expansions.show');
Route::get('{game:slug}', [LegacyRouteController::class, 'game'])
    ->where('game', GameSlug::routePattern())->name('games.show');

// Historical mutations retain their original controllers and protections.
Route::middleware(['auth'])->group(function () {
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');

});
