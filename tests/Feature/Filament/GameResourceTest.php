<?php

namespace Tests\Feature\Filament;

use App\Actions\Games\SaveGame;
use App\Filament\Resources\Games\Pages\CreateGame;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Models\Game;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class GameResourceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $user = User::factory()->create();
        $user->forceFill(['is_admin' => true])->save();
        $this->actingAs($user);
    }

    public function test_an_admin_can_create_a_game_with_an_optional_image(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Pokémon', 'slug' => 'pokemon'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('games', ['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => null]);
    }

    public function test_the_name_suggests_a_slug_without_overwriting_a_custom_slug(): void
    {
        Livewire::test(CreateGame::class)
            ->set('data.name', 'Pokémon')
            ->assertSet('data.slug', 'pokemon')
            ->set('data.slug', 'custom')
            ->set('data.name', 'Magic')
            ->assertSet('data.slug', 'custom');
    }

    public function test_uploading_and_replacing_an_image_uses_webp_and_cleans_up(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => UploadedFile::fake()->image('logo.png')])
            ->call('create')
            ->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $old = $game->image;
        $this->assertSame('image/webp', Storage::disk('public')->mimeType($old));

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['name' => 'Pokémon TCG', 'slug' => 'pokemon-tcg', 'image' => UploadedFile::fake()->image('new.jpg')])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('pokemon-tcg', $game->fresh()->slug);
        $this->assertStringEndsWith('/pokemon-tcg.webp', $game->fresh()->image);
        Storage::disk('public')->assertMissing($old);
        $this->assertCount(1, Storage::disk('public')->allFiles('games'));
    }

    public function test_removing_an_image_is_only_applied_when_saving(): void
    {
        $game = $this->createWithImage('Pokémon');
        $old = $game->image;
        $page = Livewire::test(EditGame::class, ['record' => $game->id])->fillForm(['image' => null]);
        Storage::disk('public')->assertExists($old);

        $page->call('save')->assertHasNoFormErrors();

        $this->assertNull($game->fresh()->image);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_svg_can_be_uploaded_renamed_and_deleted_in_filament(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle r="40"/></svg>';
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => UploadedFile::fake()->createWithContent('logo.svg', $svg)])
            ->call('create')->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $old = $game->image;
        $this->assertStringEndsWith('/pokemon.svg', $old);
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['name' => 'Pokémon TCG'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertStringEndsWith('/pokemon-tcg.svg', $game->fresh()->image);
        Storage::disk('public')->assertMissing($old);

        Livewire::test(ListGames::class)->callTableBulkAction(DeleteBulkAction::class, [$game]);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_validation_rejects_duplicate_or_invalid_slugs_and_blank_names(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Pokémon', 'slug' => 'pokemon'])
            ->call('create')->assertHasFormErrors(['slug' => 'unique'])
            ->fillForm(['name' => '', 'slug' => '../bad slug'])
            ->call('create')->assertHasFormErrors(['name' => 'required', 'slug' => 'regex']);
    }

    public function test_validation_errors_do_not_store_an_uploaded_image(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => '', 'slug' => 'new', 'image' => UploadedFile::fake()->image('logo.png')])
            ->call('create')->assertHasFormErrors(['name']);

        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_reserved_slugs_are_reported_on_the_game_form(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Un jeu', 'slug' => 'login'])
            ->call('create')->assertHasFormErrors(['slug']);

        $game = Game::factory()->create(['slug' => 'pokemon']);
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['slug' => 'dashboard'])
            ->call('save')->assertHasFormErrors(['slug']);

        $this->assertSame('pokemon', $game->fresh()->slug);
    }

    public function test_a_tampered_form_cannot_attach_another_games_image(): void
    {
        $first = $this->createWithImage('Pokémon');
        $second = Game::factory()->create();
        Livewire::test(EditGame::class, ['record' => $second->id])
            ->set('data.image', ['tampered' => $first->image])
            ->call('save')->assertHasFormErrors(['image']);

        $this->assertNull($second->fresh()->image);
        Storage::disk('public')->assertExists($first->image);
    }

    public function test_list_search_and_single_deletion_work(): void
    {
        $pokemon = $this->createWithImage('Pokémon');
        $magic = Game::factory()->create(['name' => 'Magic']);
        Livewire::test(ListGames::class)
            ->assertCanSeeTableRecords([$pokemon, $magic])
            ->searchTable('Pokémon')
            ->assertCanSeeTableRecords([$pokemon])
            ->assertCanNotSeeTableRecords([$magic]);

        Livewire::test(EditGame::class, ['record' => $pokemon->id])
            ->callAction(DeleteAction::class);

        $this->assertDatabaseMissing('games', ['id' => $pokemon->id]);
        Storage::disk('public')->assertMissing($pokemon->image);
    }

    public function test_bulk_deletion_cleans_each_image(): void
    {
        $first = $this->createWithImage('Pokémon');
        $second = $this->createWithImage('Magic');
        Livewire::test(ListGames::class)
            ->callTableBulkAction(DeleteBulkAction::class, [$first, $second]);

        $this->assertDatabaseCount('games', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_editing_twice_without_reloading_keeps_the_stored_image(): void
    {
        $game = $this->createWithImage('Pokémon');
        $page = Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['name' => 'Pokémon TCG'])
            ->call('save')->assertHasNoFormErrors();
        $image = $game->fresh()->image;

        $page->fillForm(['slug' => 'custom'])->call('save')->assertHasNoFormErrors();

        $this->assertSame($image, $game->fresh()->image);
        Storage::disk('public')->assertExists($image);
    }

    public function test_only_verified_administrators_can_access_game_pages(): void
    {
        $game = Game::factory()->create();
        foreach (['/admin/games', '/admin/games/create', '/admin/games/'.$game->id.'/edit'] as $url) {
            $this->get($url)->assertOk();
        }

        $this->actingAs(User::factory()->create());
        Livewire::test(CreateGame::class)->assertForbidden();
        $this->get('/admin/games')->assertForbidden();

        $unverified = User::factory()->unverified()->create();
        $unverified->forceFill(['is_admin' => true])->save();
        $this->actingAs($unverified);
        $this->get('/admin/games')->assertForbidden();
    }

    private function createWithImage(string $name): Game
    {
        return app(SaveGame::class)->handle(new Game, ['name' => $name, 'image' => UploadedFile::fake()->image('logo.png')]);
    }
}
