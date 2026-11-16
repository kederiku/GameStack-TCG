<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameVisibilityTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_games_are_visible_by_default(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Un jeu']);

        $this->assertTrue($game->is_visible);
        $this->assertTrue($game->fresh()->is_visible);
        $this->get('/fr/un-jeu')->assertOk();
    }

    public function test_hidden_games_keep_their_visibility_when_it_is_omitted(): void
    {
        $game = app(SaveGame::class)->handle(new Game, [
            'name' => 'Un jeu', 'is_visible' => false,
            'image' => UploadedFile::fake()->image('logo.png'),
            'publisher' => 'Un éditeur',
        ]);
        $image = $game->image;
        $this->assertFalse($game->is_visible);

        $updated = app(SaveGame::class)->handle($game, [
            'name' => $game->name, 'description' => 'Une description.',
        ]);

        $this->assertFalse($updated->fresh()->is_visible);
        $this->assertSame('Un éditeur', $updated->publisher);
        $this->assertSame($image, $updated->image);
        Storage::disk('public')->assertExists($image);
    }

    public function test_hiding_and_showing_a_game_updates_lists_and_direct_access(): void
    {
        $game = Game::factory()->create(['name' => 'Un jeu', 'slug' => 'un-jeu']);
        $other = Game::factory()->create(['name' => 'Autre jeu', 'slug' => 'autre-jeu']);
        $this->get('/fr/un-jeu')->assertOk();

        app(SaveGame::class)->handle($game, ['name' => $game->name, 'is_visible' => false]);

        foreach (['/fr', '/fr/autre-jeu', '/login'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page
                ->has('games', 1)->where('games.0.id', $other->id));
        }
        $this->get('/fr/un-jeu')->assertNotFound();

        app(SaveGame::class)->handle($game, ['name' => $game->name, 'is_visible' => true]);

        $this->get('/fr/un-jeu')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('currentGame.id', $game->id)->has('games', 2));
    }

    public function test_a_stale_partial_save_does_not_republish_a_hidden_game(): void
    {
        $game = Game::factory()->create();
        app(SaveGame::class)->handle($game, ['name' => $game->name, 'is_visible' => false]);

        app(SaveGame::class)->handle($game, ['name' => $game->name, 'publisher' => 'Un éditeur']);

        $this->assertFalse($game->fresh()->is_visible);
    }

    public function test_signed_in_users_cannot_open_a_hidden_public_game_page(): void
    {
        Game::factory()->create(['slug' => 'hidden', 'is_visible' => false]);
        $user = User::factory()->create();
        $this->actingAs($user)->get('/fr/hidden')->assertNotFound();

        $user->forceFill(['is_admin' => true])->save();
        $this->get('/fr/hidden')->assertNotFound();
    }

    public function test_the_site_allows_a_catalogue_containing_only_hidden_games(): void
    {
        Game::factory()->create(['is_visible' => false]);

        foreach (['/fr', '/login'] as $url) {
            $this->get($url)->assertOk()->assertInertia(fn (Assert $page) => $page->has('games', 0));
        }

        $this->actingAs(User::factory()->create())->get('/fr/mon-espace')->assertOk()
            ->assertInertia(fn (Assert $page) => $page->has('games', 0));
    }

    #[DataProvider('invalidVisibility')]
    public function test_invalid_visibility_is_rejected_before_storing_an_image(mixed $visibility): void
    {
        try {
            app(SaveGame::class)->handle(new Game, [
                'name' => 'Un jeu', 'is_visible' => $visibility,
                'image' => UploadedFile::fake()->image('logo.png'),
            ]);
            $this->fail('Invalid visibility was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('is_visible', $exception->errors());
        }

        $this->assertDatabaseCount('games', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public static function invalidVisibility(): array
    {
        return [
            'null' => [null],
            'empty' => [''],
            'false as text' => ['false'],
            'invalid number' => [2],
            'array' => [[false]],
        ];
    }
}
