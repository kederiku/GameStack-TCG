<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ReservedGameSlugTest extends TestCase
{
    use RefreshDatabase;

    public function test_reserved_slugs_are_rejected_before_storing_an_image(): void
    {
        Storage::fake('public');
        foreach (['admin', 'dashboard', 'login', 'logout', 'register', 'settings', 'user', 'email', 'forgot-password', 'reset-password', 'two-factor-challenge', 'up', 'storage', 'build', 'livewire', 'api', 'sanctum', 'fonts', 'css', 'js'] as $slug) {
            try {
                app(SaveGame::class)->handle(new Game, [
                    'name' => 'Un jeu', 'slug' => $slug,
                    'image' => UploadedFile::fake()->image('logo.png'),
                ]);
                $this->fail('Reserved slug accepted: '.$slug);
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('slug', $exception->errors());
            }
        }
        $this->assertDatabaseCount('games', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_an_automatically_generated_reserved_slug_is_rejected(): void
    {
        $this->expectException(ValidationException::class);
        app(SaveGame::class)->handle(new Game, ['name' => 'Login']);
    }

    public function test_registered_route_prefixes_are_reserved_even_when_configured_dynamically(): void
    {
        Route::get('service-console/actions', fn () => 'service');
        $this->expectException(ValidationException::class);
        app(SaveGame::class)->handle(new Game, ['name' => 'Un jeu', 'slug' => 'service-console']);
    }

    public function test_changing_a_game_to_a_reserved_slug_preserves_its_original_data(): void
    {
        $game = Game::factory()->create(['name' => 'Pokémon', 'slug' => 'pokemon']);
        try {
            app(SaveGame::class)->handle($game, ['name' => 'Changed', 'slug' => ' admin ']);
            $this->fail('Reserved slug accepted during an update.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('slug', $exception->errors());
        }
        $this->assertDatabaseHas('games', ['id' => $game->id, 'name' => 'Pokémon', 'slug' => 'pokemon']);
    }

    public function test_a_slug_that_only_starts_with_a_reserved_word_remains_available(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Un jeu', 'slug' => 'admin-tcg']);
        $this->get('/fr/admin-tcg')->assertOk();
        $this->assertSame('admin-tcg', $game->slug);
    }
}
