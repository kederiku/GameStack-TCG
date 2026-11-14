<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class AdminLoginController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        return redirect()->route('login');
    }
}
