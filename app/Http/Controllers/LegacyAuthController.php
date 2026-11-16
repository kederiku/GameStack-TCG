<?php

namespace App\Http\Controllers;

use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LegacyAuthController
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect(LocalizedRoutes::url((string) $request->route()?->getName(), [
            ...$request->route()->parameters(), ...LocalizedRoutes::query($request),
        ]))->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
