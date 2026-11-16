<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('site.fr.dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('site.fr.dashboard'));
        $response->assertOk();
    }

    public function test_unverified_users_must_verify_their_email(): void
    {
        $this->actingAs(User::factory()->unverified()->create())
            ->get(route('site.fr.dashboard'))->assertRedirect(route('verification.notice'));
    }

    public function test_dashboard_exposes_sorted_public_games_and_preserves_the_account(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => null]);
        Game::factory()->create(['name' => 'Magic', 'slug' => 'magic', 'image' => 'games/magic.svg']);
        Game::factory()->create(['name' => 'Admin', 'slug' => 'admin']);
        Game::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'is_visible' => false]);

        $this->actingAs($user)->get(route('site.fr.dashboard'))->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('dashboard')
                ->where('auth.user.id', $user->id)
                ->missing('auth.user.password')
                ->has('games', 2)
                ->where('games.0.name', 'Magic')
                ->where('games.0.url', '/fr/magic')
                ->where('games.0.imageUrl', url('/storage/games/magic.svg'))
                ->where('games.1.name', 'Pokémon')
                ->where('games.1.url', '/fr/pokemon')
                ->where('games.1.imageUrl', null)
                ->missing('games.0.image')
                ->missing('games.0.created_at'));
    }

    public function test_dashboard_works_without_games(): void
    {
        $this->actingAs(User::factory()->create())->get(route('site.fr.dashboard'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('dashboard')->has('games', 0));
    }
}
