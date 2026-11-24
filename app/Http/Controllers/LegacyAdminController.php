<?php

namespace App\Http\Controllers;

use App\Support\Localization\AdminPanels;
use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LegacyAdminController
{
    public function __invoke(Request $request): RedirectResponse
    {
        $page = $request->route()->defaults['admin_page'];
        $parameters = $request->route()->parameters();
        unset($parameters['admin_page']);

        return redirect(AdminPanels::url($page, [...$parameters, ...AdminPanels::query($request)], LocalizedRoutes::locale($request)), 302)
            ->withHeaders(['Cache-Control' => 'private, no-store']);
    }
}
