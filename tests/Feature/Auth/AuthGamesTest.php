<?php

namespace Tests\Feature\Auth;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class AuthGamesTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_guest_auth_pages_receive_the_public_catalogue(): void
    {
        $pokemonImage = 'games/860c07f9-10f0-4c9c-ad96-21327f4559ed/pokemon.avif';
        Game::factory()->create(['name' => 'Yu-Gi-Oh!', 'slug' => 'yugioh']);
        Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => $pokemonImage]);
        Game::factory()->create(['name' => 'Reserved', 'slug' => 'login']);
        Game::factory()->create(['name' => 'Hidden', 'slug' => 'hidden', 'is_visible' => false]);

        foreach (['/fr/connexion', '/fr/inscription', '/fr/mot-de-passe-oublie', '/fr/reinitialiser-mot-de-passe/test-token?email=test@example.test'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->has('games', 2)
                ->where('games.0.name', 'Pokémon')
                ->where('games.0.imageUrl', url('/storage/'.$pokemonImage))
                ->where('games.0.url', '/fr/pokemon')
                ->where('games.1.name', 'Yu-Gi-Oh!')
                ->where('games.1.imageUrl', null)
                ->missing('games.0.image')
                ->missing('games.0.created_at'));
        }
    }

    public function test_protected_auth_pages_receive_games_too(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        Game::factory()->create(['slug' => 'hidden', 'is_visible' => false]);
        $user = User::factory()->unverified()->create();

        foreach (['verification.notice', 'password.confirm'] as $route) {
            $this->actingAs($user)->get(route('site.fr.'.$route))->assertOk()->assertInertia(fn (Assert $page) => $page
                ->has('games', 1)->where('games.0.slug', 'pokemon'));
        }
    }

    public function test_two_factor_challenge_receives_games(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        Game::factory()->create(['slug' => 'hidden', 'is_visible' => false]);
        $user = User::factory()->withTwoFactor()->create();

        $this->post(route('site.fr.login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('site.fr.two-factor.login'));
        $this->get(route('site.fr.two-factor.login'))->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('auth/two-factor-challenge')
            ->has('games', 1)->where('games.0.slug', 'pokemon'));
    }

    public function test_auth_pages_allow_an_empty_catalogue(): void
    {
        $this->get('/fr/connexion')->assertOk()->assertInertia(fn (Assert $page) => $page->has('games', 0));
    }

    public function test_account_pages_do_not_receive_the_auth_catalogue(): void
    {
        $this->actingAs(User::factory()->create())->get(route('site.fr.profile.edit'))
            ->assertOk()->assertInertia(fn (Assert $page) => $page->missing('games'));
    }
}
