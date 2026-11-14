<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicGameController;
use App\Support\GameSlug;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicGameController::class, 'index'])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
});

require __DIR__.'/settings.php';

Route::get('/{game:slug}', [PublicGameController::class, 'show'])
    ->where('game', GameSlug::routePattern())
    ->name('games.show');
