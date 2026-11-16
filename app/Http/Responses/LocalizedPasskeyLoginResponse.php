<?php

namespace App\Http\Responses;

use App\Support\Localization\LocalizedRoutes;
use Laravel\Passkeys\Http\Responses\PasskeyLoginResponse;
use Symfony\Component\HttpFoundation\Response;

final class LocalizedPasskeyLoginResponse extends PasskeyLoginResponse
{
    public function toResponse($request): Response
    {
        $previous = config('passkeys.redirect');
        config(['passkeys.redirect' => LocalizedRoutes::url('dashboard')]);
        try {
            // Preserve the vendor's intended handling, status and JSON response shape.
            return parent::toResponse($request);
        } finally {
            config(['passkeys.redirect' => $previous]);
        }
    }
}
