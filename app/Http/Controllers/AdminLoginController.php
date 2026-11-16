<?php

namespace App\Http\Controllers;

use App\Support\Localization\LocalizedRoutes;
use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect(LocalizedRoutes::url('login'));
    }
}
