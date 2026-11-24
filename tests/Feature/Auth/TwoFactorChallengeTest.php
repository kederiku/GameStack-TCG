<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use PragmaRX\Google2FA\Google2FA;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class TwoFactorChallengeTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());
    }

    public function test_two_factor_challenge_redirects_to_login_when_not_authenticated(): void
    {
        $response = $this->get(route('site.fr.two-factor.login'));

        $response->assertRedirect(route('site.fr.login'));
    }

    public function test_two_factor_challenge_can_be_rendered(): void
    {
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $this->post(route('site.fr.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->get(route('site.fr.two-factor.login'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('auth/two-factor-challenge'),
            );
    }

    public function test_only_a_valid_totp_completes_two_factor_login(): void
    {
        $totp = app(Google2FA::class);
        $secret = $totp->generateSecretKey();
        $user = User::factory()->withTwoFactor()->create(['two_factor_secret' => encrypt($secret)]);

        $this->post(route('site.fr.login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('site.fr.two-factor.login'));
        $this->assertGuest();

        $this->post(route('site.fr.two-factor.login.store'), ['code' => 'invalid-code'])
            ->assertSessionHasErrors('code');
        $this->assertGuest();

        $this->post(route('site.fr.two-factor.login.store'), ['code' => $totp->getCurrentOtp($secret)])
            ->assertSessionHasNoErrors()
            ->assertSessionMissing('login.id')
            ->assertRedirect(route('site.fr.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_recovery_codes_must_be_valid_and_can_only_be_used_once(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $code = $user->recoveryCodes()[0];
        $credentials = ['email' => $user->email, 'password' => 'password'];
        $this->post(route('site.fr.login.store'), $credentials)->assertRedirect(route('site.fr.two-factor.login'));

        $this->post(route('site.fr.two-factor.login.store'), ['recovery_code' => 'invalid-code'])
            ->assertSessionHasErrors('recovery_code');
        $this->assertGuest();

        $this->post(route('site.fr.two-factor.login.store'), ['recovery_code' => $code])
            ->assertSessionHasNoErrors()->assertRedirect(route('site.fr.dashboard'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotContains($code, $user->fresh()->recoveryCodes());

        $this->post(route('site.fr.logout'));
        $this->post(route('site.fr.login.store'), $credentials)->assertRedirect(route('site.fr.two-factor.login'));
        $this->post(route('site.fr.two-factor.login.store'), ['recovery_code' => $code])
            ->assertSessionHasErrors('recovery_code');
        $this->assertGuest();
    }
}
