<?php

namespace Tests\Feature\Localization;

use App\Http\Middleware\NormalizeLocalizedRedirects;
use App\Models\User;
use App\Notifications\LocalizedResetPassword;
use App\Support\Localization\AuthRouteDefinitions;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Laravel\Passkeys\Contracts\PasskeyConfirmationResponse;
use Laravel\Passkeys\Contracts\PasskeyLoginResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class LocalizedAuthTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_auth_views_have_canonical_locale_and_legacy_redirects(): void
    {
        foreach (['fr' => ['connexion', 'inscription', 'mot-de-passe-oublie', 'reinitialiser-mot-de-passe/token'], 'en' => ['login', 'register', 'forgot-password', 'reset-password/token']] as $locale => $paths) {
            foreach ($paths as $path) {
                $this->get('/'.$locale.'/'.$path)->assertOk()->assertInertia(fn (Assert $page) => $page->where('locale', $locale));
            }
        }
        $this->get('/login?lang=en')->assertRedirect('/en/login');
        $this->get('/reset-password/token?email=user%40example.com&lang=en')->assertRedirect('/en/reset-password/token?email=user%40example.com');
    }

    public function test_registration_saves_form_locale_without_granting_admin_access(): void
    {
        Notification::fake();
        foreach (['fr' => 'inscription', 'en' => 'register'] as $locale => $path) {
            $this->post('/'.$locale.'/'.$path, ['name' => 'Tester', 'email' => $locale.'@example.com', 'password' => 'password', 'password_confirmation' => 'password'])
                ->assertSessionHasNoErrors()->assertRedirect($locale === 'fr' ? '/fr/mon-espace' : '/en/dashboard');
            $user = User::where('email', $locale.'@example.com')->firstOrFail();
            $this->assertSame($locale, $user->preferred_locale);
            $this->assertFalse($user->is_admin);
            $this->get('/fr/admin')->assertForbidden();
            $this->post('/'.$locale.'/'.($locale === 'fr' ? 'deconnexion' : 'logout'));
        }
    }

    public function test_intended_security_destination_survives_login_and_two_factor(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $this->get('/en/settings/security')->assertRedirect('/en/login');
        $this->post('/en/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/en/two-factor-challenge');
        $this->get('/en/two-factor-challenge')->assertOk();
        $this->post('/en/two-factor-challenge', ['recovery_code' => $user->recoveryCodes()[0]])
            ->assertRedirect('/en/settings/security');
        $this->assertAuthenticatedAs($user);
        $this->get('/en/settings/security')->assertRedirect('/en/user/confirm-password');
        $this->post('/en/user/confirm-password', ['password' => 'password'])->assertRedirect('/en/settings/security');
        $this->get('/en/settings/security')->assertOk();
    }

    public function test_passkey_json_response_generates_localized_default_without_changing_json_contract(): void
    {
        Route::middleware('web')->get('__test-passkey-response/en', fn () => app(PasskeyLoginResponse::class))
            ->defaults('_locale', 'en');
        $this->getJson('/__test-passkey-response/en')->assertOk()->assertJsonPath('redirect', url('/en/dashboard'));
        $this->assertSame('/dashboard', config('passkeys.redirect'));
    }

    public function test_password_and_two_factor_ignore_outside_intended_destinations(): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended' => 'https://outside.invalid/path'])
            ->post('/en/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/en/dashboard');
        $this->post('/en/logout');
        $user = User::factory()->withTwoFactor()->create();
        $this->post('/en/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/en/two-factor-challenge');
        $this->withSession(['url.intended' => '//outside.invalid/path'])
            ->post('/en/two-factor-challenge', ['recovery_code' => $user->recoveryCodes()[0]])->assertRedirect('/en/dashboard');
    }

    public function test_password_and_two_factor_json_preserve_the_fortify_contract(): void
    {
        $user = User::factory()->create();
        $this->withSession(['url.intended' => 'https://outside.invalid/path'])
            ->postJson('/en/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertExactJson(['two_factor' => false]);
        $this->post('/en/logout');
        $user = User::factory()->withTwoFactor()->create();
        $this->withSession(['url.intended' => '//outside.invalid/path'])
            ->postJson('/en/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()->assertExactJson(['two_factor' => true]);
        $this->postJson('/en/two-factor-challenge', ['recovery_code' => $user->recoveryCodes()[0]])->assertNoContent();
        $this->assertAuthenticatedAs($user);
    }

    #[DataProvider('intendedUrls')]
    public function test_passkey_success_consumes_only_local_intended_urls(?string $intended, ?string $expected): void
    {
        foreach ([PasskeyLoginResponse::class, PasskeyConfirmationResponse::class] as $index => $response) {
            $path = '/__test-passkey-'.$index;
            Route::middleware('web')->get($path, fn () => app($response))->defaults('_locale', 'en');
            $target = str_replace('{origin}', url('/'), $expected ?? url('/en/dashboard'));
            $value = $intended === null ? null : str_replace('{origin}', url('/'), $intended);
            foreach ([true, false] as $json) {
                $this->withSession(['url.intended' => $value]);
                if ($json) {
                    $this->getJson($path)->assertOk()->assertExactJson(['redirect' => $target]);
                } else {
                    $this->get($path)->assertRedirect($target);
                }
            }
        }
    }

    public static function intendedUrls(): array
    {
        return [
            'absent' => [null, null],
            'outside' => ['https://outside.invalid', null],
            'scheme relative' => ['//outside.invalid', null],
            'backslash' => ['/\\outside.invalid', null],
            'control' => ["/en/\tsettings", null],
            'credentials' => ['http://user@localhost/path', null],
            'other port' => ['http://localhost:9999/path', null],
            'other scheme' => ['mailto:local@localhost', null],
            'local absolute' => ['{origin}/fr/admin/jeux/2/modifier?x=%2F&y=2#tab', '{origin}/fr/admin/jeux/2/modifier?x=%2F&y=2#tab'],
            'local signed' => ['/fr/verification?x=%2f&signature=abc#view', '{origin}/fr/verification?x=%2f&signature=abc#view'],
        ];
    }

    public function test_complete_installed_fortify_inventory_matches_the_adapter(): void
    {
        $original = Route::getFacadeRoot();
        $vendor = new Router(app('events'), app());
        Route::swap($vendor);
        try {
            require base_path('vendor/laravel/fortify/routes/routes.php');
        } finally {
            Route::swap($original);
        }
        $definitions = AuthRouteDefinitions::all();
        $this->assertCount(count($vendor->getRoutes()), $definitions);
        foreach ($vendor->getRoutes() as $expected) {
            $key = $expected->getName();
            $this->assertArrayHasKey($key, $definitions);
            foreach (['fr', 'en'] as $locale) {
                $actual = Route::getRoutes()->getByName('site.'.$locale.'.'.$key);
                $this->assertSame($expected->getActionName(), $actual->getActionName(), $key);
                $this->assertSame($expected->methods(), $actual->methods(), $key);
                $this->assertSame($expected->gatherMiddleware(), $actual->gatherMiddleware(), $key);
            }
        }
    }

    public function test_canonical_and_legacy_fortify_routes_keep_protections(): void
    {
        foreach (['login.store', 'password.update', 'verification.verify', 'password.confirmation', 'two-factor.qr-code', 'two-factor.secret-key', 'two-factor.recovery-codes', 'passkey.login-options', 'passkey.confirm-options', 'passkey.registration-options', 'passkey.destroy'] as $key) {
            $legacy = Route::getRoutes()->getByName($key);
            $this->assertNotNull($legacy, $key);
            foreach (['fr', 'en'] as $locale) {
                $canonical = Route::getRoutes()->getByName('site.'.$locale.'.'.$key);
                $this->assertNotNull($canonical, $key);
                $this->assertSame($legacy->getActionName(), $canonical->getActionName());
                $this->assertSame($legacy->methods(), $canonical->methods());
                $this->assertSame($legacy->gatherMiddleware(), $canonical->gatherMiddleware());
            }
        }
        $this->getJson('/en/passkeys/login/options')->assertOk()->assertJsonStructure(['options' => ['challenge', 'rpId']])->assertJsonPath('options.rpId', config('fortify.passkeys.relying_party_id'));
        $this->getJson('/.well-known/passkey-endpoints?lang=en')->assertOk()->assertJsonPath('manage', url('/en/settings/security'));
        $this->actingAs(User::factory()->create())->get('/en/user/two-factor-qr-code')->assertRedirect('/en/user/confirm-password');
    }

    public function test_redirect_normalization_preserves_response_and_leaves_signed_external_and_json_alone(): void
    {
        $request = Request::create(url('/en/login'));
        $request->attributes->set('site_locale', 'en');
        $middleware = new NormalizeLocalizedRedirects;
        $response = redirect('/dashboard?verified=1#section', 307)->header('X-Test', 'kept');
        $this->assertSame($response, $middleware->handle($request, fn () => $response));
        $this->assertSame('/en/dashboard?verified=1#section', $response->getTargetUrl());
        $this->assertSame(307, $response->getStatusCode());
        $this->assertSame('kept', $response->headers->get('X-Test'));
        foreach ([url('/email/verify/1/hash?signature=abc'), 'https://example.com/dashboard', '/unknown/path', '/fr/mon-espace'] as $target) {
            $response = redirect($target);
            $original = $response->getTargetUrl();
            $this->assertSame($original, $middleware->handle($request, fn () => $response)->getTargetUrl());
        }
        $json = response()->json(['redirect' => '/dashboard']);
        $this->assertSame($json, $middleware->handle($request, fn () => $json));
        $this->assertSame('/dashboard', $json->getData(true)['redirect']);
    }

    public function test_english_reset_password_and_validation_flash_use_canonical_destinations(): void
    {
        Notification::fake();
        $user = User::factory()->create(['preferred_locale' => 'en']);
        $this->from('/en/forgot-password')->post('/en/forgot-password', ['email' => $user->email])
            ->assertRedirect('/en/forgot-password')->assertSessionHas('status');
        $notification = Notification::sent($user, LocalizedResetPassword::class)->first();
        $this->post('/en/reset-password', ['token' => $notification->token, 'email' => $user->email,
            'password' => 'a-new-password-123', 'password_confirmation' => 'a-new-password-123'])
            ->assertRedirect('/en/login')->assertSessionHas('status');
        $this->from('/en/login')->post('/en/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertRedirect('/en/login')->assertSessionHasErrors('email')->assertSessionHasInput('email', $user->email);
    }

    public function test_feature_flags_views_and_optional_confirmation_match_vendor_configuration(): void
    {
        config(['fortify.features' => [], 'fortify.views' => false]);
        $this->assertSame(['login.store', 'logout', 'password.confirmation', 'password.confirm.store'], array_keys(AuthRouteDefinitions::all()));
        config(['fortify.features' => [Features::twoFactorAuthentication(), Features::passkeys()],
            'fortify-options.two-factor-authentication.confirmPassword' => false,
            'fortify-options.passkeys.confirmPassword' => false]);
        $definitions = AuthRouteDefinitions::all();
        $this->assertNotContains('password.confirm', $definitions['two-factor.qr-code']['middleware']);
        $this->assertNotContains('password.confirm', $definitions['passkey.destroy']['middleware']);
        $this->assertContains('auth:web', $definitions['passkey.destroy']['middleware']);
        $this->assertContains('throttle:passkeys', $definitions['passkey.registration-options']['middleware']);
    }
}
