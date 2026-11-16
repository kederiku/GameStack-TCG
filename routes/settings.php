<?php

use App\Support\Localization\LocalizedRoutes;
use Illuminate\Support\Facades\Route;

Route::get('.well-known/passkey-endpoints', function () {
    return response()->json([
        'enroll' => LocalizedRoutes::url('security.edit', absolute: true),
        'manage' => LocalizedRoutes::url('security.edit', absolute: true),
    ]);
})->name('well-known.passkeys');
