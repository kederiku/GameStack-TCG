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
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
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

        $this->assertDatabaseHas('games', [
            'name' => 'Pokémon', 'slug' => 'pokemon', 'image' => null, 'is_visible' => true,
            'start_year' => null, 'end_year' => null, 'publisher' => null, 'description' => null,
        ]);
    }

    public function test_an_admin_can_create_a_hidden_game_and_show_it_later(): void
    {
        Livewire::test(CreateGame::class)
            ->assertFormSet(['is_visible' => true])
            ->fillForm(['name' => 'Un jeu', 'slug' => 'un-jeu', 'is_visible' => false])
            ->call('create')->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $this->assertFalse($game->is_visible);
        $this->get('/fr/un-jeu')->assertNotFound();

        Livewire::test(ListGames::class)
            ->assertCanSeeTableRecords([$game])
            ->assertTableColumnStateSet('is_visible', false, $game);

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->assertFormSet(['is_visible' => false])
            ->fillForm(['is_visible' => true])
            ->call('save')->assertHasNoFormErrors();

        $this->assertTrue($game->fresh()->is_visible);
        $this->get('/fr/un-jeu')->assertOk();
    }

    public function test_an_admin_can_hide_a_game_and_edit_it_without_republishing_it(): void
    {
        $game = Game::factory()->create(['slug' => 'un-jeu']);
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['is_visible' => false])
            ->call('save')->assertHasNoFormErrors();

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['description' => 'Une description.'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertFalse($game->fresh()->is_visible);
        $this->assertSame('Une description.', $game->fresh()->description);
        $this->get('/fr/un-jeu')->assertNotFound();
    }

    public function test_an_admin_can_create_edit_and_clear_optional_game_details(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm([
                'name' => 'Un TCG', 'slug' => 'un-tcg',
                'start_year' => 2024, 'end_year' => 2026,
                'publisher' => 'Un éditeur', 'description' => "Un TCG.\nSa description.",
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $this->assertSame(2024, $game->start_year);
        $this->assertSame(2026, $game->end_year);
        $this->assertSame('Un éditeur', $game->publisher);
        $this->assertSame("Un TCG.\nSa description.", $game->description);

        $page = Livewire::test(EditGame::class, ['record' => $game->id])
            ->assertFormSet([
                'start_year' => 2024, 'end_year' => 2026,
                'publisher' => 'Un éditeur', 'description' => "Un TCG.\nSa description.",
            ])
            ->fillForm([
                'start_year' => 2023, 'end_year' => 2025,
                'publisher' => 'Autre éditeur', 'description' => 'Description révisée.',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('games', [
            'id' => $game->id, 'start_year' => 2023, 'end_year' => 2025,
            'publisher' => 'Autre éditeur', 'description' => 'Description révisée.',
        ]);

        $page->fillForm(['start_year' => '', 'end_year' => '', 'publisher' => '', 'description' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('games', [
            'id' => $game->id, 'start_year' => null, 'end_year' => null,
            'publisher' => null, 'description' => null,
        ]);
    }

    public function test_each_year_is_optional_in_filament(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Un TCG', 'slug' => 'un-tcg', 'end_year' => 2026])
            ->call('create')
            ->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $this->assertNull($game->start_year);
        $this->assertSame(2026, $game->end_year);

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['start_year' => 1993, 'end_year' => null])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame(1993, $game->fresh()->start_year);
        $this->assertNull($game->fresh()->end_year);
    }

    public function test_filament_rejects_invalid_years_and_a_long_publisher(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm([
                'name' => 'Un TCG', 'slug' => 'un-tcg',
                'start_year' => 2024.5, 'end_year' => 10000,
                'publisher' => str_repeat('a', 256),
            ])
            ->call('create')
            ->assertHasFormErrors(['start_year', 'end_year', 'publisher']);

        $this->assertDatabaseCount('games', 0);
    }

    public function test_filament_reports_an_end_year_before_creation_without_saving_changes(): void
    {
        $game = Game::factory()->create(['start_year' => 2024, 'end_year' => 2026]);

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['start_year' => 2027])
            ->call('save')
            ->assertHasFormErrors(['end_year'])
            ->assertDispatched('form-validation-error');

        $this->assertSame(2024, $game->fresh()->start_year);
        $this->assertSame(2026, $game->fresh()->end_year);
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

    public function test_game_forms_stay_stacked_until_the_sidebar_breakpoint(): void
    {
        $game = Game::factory()->create();

        foreach ([Livewire::test(CreateGame::class), Livewire::test(EditGame::class, ['record' => $game->id])] as $page) {
            $columns = $page->instance()->getSchema('form')->getColumns();

            $this->assertSame(1, $columns['default']);
            $this->assertSame(1, $columns['lg']);
            $this->assertSame(3, $columns['xl']);
        }
    }

    public function test_uploading_and_replacing_an_image_uses_avif_and_cleans_up(): void
    {
        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'Pokémon', 'slug' => 'pokemon', 'image' => UploadedFile::fake()->image('logo.png')])
            ->call('create')
            ->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $old = $game->image;
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($old));

        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['name' => 'Pokémon TCG', 'slug' => 'pokemon-tcg', 'image' => UploadedFile::fake()->image('new.jpg')])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('pokemon-tcg', $game->fresh()->slug);
        $this->assertStringEndsWith('/pokemon-tcg.avif', $game->fresh()->image);
        Storage::disk('public')->assertMissing($old);
        $this->assertCount(1, Storage::disk('public')->allFiles('games'));
    }

    public function test_an_avif_upload_has_a_preview_and_can_be_saved_and_edited(): void
    {
        $image = imagecreatetruecolor(64, 32);
        ob_start();
        imageavif($image);
        $contents = ob_get_clean();
        Storage::disk('public')->put('livewire-tmp/preview.avif', $contents);
        $temporary = new TemporaryUploadedFile('preview.avif', 'public');
        $this->assertNotEmpty($temporary->temporaryUrl());

        Livewire::test(CreateGame::class)
            ->fillForm(['name' => 'AVIF', 'slug' => 'avif', 'image' => UploadedFile::fake()->createWithContent('logo.avif', $contents)])
            ->call('create')->assertHasNoFormErrors();

        $game = Game::query()->sole();
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($game->image));
        $this->assertSame([64, 32], array_slice(getimagesize(Storage::disk('public')->path($game->image)), 0, 2));
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['publisher' => 'An editor'])->call('save')->assertHasNoFormErrors();
        $this->assertSame($game->image, $game->fresh()->image);
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
        $this->assertStringEndsWith('/pokemon.avif', $old);
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->fillForm(['name' => 'Pokémon TCG'])
            ->call('save')->assertHasNoFormErrors();
        $this->assertStringEndsWith('/pokemon-tcg.avif', $game->fresh()->image);
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

    public function test_list_search_finds_a_game_by_its_slug_when_the_column_is_hidden(): void
    {
        $game = Game::factory()->create(['name' => 'Un jeu', 'slug' => 'custom-identifier']);
        $other = Game::factory()->create(['name' => 'Autre jeu', 'slug' => 'other']);

        Livewire::test(ListGames::class)
            ->searchTable('custom-identifier')
            ->assertCanSeeTableRecords([$game])
            ->assertCanNotSeeTableRecords([$other]);
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

    public function test_a_previously_open_delete_action_cannot_run_after_admin_access_is_revoked(): void
    {
        $game = $this->createWithImage('Pokémon');
        $page = Livewire::test(EditGame::class, ['record' => $game->id])
            ->mountAction(DeleteAction::class)->assertActionMounted(DeleteAction::class);

        $this->actingAs(User::factory()->create());
        $page->call('callMountedAction');

        $this->assertModelExists($game);
        Storage::disk('public')->assertExists($game->image);
    }

    public function test_a_previously_open_bulk_delete_cannot_run_after_admin_access_is_revoked(): void
    {
        $games = [$this->createWithImage('Pokémon'), $this->createWithImage('Magic')];
        $page = Livewire::test(ListGames::class)
            ->mountTableBulkAction(DeleteBulkAction::class, $games)
            ->assertTableBulkActionMounted(DeleteBulkAction::class);

        $this->actingAs(User::factory()->create());
        $page->call('callMountedAction');

        foreach ($games as $game) {
            $this->assertModelExists($game);
            Storage::disk('public')->assertExists($game->image);
        }
    }
}
