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
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_login_uses_the_react_authentication_flow(): void
    {
        $this->get('/admin/login')->assertRedirect('/login');
    }

    public function test_regular_users_cannot_access_the_admin_panel(): void
    {
        config(['app.env' => 'local']);

        $this->actingAs(User::factory()->create())
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_verified_administrators_can_access_the_admin_panel(): void
    {
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/admin')->assertOk();
    }

    public function test_unverified_administrators_cannot_access_the_admin_panel(): void
    {
        $user = User::factory()->unverified()->create();
        $user->forceFill(['is_admin' => true])->save();

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_registration_cannot_grant_administrator_privileges(): void
    {
        $this->post('/register', [
            'name' => 'User',
            'email' => 'user@example.com',
            'password' => 'UserPassword123!',
            'password_confirmation' => 'UserPassword123!',
            'is_admin' => true,
        ])->assertRedirect('/dashboard');

        $this->assertFalse(User::query()->where('email', 'user@example.com')->sole()->is_admin);
        $this->get('/admin')->assertForbidden();
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

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->get('/admin/login')->assertRedirect('/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/admin');
        $this->get('/admin')->assertOk();
    }

    public function test_administrators_must_complete_two_factor_authentication(): void
    {
        $user = User::factory()->withTwoFactor()->create();
        $user->forceFill(['is_admin' => true])->save();
        $recoveryCode = $user->recoveryCodes()[0];

        $this->get('/admin')->assertRedirect('/admin/login');
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/two-factor-challenge');

        $this->assertGuest();
        $this->get('/admin')->assertRedirect('/admin/login');

        $this->post('/two-factor-challenge', ['recovery_code' => $recoveryCode])
            ->assertRedirect('/admin');

        $this->assertAuthenticatedAs($user);
        $this->get('/admin')->assertOk();
    }
}
