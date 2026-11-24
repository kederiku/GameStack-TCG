<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered()
    {
        $response = $this->get(route('site.fr.login'));

        $response->assertOk();
    }

    public function test_login_reports_whether_registration_is_available(): void
    {
        config(['fortify.features' => [Features::registration()]]);

        $this->get(route('site.fr.login'))->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('canRegister', true)
        );

        config(['fortify.features' => []]);

        $this->get(route('site.fr.login'))->assertInertia(fn (Assert $page) => $page
            ->component('auth/login')
            ->where('canRegister', false)
        );
    }

    public function test_users_can_authenticate_using_the_login_screen()
    {
        $user = User::factory()->create();

        $response = $this->post(route('site.fr.login.store'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('site.fr.dashboard', absolute: false));
    }

    public function test_inertia_login_keeps_ordinary_redirects_to_the_dashboard(): void
    {
        $user = User::factory()->create();

        $this->get('/fr/mon-espace')->assertRedirect('/fr/connexion');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'], ['X-Inertia' => 'true'])
            ->assertRedirect('/fr/mon-espace')
            ->assertHeaderMissing('X-Inertia-Location');
    }

    public function test_users_with_two_factor_enabled_are_redirected_to_two_factor_challenge()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $user = User::factory()->withTwoFactor()->create();

        $response = $this->post(route('site.fr.login'), [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('site.fr.two-factor.login'));
        $response->assertSessionHas('login.id', $user->id);
        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_password()
    {
        $user = User::factory()->create();

        $this->post(route('site.fr.login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('site.fr.logout'));

        $response->assertRedirect(route('site.fr.home'));

        $this->assertGuest();
    }

    public function test_users_are_rate_limited()
    {
        $user = User::factory()->create();

        RateLimiter::increment(md5('login'.implode('|', [$user->email, '127.0.0.1'])), amount: 5);

        $response = $this->post(route('site.fr.login.store'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertTooManyRequests();
    }
}
