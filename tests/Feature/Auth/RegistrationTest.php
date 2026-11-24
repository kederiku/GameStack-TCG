<?php

namespace Tests\Feature\Auth;

use Laravel\Fortify\Features;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('site.fr.register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('site.fr.register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('site.fr.dashboard', absolute: false));
    }
}
