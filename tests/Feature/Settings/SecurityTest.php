<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;
use Laravel\Fortify\Features;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_security_page_is_displayed()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);
        Features::passkeys([
            'confirmPassword' => true,
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('site.fr.security.edit'))
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/security')
                ->where('canManagePasskeys', true)
                ->where('passkeys', [])
                ->where('canManageTwoFactor', true)
                ->where('twoFactorEnabled', false),
            );
    }

    public function test_security_page_requires_password_confirmation_when_enabled()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        $user = User::factory()->create();

        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]);

        $response = $this->actingAs($user)
            ->get(route('site.fr.security.edit'));

        $response->assertRedirect(route('site.fr.password.confirm'));
    }

    public function test_security_page_renders_without_two_factor_when_feature_is_disabled()
    {
        $this->skipUnlessFortifyHas(Features::twoFactorAuthentication());

        config(['fortify.features' => []]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('site.fr.security.edit'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('settings/security')
                ->where('canManagePasskeys', false)
                ->where('passkeys', [])
                ->where('canManageTwoFactor', false)
                ->missing('twoFactorEnabled')
                ->missing('requiresConfirmation'),
            );
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('site.fr.security.edit'))
            ->put(route('site.fr.user-password.update'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('site.fr.security.edit'));

        $this->assertTrue(Hash::check('new-password', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('site.fr.security.edit'))
            ->put(route('site.fr.user-password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('site.fr.security.edit'));
    }

    public function test_users_cannot_delete_another_accounts_passkey(): void
    {
        $owner = User::factory()->create();
        $passkey = $owner->passkeys()->create([
            'name' => 'Laptop', 'credential_id' => 'owner-key', 'credential' => [],
        ]);

        $this->actingAs(User::factory()->create())
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('passkey.destroy', $passkey))
            ->assertForbidden();

        $this->assertModelExists($passkey);
    }

    public function test_the_owner_must_confirm_their_password_before_deleting_a_passkey(): void
    {
        $owner = User::factory()->create();
        $passkey = $owner->passkeys()->create([
            'name' => 'Laptop', 'credential_id' => 'owner-key', 'credential' => [],
        ]);

        $this->actingAs($owner)->delete(route('passkey.destroy', $passkey))
            ->assertRedirect(route('site.fr.password.confirm'));
        $this->assertModelExists($passkey);

        $this->withSession(['auth.password_confirmed_at' => time()])
            ->from(route('site.fr.security.edit'))
            ->delete(route('passkey.destroy', $passkey))
            ->assertRedirect(route('site.fr.security.edit'));
        $this->assertModelMissing($passkey);
    }

    public function test_security_only_exposes_the_owners_passkey_metadata(): void
    {
        $owner = User::factory()->create();
        $passkey = $owner->passkeys()->create([
            'name' => 'My laptop', 'credential_id' => 'owner-key', 'credential' => ['secret' => 'private-data'],
        ]);
        User::factory()->create()->passkeys()->create([
            'name' => 'Another account', 'credential_id' => 'other-key', 'credential' => [],
        ]);

        $this->actingAs($owner)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->get(route('site.fr.security.edit'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->has('passkeys', 1)
                ->where('passkeys.0.id', $passkey->id)
                ->where('passkeys.0.name', 'My laptop')
                ->missing('passkeys.0.credential')
                ->missing('passkeys.0.credential_id')
                ->missing('passkeys.0.user_id'));
    }
}
