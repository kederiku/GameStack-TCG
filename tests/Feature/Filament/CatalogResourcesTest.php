<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Catalogs\Pages\CreateCatalog;
use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Catalogs\Pages\ListCatalogs;
use App\Filament\Resources\ExpansionEditions\Pages\EditExpansionEdition;
use App\Filament\Resources\ExpansionEditions\RelationManagers\ReleasesRelationManager;
use App\Filament\Resources\Expansions\Pages\CreateExpansion;
use App\Filament\Resources\Expansions\Pages\EditExpansion;
use App\Filament\Resources\Expansions\Pages\ListExpansions;
use App\Filament\Resources\Expansions\RelationManagers\EditionsRelationManager;
use App\Filament\Resources\Expansions\RelationManagers\ExpansionLocalizationsRelationManager;
use App\Filament\Resources\Expansions\RelationManagers\RelationsRelationManager;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Resources\Languages\Pages\CreateLanguage;
use App\Filament\Resources\Languages\Pages\EditLanguage;
use App\Filament\Resources\Markets\Pages\CreateMarket;
use App\Filament\Resources\Markets\Pages\EditMarket;
use App\Filament\Resources\Series\Pages\CreateSeries;
use App\Filament\Resources\Series\Pages\EditSeries;
use App\Filament\Resources\Series\RelationManagers\SeriesLocalizationsRelationManager;
use App\Models\Catalog;
use App\Models\ExpansionRelation;
use App\Models\Game;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class CatalogResourcesTest extends TestCase
{
    use BuildsCatalog, DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        config(['game-images.disk' => 'public']);
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_an_administrator_creates_catalogue_records_with_cjk_names(): void
    {
        $game = Game::factory()->create();

        Livewire::test(CreateLanguage::class)->fillForm(['code' => 'ja', 'name' => '日本語'])
            ->call('create')->assertHasNoFormErrors();
        Livewire::test(CreateMarket::class)->fillForm(['key' => 'japon', 'name' => '日本'])
            ->call('create')->assertHasNoFormErrors();
        $language = $this->language('ja');
        Livewire::test(CreateCatalog::class, ['game' => $game->id])
            ->assertFormSet(['game_id' => $game->id])
            ->fillForm(['default_language_id' => $language->id, 'key' => 'japon', 'name' => '日本版'])
            ->call('create')->assertHasNoFormErrors();

        $catalog = Catalog::query()->sole();
        Livewire::test(CreateSeries::class)->fillForm([
            'catalog_id' => $catalog->id, 'type' => 'block', 'key' => 'block', 'name' => 'ブロック',
        ])->call('create')->assertHasNoFormErrors();
        $series = $catalog->series()->sole();
        Livewire::test(CreateExpansion::class)->fillForm([
            'catalog_id' => $catalog->id, 'series_id' => $series->id, 'type' => 'special', 'name' => '拡張パック',
        ])->call('create')->assertHasNoFormErrors();
        $expansion = $catalog->expansions()->sole();

        Livewire::test(EditCatalog::class, ['record' => $catalog->id])
            ->fillForm(['name' => '新版'])->call('save')->assertHasNoFormErrors();
        Livewire::test(EditSeries::class, ['record' => $series->id])
            ->fillForm(['name' => '新シリーズ'])->call('save')->assertHasNoFormErrors();
        Livewire::test(EditExpansion::class, ['record' => $expansion->id])
            ->fillForm(['name' => '新拡張パック'])->call('save')->assertHasNoFormErrors();
        Livewire::test(EditLanguage::class, ['record' => $language->id])
            ->fillForm(['name' => '日本語（日本）'])->call('save')->assertHasNoFormErrors();
        $market = $this->market('japon');
        Livewire::test(EditMarket::class, ['record' => $market->id])
            ->fillForm(['name' => '日本国内'])->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseHas('catalogs', ['id' => $catalog->id, 'key' => 'japon', 'name' => '新版']);
        $this->assertDatabaseHas('series', ['id' => $series->id, 'name' => '新シリーズ']);
        $this->assertDatabaseHas('expansions', ['id' => $expansion->id, 'name' => '新拡張パック']);
        $this->assertDatabaseHas('languages', ['id' => $language->id, 'name' => '日本語（日本）']);
        $this->assertDatabaseHas('markets', ['id' => $market->id, 'name' => '日本国内']);
    }

    public function test_an_expansion_cannot_be_saved_with_a_series_from_another_catalogue(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($this->catalog());

        Livewire::test(CreateExpansion::class)->fillForm([
            'catalog_id' => $catalog->id, 'series_id' => $series->id, 'type' => 'main', 'name' => 'Une extension',
        ])->call('create')->assertHasFormErrors(['series_id']);

        $this->assertDatabaseCount('expansions', 0);
    }

    public function test_parent_and_key_are_disabled_and_cannot_be_changed_by_tampering(): void
    {
        $catalog = $this->catalog();
        $otherGame = Game::factory()->create();
        Livewire::test(EditCatalog::class, ['record' => $catalog->id])
            ->assertFormFieldIsDisabled('key')
            ->assertFormFieldIsDisabled('game_id')
            ->set('data.key', 'changed')
            ->set('data.game_id', $otherGame->id)
            ->fillForm(['name' => 'Nouveau'])
            ->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseHas('catalogs', [
            'id' => $catalog->id, 'key' => 'international', 'game_id' => $catalog->game_id, 'name' => 'Nouveau',
        ]);
    }

    public function test_an_expansion_localization_does_not_create_a_printed_edition(): void
    {
        $expansion = $this->expansion($this->catalog());
        $language = $this->language('ja', 'Japonais');
        Livewire::test(ExpansionLocalizationsRelationManager::class, [
            'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
        ])->callTableAction(CreateAction::class, data: [
            'language_id' => $language->id, 'name' => 'ポケモンカード', 'description' => '拡張パック',
        ])->assertHasNoActionErrors();

        $this->assertDatabaseHas('expansion_localizations', ['language_id' => $language->id, 'name' => 'ポケモンカード', 'expansion_id' => $expansion->id]);
        $this->assertDatabaseCount('expansion_editions', 0);
    }

    public function test_localized_series_and_printed_editions_have_independent_avif_images(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $language = $this->language();
        Livewire::test(SeriesLocalizationsRelationManager::class, [
            'ownerRecord' => $series, 'pageClass' => EditSeries::class,
        ])->callTableAction(CreateAction::class, data: [
            'language_id' => $language->id, 'name' => 'Series', 'image' => UploadedFile::fake()->image('series.png'),
        ])->assertHasNoActionErrors();
        Livewire::test(EditionsRelationManager::class, [
            'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
        ])->callTableAction(CreateAction::class, data: [
            'language_id' => $language->id, 'name' => 'Edition', 'status' => 'announced', 'image' => UploadedFile::fake()->image('edition.png'),
        ])->assertHasNoActionErrors();

        $edition = $expansion->editions()->sole();
        $seriesImage = $series->localizations()->sole()->image;
        $this->assertNotNull($seriesImage);
        $this->assertNotNull($edition->image);
        $this->assertNotSame($seriesImage, $edition->image);
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($seriesImage));
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($edition->image));
    }

    public function test_a_market_release_is_managed_on_the_edition_page(): void
    {
        $expansion = $this->expansion($this->catalog());
        $edition = $this->edition($expansion);
        $market = $this->market();
        $editionUrl = url('/admin/expansion-editions/'.$edition->id.'/edit');
        $expansionUrl = url('/admin/expansions/'.$expansion->id.'/edit');
        $this->get($editionUrl)->assertOk()->assertSee('Sorties')->assertSee($expansionUrl);
        Livewire::test(EditionsRelationManager::class, [
            'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
        ])->assertTableActionHasUrl('edit', $editionUrl, $edition);

        $releases = Livewire::test(ReleasesRelationManager::class, [
            'ownerRecord' => $edition, 'pageClass' => EditExpansionEdition::class,
        ])->callTableAction(CreateAction::class, data: [
            'market_id' => $market->id, 'channel' => 'prerelease', 'released_on' => '2026-11-15', 'status' => 'released',
        ])->assertHasNoActionErrors();

        $this->assertDatabaseHas('expansion_releases', [
            'edition_id' => $edition->id, 'market_id' => $market->id, 'channel' => 'prerelease', 'released_on' => '2026-11-15',
        ]);
        $this->assertSame('released', $edition->refresh()->status);
        $release = $edition->releases()->sole();
        $releases->callTableAction(EditAction::class, $release, ['released_on' => '2026-11-16'])
            ->assertHasNoActionErrors();
        $this->assertSame('2026-11-16', $release->refresh()->released_on->format('Y-m-d'));

        Livewire::test(EditExpansionEdition::class, ['record' => $edition->id])
            ->fillForm(['status' => 'announced'])->call('save')->assertHasFormErrors(['status']);
    }

    #[DataProvider('releasePublicationModes')]
    public function test_publishing_a_release_refreshes_the_open_edition_status(bool $editExisting): void
    {
        $edition = $this->edition($this->expansion($this->catalog()));
        $market = $this->market();
        $page = Livewire::test(EditExpansionEdition::class, ['record' => $edition->id])
            ->assertFormSet(['status' => 'announced'])
            ->fillForm(['name' => 'Nom en cours de saisie', 'official_code' => 'BROUILLON']);
        $releases = Livewire::test(ReleasesRelationManager::class, [
            'ownerRecord' => $edition, 'pageClass' => EditExpansionEdition::class,
        ]);

        if ($editExisting) {
            $releases->callTableAction(CreateAction::class, data: [
                'market_id' => $market->id, 'channel' => 'general', 'status' => 'announced',
            ])->assertHasNoActionErrors()->assertNotDispatched('expansion-edition-status-changed');
            $releases->callTableAction(EditAction::class, $edition->releases()->sole(), ['status' => 'released']);
        } else {
            $releases->callTableAction(CreateAction::class, data: [
                'market_id' => $market->id, 'channel' => 'general', 'status' => 'released',
            ]);
        }

        $releases->assertHasNoActionErrors();
        $this->assertSame('released', $edition->refresh()->status);
        $releases->assertDispatched('expansion-edition-status-changed');

        $page->dispatch('expansion-edition-status-changed')->assertOk()
            ->assertFormSet([
                'status' => 'released', 'name' => 'Nom en cours de saisie', 'official_code' => 'BROUILLON',
            ])
            ->call('save')->assertHasNoFormErrors();

        $this->assertDatabaseHas('expansion_editions', [
            'id' => $edition->id, 'status' => 'released', 'name' => 'Nom en cours de saisie', 'official_code' => 'BROUILLON',
        ]);
    }

    /** @return array<string, array{bool}> */
    public static function releasePublicationModes(): array
    {
        return ['create' => [false], 'edit' => [true]];
    }

    public function test_relation_errors_are_mapped_to_the_modal_form_fields(): void
    {
        $expansion = $this->expansion($this->catalog());
        $other = $this->expansion($this->catalog());
        foreach ([$expansion, $other] as $target) {
            Livewire::test(RelationsRelationManager::class, [
                'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
            ])->callTableAction(CreateAction::class, data: [
                'target_expansion_id' => $target->id, 'type' => 'related_to',
                'notes' => 'Contenu comparable.', 'source_url' => 'https://example.com/source',
            ])->assertHasActionErrors(['target_expansion_id']);
        }

        $this->assertDatabaseCount('expansion_relations', 0);
    }

    public function test_a_reciprocal_relation_is_visible_and_editable_from_either_endpoint(): void
    {
        $catalog = $this->catalog();
        $first = $this->expansion($catalog, key: 'first');
        $second = $this->expansion($catalog, key: 'second');
        Livewire::test(RelationsRelationManager::class, [
            'ownerRecord' => $second, 'pageClass' => EditExpansion::class,
        ])->callTableAction(CreateAction::class, data: [
            'target_expansion_id' => $first->id, 'type' => 'related_to', 'notes' => 'Une source.',
            'source_url' => 'https://example.com/source',
        ])->assertHasNoActionErrors();

        $relation = ExpansionRelation::query()->sole();
        $this->assertSame($first->id, $relation->source_expansion_id);
        foreach ([$first, $second] as $expansion) {
            $notes = 'Source revue depuis '.$expansion->key.'.';
            $manager = Livewire::test(RelationsRelationManager::class, [
                'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
            ])->assertCanSeeTableRecords([$relation])->mountTableAction(EditAction::class, $relation);
            $formHtml = $manager->instance()->getSchema('mountedActionSchema0')->toEmbeddedHtml();
            $this->assertStringContainsString('(first)', $formHtml);
            $this->assertStringContainsString('(second)', $formHtml);

            $manager->setTableActionData(['notes' => $notes])
                ->callMountedTableAction()
                ->assertHasNoActionErrors();
            $this->assertSame($notes, $relation->refresh()->notes);
        }
    }

    public function test_expansion_list_filters_narrow_the_results(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $noSeries = $this->expansion($catalog, key: 'no-series');
        $other = $this->expansion($this->catalog());

        Livewire::test(ListExpansions::class)
            ->assertCanSeeTableRecords([$expansion, $noSeries, $other])
            ->filterTable('game', $catalog->game_id)
            ->assertCanSeeTableRecords([$expansion, $noSeries])
            ->assertCanNotSeeTableRecords([$other])
            ->filterTable('catalog', $catalog->id)
            ->filterTable('series', $series->id)
            ->assertCanSeeTableRecords([$expansion])->assertCanNotSeeTableRecords([$noSeries, $other])
            ->filterTable('type', 'promo')->assertCanNotSeeTableRecords([$expansion]);
    }

    public function test_malformed_selector_values_are_reported_on_their_forms(): void
    {
        $expansion = $this->expansion($this->catalog());

        Livewire::test(CreateExpansion::class)
            ->fillForm(['catalog_id' => 'invalid', 'series_id' => 1, 'type' => 'main', 'name' => 'Test'])
            ->call('create')->assertHasFormErrors(['catalog_id']);

        Livewire::test(RelationsRelationManager::class, [
            'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
        ])->callTableAction(CreateAction::class, data: [
            'target_expansion_id' => 'invalid', 'type' => 'related_to',
            'notes' => 'Une source.', 'source_url' => 'https://example.com/source',
        ])->assertHasActionErrors(['target_expansion_id']);
    }

    public function test_linking_from_a_game_opens_its_filtered_catalogue_list(): void
    {
        $catalogue = $this->catalog();
        $other = $this->catalog();
        $gamePage = Livewire::test(EditGame::class, ['record' => $catalogue->game_id]);
        $action = $gamePage->instance()->getAction('catalogues');
        $url = $action?->getUrl();
        $this->assertNotNull($url);
        $this->get($url)->assertOk();

        parse_str((string) parse_url($url, PHP_URL_QUERY), $parameters);
        Livewire::withQueryParams($parameters)->test(ListCatalogs::class)
            ->assertCanSeeTableRecords([$catalogue])->assertCanNotSeeTableRecords([$other]);
    }

    public function test_protected_game_deletion_displays_the_catalogue_dependency(): void
    {
        $catalog = $this->catalog();
        Livewire::test(EditGame::class, ['record' => $catalog->game_id])
            ->callAction(DeleteAction::class)
            ->assertNotified('Ce jeu contient des catalogues. Supprimez-les explicitement avant le jeu.');
        $this->assertModelExists($catalog->game);

        $free = Game::factory()->create();
        Livewire::test(ListGames::class)->callTableBulkAction(DeleteBulkAction::class, [$catalog->game, $free])
            ->assertNotified();
        $this->assertModelExists($catalog->game);
        $this->assertModelMissing($free);
    }

    public function test_protected_catalogue_deletion_displays_the_series_dependency(): void
    {
        $catalog = $this->catalog();
        $this->series($catalog);

        Livewire::test(EditCatalog::class, ['record' => $catalog->id])->callAction(DeleteAction::class)
            ->assertNotified('Ce catalogue contient des séries ou des extensions.');
        $this->assertModelExists($catalog);
    }

    public function test_catalogue_pages_and_previously_opened_mutations_require_a_verified_administrator(): void
    {
        $catalog = $this->catalog();
        $expansion = $this->expansion($catalog);
        $language = $this->language('fr', 'Français');

        foreach ([
            User::factory()->create(),
            User::factory()->unverified()->create(['is_admin' => true]),
        ] as $deniedUser) {
            $admin = User::factory()->create(['is_admin' => true]);
            $this->actingAs($admin);
            $list = Livewire::test(ListExpansions::class)
                ->mountTableBulkAction(DeleteBulkAction::class, [$expansion])
                ->assertTableBulkActionMounted(DeleteBulkAction::class);
            $edit = Livewire::test(EditCatalog::class, ['record' => $catalog->id])->fillForm(['name' => 'Intrus']);
            $manager = Livewire::test(ExpansionLocalizationsRelationManager::class, [
                'ownerRecord' => $expansion, 'pageClass' => EditExpansion::class,
            ])->mountTableAction(CreateAction::class)->assertTableActionMounted(CreateAction::class)
                ->setTableActionData(['language_id' => $language->id, 'name' => 'Intrus']);

            $this->actingAs($deniedUser);
            $this->get('/admin/catalogs')->assertForbidden();
            Livewire::test(CreateCatalog::class)->assertForbidden();
            $edit->call('save')->assertForbidden();
            $list->call('callMountedAction')->assertForbidden();
            $manager->call('callMountedAction')->assertForbidden();

            $this->assertSame('International', $catalog->refresh()->name);
            $this->assertModelExists($expansion);
            $this->assertDatabaseCount('expansion_localizations', 0);
        }
    }
}
