<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Http\Responses\LocalizedPasskeyRedirectResponse;
use App\Support\PublicGames;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;
use Laravel\Passkeys\Contracts\PasskeyConfirmationResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        Fortify::ignoreRoutes();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(PasskeyLoginResponse::class, LocalizedPasskeyRedirectResponse::class);
        $this->app->bind(PasskeyConfirmationResponse::class, LocalizedPasskeyRedirectResponse::class);
        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::createUsersUsing(CreateNewUser::class);
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => $this->authView('auth/login', [
            'canRegister' => Features::enabled(Features::registration()),
            'canResetPassword' => Features::enabled(Features::resetPasswords()),
            'status' => $request->session()->get('status'),
        ]));

        Fortify::resetPasswordView(fn (Request $request) => $this->authView('auth/reset-password', [
            'email' => $request->email,
            'token' => $request->route('token'),
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::requestPasswordResetLinkView(fn (Request $request) => $this->authView('auth/forgot-password', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::verifyEmailView(fn (Request $request) => $this->authView('auth/verify-email', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::registerView(fn () => $this->authView('auth/register', [
            'passwordRules' => Password::defaults()->toPasswordRulesString(),
        ]));

        Fortify::twoFactorChallengeView(fn () => $this->authView('auth/two-factor-challenge'));

        Fortify::confirmPasswordView(fn () => $this->authView('auth/confirm-password'));
    }

    /** @param array<string, mixed> $props */
    private function authView(string $component, array $props = []): Response
    {
        return Inertia::render($component, [
            ...$props,
            'games' => fn () => PublicGames::all(),
        ]);
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('passkeys', function (Request $request) {
            return Limit::perMinute(10)->by(
                ($request->input('credential.id') ?: $request->session()->getId()).'|'.$request->ip(),
            );
        });
    }
}
