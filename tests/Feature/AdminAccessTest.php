<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_admin_login(): void
    {
        $this->get('/fr/admin')->assertRedirect('/fr/admin/connexion');
    }

    public function test_admin_login_uses_the_react_authentication_flow(): void
    {
        $this->get('/fr/admin/connexion')->assertRedirect('/fr/connexion');
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        config(['app.env' => 'local']);

        $this->actingAs(User::factory()->create())
            ->get('/fr/admin')
            ->assertForbidden();
    }

    public function test_verified_administrators_can_access_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/fr/admin')->assertOk();
    }

    public function test_unverified_administrators_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/fr/admin')->assertForbidden();
    }

    public function test_registration_cannot_grant_administrator_privileges(): void
    {
        $this->post('/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'UserPassword123!',
            'password_confirmation' => 'UserPassword123!',
            'is_admin' => true,
        ])->assertRedirect('/fr/mon-espace');

        $this->assertFalse(User::query()->where('email', 'user@example.com')->sole()->is_admin);
        $this->get('/fr/admin')->assertForbidden();
    }

    public function test_admin_flag_is_not_mass_assignable(): void
    {
        $user = User::factory()->create();
        $user->fill(['is_admin' => true])->save();

        $this->assertFalse($user->fresh()->is_admin);
    }

    public function test_admin_login_preserves_the_intended_destination(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->get('/fr/admin')->assertRedirect('/fr/admin/connexion');
        $this->get('/fr/admin/connexion')->assertRedirect('/fr/connexion');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/fr/admin');
        $this->get('/fr/admin')->assertOk();
    }

    public function test_inertia_login_opens_the_intended_catalog_page_as_a_full_navigation(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $destination = '/fr/admin/catalogues?page=2';

        $this->get($destination)->assertRedirect('/fr/admin/connexion');
        $this->get('/fr/admin/connexion')->assertRedirect('/fr/connexion');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'], ['X-Inertia' => 'true'])
            ->assertConflict()
            ->assertHeader('X-Inertia-Location', url($destination))
            ->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($user);
        $this->get($destination)->assertOk();
    }

    public function test_json_login_keeps_the_fortify_response_when_admin_is_intended(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->get('/fr/admin')->assertRedirect('/fr/admin/connexion');
        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertExactJson(['two_factor' => false])
            ->assertHeaderMissing('X-Inertia-Location');

        $this->assertAuthenticatedAs($user);
    }

    public function test_administrators_must_complete_two_factor_authentication(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $user->forceFill(['is_admin' => true])->save();
        $recoveryCode = $user->recoveryCodes()[0];

        $this->get('/fr/admin')->assertRedirect('/fr/admin/connexion');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'], ['X-Inertia' => 'true'])
            ->assertRedirect('/fr/verification-deux-facteurs')
            ->assertHeaderMissing('X-Inertia-Location');

        $this->assertGuest();
        $this->get('/fr/admin')->assertRedirect('/fr/admin/connexion');

        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode], ['X-Inertia' => 'true'])
            ->assertConflict()
            ->assertHeader('X-Inertia-Location', url('/fr/admin'))
            ->assertSessionMissing('url.intended');

        $this->assertAuthenticatedAs($user);
        $this->get('/fr/admin')->assertOk();
    }
}
