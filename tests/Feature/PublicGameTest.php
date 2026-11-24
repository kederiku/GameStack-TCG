<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\ForwardOnlyRefreshDatabase;
use Tests\TestCase;

class PublicGameTest extends TestCase
{
    use ForwardOnlyRefreshDatabase;

    public function test_the_landing_exposes_sorted_public_games_with_optional_images(): void
    {
        Game::factory()->create(['name' => 'Yu-Gi-Oh!', 'slug' => 'yugioh']);
        Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => 'games/pokemon.svg']);
        Game::factory()->create(['name' => 'Magic', 'slug' => 'magic', 'image' => 'games/magic.webp']);

        $this->get('/fr')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('welcome')
            ->where('currentGame', null)
            ->where('canRegister', true)
            ->has('games', 3)
            ->where('games.0.name', 'Magic')
            ->where('games.0.nameLanguage', null)
            ->where('games.0.nameFallback', true)
            ->where('games.0.url', '/fr/magic')
            ->where('games.0.imageUrl', url('/storage/games/magic.webp'))
            ->where('games.1.slug', 'pokemon')
            ->where('games.1.imageUrl', url('/storage/games/pokemon.svg'))
            ->where('games.2.imageUrl', null)
            ->missing('games.0.created_at')
            ->missing('games.0.image'));
    }

    public function test_games_sort_by_the_resolved_name_then_id_in_each_interface_language(): void
    {
        $first = Game::factory()->create(['name' => 'Zulu', 'display_name' => ['fr' => 'Alpha', 'en' => 'Zulu']]);
        $second = Game::factory()->create(['name' => 'Alpha', 'display_name' => ['fr' => 'Alpha']]);
        $this->get('/fr')->assertInertia(fn (Assert $page) => $page
            ->where('games.0.id', $first->id)->where('games.1.id', $second->id));
        $this->get('/en')->assertInertia(fn (Assert $page) => $page
            ->where('games.0.id', $second->id)->where('games.0.nameFallback', true)->where('games.0.nameLanguage', 'fr')
            ->where('games.1.id', $first->id)->where('games.1.nameLanguage', 'en'));
    }

    public function test_the_landing_works_without_any_game(): void
    {
        $this->get('/fr')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->has('games', 0)->where('currentGame', null));
    }

    public function test_a_game_is_publicly_accessible_by_its_custom_slug_not_its_id(): void
    {
        $game = Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon-custom']);

        $this->get('/fr/pokemon-custom')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->component('games/show', false)
            ->where('currentGame.id', $game->id)
            ->where('currentGame.slug', 'pokemon-custom')
            ->where('currentGame.url', '/fr/pokemon-custom')
            ->has('games', 1));
        $this->get('/fr/'.$game->id)->assertNotFound();
    }

    public function test_navigation_resolves_the_current_game_from_each_url(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        Game::factory()->create(['slug' => 'yugioh']);

        foreach (['pokemon', 'yugioh', 'pokemon'] as $slug) {
            $this->get('/fr/'.$slug)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->where('currentGame.slug', $slug)->has('games', 2));
        }
    }

    public function test_a_changed_or_deleted_slug_is_no_longer_accessible(): void
    {
        $game = Game::factory()->create(['slug' => 'original']);
        $game->update(['slug' => 'renamed']);
        $this->get('/fr/original')->assertNotFound();
        $this->get('/fr/renamed')->assertOk();
        $game->delete();
        $this->get('/fr/renamed')->assertNotFound();
    }

    public function test_unknown_and_malformed_game_paths_are_not_found(): void
    {
        foreach (['absent', 'not/a/game', 'UPPERCASE', 'bad_slug'] as $path) {
            $this->get('/fr/'.$path)->assertNotFound();
        }
    }

    public function test_existing_reserved_slugs_never_become_public_game_links(): void
    {
        foreach (['login', 'register', 'dashboard', 'admin', 'up', 'storage', 'build'] as $slug) {
            Game::factory()->create(['slug' => $slug]);
        }
        Game::factory()->create(['slug' => 'pokemon']);

        $this->get('/fr')->assertInertia(fn (Assert $page) => $page
            ->has('games', 1)->where('games.0.slug', 'pokemon'));
        $this->get('/fr/connexion')->assertInertia(fn (Assert $page) => $page->component('auth/login'));
        $this->get('/fr/inscription')->assertInertia(fn (Assert $page) => $page->component('auth/register'));
        $this->get('/fr/mon-espace')->assertRedirect('/fr/connexion');
        $this->get('/admin')->assertRedirect();
        $this->get('/up')->assertOk();
        $this->get('/storage')->assertNotFound();
        $this->get('/build')->assertNotFound();
    }

    public function test_public_pages_preserve_the_signed_in_account(): void
    {
        $user = User::factory()->create();
        Game::factory()->create(['slug' => 'pokemon']);
        $this->actingAs($user)->get('/fr/pokemon')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('auth.user.id', $user->id)->missing('auth.user.password'));
    }

    public function test_registration_visibility_tracks_the_enabled_feature(): void
    {
        config(['fortify.features' => []]);
        $this->get('/fr')->assertInertia(fn (Assert $page) => $page->where('canRegister', false));
    }

    public function test_long_names_and_absent_images_reach_the_public_page_unchanged(): void
    {
        $name = str_repeat('Jeu ', 63).'TCG';
        Game::factory()->create(['name' => $name, 'slug' => 'long-name']);
        $this->get('/fr/long-name')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('currentGame.name', $name)->where('currentGame.imageUrl', null));
    }
}
