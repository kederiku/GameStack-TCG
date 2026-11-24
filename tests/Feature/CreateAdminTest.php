<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class CreateAdminTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_an_administrator_can_be_created_interactively(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Name', 'Admin')
            ->expectsQuestion('Email address', 'admin@example.com')
            ->expectsQuestion('Password (at least 12 characters)', 'AdminPassword123!')
            ->expectsQuestion('Confirm password', 'AdminPassword123!')
            ->assertSuccessful();

        $user = User::query()->where('email', 'admin@example.com')->sole();

        $this->assertTrue($user->is_admin);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertTrue(Hash::check('AdminPassword123!', $user->password));
    }

    public function test_existing_users_are_not_silently_promoted(): void
    {
        $user = User::factory()->create(['email' => 'user@example.com']);

        $this->artisan('app:create-admin')
            ->expectsQuestion('Name', 'Admin')
            ->expectsQuestion('Email address', 'USER@example.com')
            ->expectsQuestion('Password (at least 12 characters)', 'AdminPassword123!')
            ->expectsQuestion('Confirm password', 'AdminPassword123!')
            ->assertFailed();

        $this->assertFalse($user->fresh()->is_admin);
        $this->assertDatabaseCount('users', 1);
    }

    public function test_invalid_passwords_do_not_create_users(): void
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Name', 'Admin')
            ->expectsQuestion('Email address', 'admin@example.com')
            ->expectsQuestion('Password (at least 12 characters)', 'short')
            ->expectsQuestion('Confirm password', 'different')
            ->assertFailed();

        $this->assertDatabaseCount('users', 0);
    }
}
