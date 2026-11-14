<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class PublicGameTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_landing_exposes_sorted_public_games_with_optional_images(): void
    {
        Game::factory()->create(['name' => 'Yu-Gi-Oh!', 'slug' => 'yugioh']);
        Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => 'games/pokemon.svg']);
        Game::factory()->create(['name' => 'Magic', 'slug' => 'magic', 'image' => 'games/magic.webp']);

        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('currentGame', null)
            ->where('canRegister', true)
            ->has('games', 3)
            ->where('games.0.name', 'Magic')
            ->where('games.0.url', '/magic')
            ->where('games.0.imageUrl', url('/storage/games/magic.webp'))
            ->where('games.1.slug', 'pokemon')
            ->where('games.1.imageUrl', url('/storage/games/pokemon.svg'))
            ->where('games.2.imageUrl', null)
            ->missing('games.0.created_at')
            ->missing('games.0.image'));
    }

    public function test_the_landing_works_without_any_game(): void
    {
        $this->get('/')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('games', 0)->where('currentGame', null));
    }

    public function test_a_game_is_publicly_accessible_by_its_custom_slug_not_its_id(): void
    {
        $game = Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon-custom']);

        $this->get('/pokemon-custom')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('games/show', false)
            ->where('currentGame.id', $game->id)
            ->where('currentGame.slug', 'pokemon-custom')
            ->where('currentGame.url', '/pokemon-custom')
            ->has('games', 1));
        $this->get('/'.$game->id)->assertNotFound();
    }

    public function test_navigation_resolves_the_current_game_from_each_url(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        Game::factory()->create(['slug' => 'yugioh']);

        foreach (['pokemon', 'yugioh', 'pokemon'] as $slug) {
            $this->get('/'.$slug)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('currentGame.slug', $slug)->has('games', 2));
        }
    }

    public function test_a_changed_or_deleted_slug_is_no_longer_accessible(): void
    {
        $game = Game::factory()->create(['slug' => 'original']);
        $game->update(['slug' => 'renamed']);
        $this->get('/original')->assertNotFound();
        $this->get('/renamed')->assertOk();
        $game->delete();
        $this->get('/renamed')->assertNotFound();
    }

    public function test_unknown_and_malformed_game_paths_are_not_found(): void
    {
        foreach (['absent', 'not/a/game', 'UPPERCASE', 'bad_slug'] as $path) {
            $this->get('/'.$path)->assertNotFound();
        }
    }

    public function test_existing_reserved_slugs_never_become_public_game_links(): void
    {
        foreach (['login', 'register', 'dashboard', 'admin', 'up', 'storage', 'build'] as $slug) {
            Game::factory()->create(['slug' => $slug]);
        }
        Game::factory()->create(['slug' => 'pokemon']);

        $this->get('/')->assertInertia(fn (Assert $page) => $page
            ->has('games', 1)->where('games.0.slug', 'pokemon'));
        $this->get('/login')->assertInertia(fn (Assert $page) => $page->component('auth/login'));
        $this->get('/register')->assertInertia(fn (Assert $page) => $page->component('auth/register'));
        $this->get('/dashboard')->assertRedirect('/login');
        $this->get('/admin')->assertRedirect();
        $this->get('/up')->assertOk();
        $this->get('/storage')->assertNotFound();
        $this->get('/build')->assertNotFound();
    }

    public function test_public_pages_preserve_the_signed_in_account(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['slug' => 'pokemon']);
        $this->actingAs($user)->get('/pokemon')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)->missing('auth.user.password'));
    }

    public function test_registration_visibility_tracks_the_enabled_feature(): void
    {
        config(['fortify.features' => []]);
        $this->get('/')->assertInertia(fn (Assert $page) => $page->where('canRegister', false));
    }

    public function test_long_names_and_absent_images_reach_the_public_page_unchanged(): void
    {
        $name = str_repeat('Jeu ', 63).'TCG';
        Game::factory()->create(['name' => $name, 'slug' => 'long-name']);
        $this->get('/long-name')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('currentGame.name', $name)->where('currentGame.imageUrl', null));
    }
}
