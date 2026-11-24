<?php

namespace App\Support\Localization;

use Illuminate\Support\Facades\Config;
use Laravel\Fortify\Features;
use Laravel\Fortify\Http\Controllers as Fortify;
use Laravel\Passkeys\Http\Controllers as Passkeys;

/** @phpstan-import-type LocalizedRouteDefinition from RouteDefinitions */
final class AuthRouteDefinitions
{
    /** @return array<string, LocalizedRouteDefinition> */
    public static function all(): array
    {
        $guest = ['guest:'.config('fortify.guard')];
        $auth = [config('fortify.auth_middleware', 'auth').':'.config('fortify.guard')];
        $twoFactor = Features::optionEnabled(Features::twoFactorAuthentication(), 'confirmPassword') ? [...$auth, 'password.confirm'] : $auth;
        $passkeys = config('fortify-options.passkeys.confirmPassword', true) ? [...$auth, 'password.confirm'] : $auth;
        $definitions = [];
        $throttle = static fn (string $name): array => config('fortify.limiters.'.$name) ? ['throttle:'.config('fortify.limiters.'.$name)] : [];
        $verify = [...$auth, 'throttle:'.config('fortify.limiters.verification', '6,1')];
        self::add($definitions, 'login', 'GET', Fortify\AuthenticatedSessionController::class, 'create', $guest, view: true);
        self::add($definitions, 'login.store', 'POST', Fortify\AuthenticatedSessionController::class, 'store', [...$guest, ...$throttle('login')]);
        self::add($definitions, 'logout', 'POST', Fortify\AuthenticatedSessionController::class, 'destroy', $auth);
        self::add($definitions, 'password.request', 'GET', Fortify\PasswordResetLinkController::class, 'create', $guest, Features::resetPasswords(), true);
        self::add($definitions, 'password.reset', 'GET', Fortify\NewPasswordController::class, 'create', $guest, Features::resetPasswords(), true);
        self::add($definitions, 'password.email', 'POST', Fortify\PasswordResetLinkController::class, 'store', $guest, Features::resetPasswords());
        self::add($definitions, 'password.update', 'POST', Fortify\NewPasswordController::class, 'store', $guest, Features::resetPasswords());
        self::add($definitions, 'register', 'GET', Fortify\RegisteredUserController::class, 'create', $guest, Features::registration(), true);
        self::add($definitions, 'register.store', 'POST', Fortify\RegisteredUserController::class, 'store', $guest, Features::registration());
        self::add($definitions, 'verification.notice', 'GET', Fortify\EmailVerificationPromptController::class, '__invoke', $auth, Features::emailVerification(), true);
        self::add($definitions, 'verification.verify', 'GET', Fortify\VerifyEmailController::class, '__invoke', [...$auth, 'signed', $verify[1]], Features::emailVerification());
        self::add($definitions, 'verification.send', 'POST', Fortify\EmailVerificationNotificationController::class, 'store', $verify, Features::emailVerification());
        self::add($definitions, 'user-profile-information.update', 'PUT', Fortify\ProfileInformationController::class, 'update', $auth, Features::updateProfileInformation());
        // The application owns user-password.update (verified settings + throttle), as before localization.
        self::add($definitions, 'password.confirm', 'GET', Fortify\ConfirmablePasswordController::class, 'show', $auth, view: true);
        self::add($definitions, 'password.confirmation', 'GET', Fortify\ConfirmedPasswordStatusController::class, 'show', $auth);
        self::add($definitions, 'password.confirm.store', 'POST', Fortify\ConfirmablePasswordController::class, 'store', $auth);
        self::add($definitions, 'two-factor.login', 'GET', Fortify\TwoFactorAuthenticatedSessionController::class, 'create', $guest, Features::twoFactorAuthentication(), true);
        self::add($definitions, 'two-factor.login.store', 'POST', Fortify\TwoFactorAuthenticatedSessionController::class, 'store', [...$guest, ...$throttle('two-factor')], Features::twoFactorAuthentication());
        foreach ([
            ['two-factor.enable', 'POST', Fortify\TwoFactorAuthenticationController::class, 'store'],
            ['two-factor.confirm', 'POST', Fortify\ConfirmedTwoFactorAuthenticationController::class, 'store'],
            ['two-factor.disable', 'DELETE', Fortify\TwoFactorAuthenticationController::class, 'destroy'],
            ['two-factor.qr-code', 'GET', Fortify\TwoFactorQrCodeController::class, 'show'],
            ['two-factor.secret-key', 'GET', Fortify\TwoFactorSecretKeyController::class, 'show'],
            ['two-factor.recovery-codes', 'GET', Fortify\RecoveryCodeController::class, 'index'],
            ['two-factor.regenerate-recovery-codes', 'POST', Fortify\RecoveryCodeController::class, 'store'],
        ] as [$key, $method, $controller, $action]) {
            self::add($definitions, $key, $method, $controller, $action, $twoFactor, Features::twoFactorAuthentication());
        }
        foreach ([
            ['passkey.login-options', 'GET', Passkeys\PasskeyLoginController::class, 'index', [...$guest, ...$throttle('passkeys')]],
            ['passkey.login', 'POST', Passkeys\PasskeyLoginController::class, 'store', [...$guest, ...$throttle('passkeys')]],
            ['passkey.confirm-options', 'GET', Passkeys\PasskeyConfirmationController::class, 'index', [...$auth, ...$throttle('passkeys')]],
            ['passkey.confirm', 'POST', Passkeys\PasskeyConfirmationController::class, 'store', [...$auth, ...$throttle('passkeys')]],
            ['passkey.registration-options', 'GET', Passkeys\PasskeyRegistrationController::class, 'index', [...$passkeys, ...$throttle('passkeys')]],
            ['passkey.store', 'POST', Passkeys\PasskeyRegistrationController::class, 'store', [...$passkeys, ...$throttle('passkeys')]],
            ['passkey.destroy', 'DELETE', Passkeys\PasskeyRegistrationController::class, 'destroy', $passkeys],
        ] as [$key, $method, $controller, $action, $middleware]) {
            self::add($definitions, $key, $method, $controller, $action, $middleware, Features::passkeys());
        }

        return $definitions;
    }

    /**
     * @param  array<string, LocalizedRouteDefinition>  $definitions
     * @param  class-string  $controller
     * @param  list<string>  $middleware
     */
    private static function add(array &$definitions, string $key, string $method, string $controller, string $action, array $middleware, ?string $feature = null, bool $view = false): void
    {
        if (($view && ! config('fortify.views', true)) || ($feature !== null && ! Features::enabled($feature))) {
            return;
        }
        /** @var list<string> $globalMiddleware */
        $globalMiddleware = Config::array('fortify.middleware', ['web']);
        $definitions[$key] = [
            'key' => $key, 'pathKey' => 'auth.'.$key, 'methods' => [$method],
            'action' => [$controller, $action],
            'middleware' => array_values(array_unique([...$globalMiddleware, ...$middleware])),
            'constraints' => [], 'feature' => $feature,
        ];
    }

    /** Historical view routes redirect; signed actions and JSON endpoints execute directly.
     * @return list<string>
     */
    public static function views(): array
    {
        return ['login', 'register', 'password.request', 'password.reset', 'verification.notice', 'password.confirm', 'two-factor.login'];
    }

    /** @return array<string, string> */
    public static function legacyPaths(): array
    {
        return [
            'login' => 'login', 'login.store' => 'login', 'logout' => 'logout',
            'password.request' => 'forgot-password', 'password.email' => 'forgot-password',
            'password.reset' => 'reset-password/{token}', 'password.update' => 'reset-password',
            'register' => 'register', 'register.store' => 'register',
            'verification.notice' => 'email/verify', 'verification.verify' => 'email/verify/{id}/{hash}',
            'verification.send' => 'email/verification-notification',
            'user-profile-information.update' => 'user/profile-information',
            'password.confirm' => 'user/confirm-password', 'password.confirm.store' => 'user/confirm-password',
            'password.confirmation' => 'user/confirmed-password-status',
            'two-factor.login' => 'two-factor-challenge', 'two-factor.login.store' => 'two-factor-challenge',
            'two-factor.enable' => 'user/two-factor-authentication', 'two-factor.disable' => 'user/two-factor-authentication',
            'two-factor.confirm' => 'user/confirmed-two-factor-authentication',
            'two-factor.qr-code' => 'user/two-factor-qr-code', 'two-factor.secret-key' => 'user/two-factor-secret-key',
            'two-factor.recovery-codes' => 'user/two-factor-recovery-codes', 'two-factor.regenerate-recovery-codes' => 'user/two-factor-recovery-codes',
            'passkey.login-options' => 'passkeys/login/options', 'passkey.login' => 'passkeys/login',
            'passkey.confirm-options' => 'passkeys/confirm/options', 'passkey.confirm' => 'passkeys/confirm',
            'passkey.registration-options' => 'user/passkeys/options', 'passkey.store' => 'user/passkeys', 'passkey.destroy' => 'user/passkeys/{passkey}',
        ];
    }
}
