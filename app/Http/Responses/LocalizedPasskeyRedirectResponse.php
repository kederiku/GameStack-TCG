<?php

namespace App\Http\Responses;

use App\Support\Localization\LocalizedRoutes;
use Laravel\Passkeys\Contracts\PasskeyConfirmationResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocalizedPasskeyRedirectResponse implements PasskeyConfirmationResponse, PasskeyLoginResponse
{
    public function toResponse($request): Response
    {
        $redirect = redirect()->intended(LocalizedRoutes::url('dashboard'));

        return $request->wantsJson()
            ? response()->json(['redirect' => $redirect->getTargetUrl()])
            : $redirect;
    }
}
