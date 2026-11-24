<?php

namespace Tests\Feature\Localization;

use App\Actions\Catalog\SaveTranslations;
use App\Filament\Resources\ExpansionEditions\Pages\EditExpansionEdition;
use App\Filament\Resources\ExpansionEditions\RelationManagers\ReleasesRelationManager;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Expansions\Pages\CreateExpansion;
use App\Filament\Resources\Expansions\Pages\EditExpansion;
use App\Filament\Resources\Expansions\Pages\ListExpansions;
use App\Filament\Resources\Expansions\RelationManagers\RelationsRelationManager;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Models\Game;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class TranslatedAdminFormsTest extends TestCase
{
    use BuildsCatalog, DatabaseMigrations;

    public function test_catalog_controls_display_canonical_casing_for_historical_language_codes(): void
    {
        $language = $this->language('pt-BR', 'Português');
        DB::table('languages')->where('id', $language->id)->update(['code' => 'pt-br']);
        $this->assertStringEndsWith(' (pt-BR)', CatalogLabels::language($language->refresh()));
        $this->assertStringEndsWith(' (pt-BR)', CatalogFields::catalogueLabel($this->catalog(language: $language)));
    }

    public function test_search_name_treats_postgres_wildcards_as_literals(): void
    {
        $literal = Game::factory()->create(['name' => 'Reference 100%']);
        $translated = Game::factory()->create(['display_name' => ['en' => 'Marker_A']]);
        foreach (['%' => $literal->id, '_' => $translated->id] as $term => $expected) {
            $query = Game::query();
            CatalogLabels::searchName($query, $term);
            $this->assertSame([$expected], $query->pluck('id')->all());
        }
    }

    public function test_english_panel_edits_only_the_selected_content_language(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->language('ja', 'Japonais');
        $this->language('fr', 'Français');
        $expansion = $this->expansion($this->catalog());
        $save = app(SaveTranslations::class);
        $save->handle($expansion, 'en', ['display_name' => 'English title', 'description' => 'Fallback']);
        $save->handle($expansion, 'ja', ['display_name' => '日本語']);

        $page = Livewire::test(EditExpansion::class, ['record' => $expansion->id])
            ->assertSee('English title')
            ->set('data.translation.locale', 'ja')
            ->assertFormSet(['translation.display_name' => '日本語', 'translation.description' => null])
            ->fillForm(['translation.display_name' => '日本語版'])->call('save')->assertHasNoFormErrors()
            ->set('data.translation.locale', 'fr')
            ->assertFormSet(['translation.display_name' => null, 'translation.description' => null])
            ->fillForm(['name' => 'Reference'])->call('save')->assertHasNoFormErrors();
        $this->assertArrayNotHasKey('fr', $expansion->refresh()->getTranslations('display_name'));
        $this->assertSame('Reference', $expansion->name);
        $page->fillForm(['translation.suppress_description' => true])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['en' => 'Fallback', 'fr' => null], $expansion->refresh()->getTranslations('description'));
        $this->assertArrayNotHasKey('fr', $expansion->getTranslations('display_name'));
        $page->fillForm(['translation.remove' => true])->call('save')->assertHasNoFormErrors();
        $this->assertArrayNotHasKey('fr', $expansion->refresh()->getTranslations('description'));
        $page->fillForm(['translation.display_name' => 'Nom français', 'translation.description' => 'Texte'])
            ->call('save')->assertHasNoFormErrors()
            ->fillForm(['translation.description' => ''])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['en' => 'Fallback', 'fr' => null], $expansion->refresh()->getTranslations('description'));
        Livewire::test(ListExpansions::class)->searchTable('Nom français')->assertCanSeeTableRecords([$expansion]);
        $page->fillForm(['translation.remove' => true])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['en' => 'English title', 'ja' => '日本語版'], $expansion->refresh()->getTranslations('display_name'));
        $this->assertSame(['en' => 'Fallback'], $expansion->getTranslations('description'));
    }

    public function test_switching_content_language_requires_confirmation_to_discard_changes(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $this->language('ja', 'Japonais');
        $expansion = $this->expansion($this->catalog());
        app(SaveTranslations::class)->handle($expansion, 'ja', ['display_name' => '日本語']);
        $page = Livewire::test(EditExpansion::class, ['record' => $expansion->id])
            ->set('data.translation.locale', 'ja')->set('data.translation.display_name', 'Draft')
            ->set('data.translation.suppress_description', true)->set('data.translation.remove', true)
            ->set('data.translation.locale', 'fr')
            ->assertSet('data.translation.locale', 'ja')->assertSet('data.translation.display_name', 'Draft')
            ->assertSet('data.translation.suppress_description', true)->assertSet('data.translation.remove', true)
            ->set('data.translation.switch_decision', 'keep')->assertSet('data.translation.locale', 'ja')
            ->set('data.translation.locale', 'fr')->set('data.translation.switch_decision', 'discard')
            ->assertSet('data.translation.locale', 'fr')->assertSet('data.translation.display_name', null);
        $this->assertSame(['ja' => '日本語'], $expansion->refresh()->getTranslations('display_name'));
        $page->set('data.translation.locale', 'ja')->assertSet('data.translation.display_name', '日本語')
            ->assertSet('data.translation.remove', false)->assertSet('data.translation.suppress_description', false);
    }

    public function test_reverted_absent_fields_remain_missing_after_livewire_updates(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $expansion = $this->expansion($this->catalog());
        Livewire::test(EditExpansion::class, ['record' => $expansion->id])
            ->set('data.translation.locale', 'en')
            ->set('data.translation.description', 'Temporary')->set('data.translation.description', '')
            ->set('data.translation.display_name', 'Temporary')->set('data.translation.display_name', '   ')
            ->call('save')->assertHasNoFormErrors();
        $this->assertSame([], $expansion->refresh()->getTranslations('display_name'));
        $this->assertSame([], $expansion->getTranslations('description'));
    }

    public function test_release_and_game_dates_use_the_panel_locale(): void
    {
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $edition = $this->edition($this->expansion($this->catalog()));
        $this->saveCatalog($this->release($edition), ['released_on' => '2026-11-24']);
        $game = Game::factory()->create(['updated_at' => '2026-11-24 14:03:00']);
        foreach (['en' => '11/24/2026', 'fr' => '24/11/2026'] as $locale => $date) {
            Filament::setCurrentPanel(Filament::getPanel('admin-'.$locale));
            app()->setLocale($locale);
            Livewire::test(ReleasesRelationManager::class, ['ownerRecord' => $edition, 'pageClass' => EditExpansionEdition::class])
                ->assertSee($date);
            Livewire::test(ListGames::class)->assertTableColumnFormattedStateSet('updated_at', $date.($locale === 'en' ? ' 2:03 PM' : ' 14:03'), $game);
        }
    }

    public function test_release_modal_keeps_pending_work_and_reverted_absent_periods(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $edition = $this->edition($this->expansion($this->catalog()));
        $release = $this->release($edition);
        $path = 'mountedActions.0.data.translation.';
        Livewire::test(ReleasesRelationManager::class, ['ownerRecord' => $edition, 'pageClass' => EditExpansionEdition::class])
            ->mountTableAction(EditAction::class, $release)
            ->set($path.'locale', 'en')->set($path.'announced_period', 'Draft')
            ->set($path.'locale', 'fr')->assertSet($path.'locale', 'en')->assertSet($path.'announced_period', 'Draft')
            ->set($path.'switch_decision', 'keep')->set($path.'announced_period', '')
            ->callMountedTableAction()->assertHasNoActionErrors();
        $this->assertSame([], $release->refresh()->getTranslations('announced_period'));
    }

    public function test_parent_options_are_ordered_before_the_fifty_record_limit(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $game = Game::factory()->create();
        $catalogIds = $expansionIds = [];
        foreach (range(51, 1) as $i) {
            $name = sprintf('Shown %02d', $i);
            $reference = sprintf('Reference %02d', 52 - $i);
            $catalog = $this->saveCatalog($this->catalog($game, key: 'catalog-'.$i), ['name' => $reference, 'display_name' => ['en' => $name]]);
            $expansion = $this->saveCatalog($this->expansion($catalog), ['name' => $reference, 'display_name' => ['en' => $name]]);
            $catalogIds[$i] = $catalog->id;
            $expansionIds[$i] = $expansion->id;
        }
        ksort($catalogIds);
        ksort($expansionIds);
        $owner = $this->expansion($catalog, key: 'owner');
        $manager = Livewire::test(RelationsRelationManager::class, ['ownerRecord' => $owner, 'pageClass' => EditExpansion::class])->mountTableAction(CreateAction::class);
        $target = $manager->instance()->getSchema('mountedActionSchema0')->getComponentByStatePath('target_expansion_id');
        $this->assertSame(array_slice($expansionIds, 0, 50), array_keys($target->getSearchResults('Shown')));
        $filter = Livewire::test(ListExpansions::class)->instance()->getTableFiltersForm()->getComponentByStatePath('catalog.value');
        $this->assertSame(array_slice($catalogIds, 0, 50), array_keys($filter->getSearchResults('Shown')));
        $this->assertSame(array_slice($catalogIds, 0, 50), array_keys($filter->getOptions()));
        $select = Livewire::test(CreateExpansion::class)->instance()->getSchema('form')->getComponentByStatePath('catalog_id');
        $this->assertSame(array_values($catalogIds), array_keys($select->getOptions()));
    }

    public function test_list_sort_and_global_search_use_translated_names(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $catalog = $this->catalog();
        $first = $this->saveCatalog($this->expansion($catalog, key: 'first'), ['name' => 'A reference', 'display_name' => ['en' => 'Zulu']]);
        $second = $this->saveCatalog($this->expansion($catalog, key: 'second'), ['name' => 'Z reference', 'display_name' => ['en' => 'Alpha']]);
        Livewire::test(ListExpansions::class)->sortTable('name')->assertCanSeeTableRecords([$second, $first], inOrder: true);
        $result = ExpansionResource::getGlobalSearchResults('Zulu')->sole();
        $this->assertSame('Zulu', $result->title);
        $this->assertStringContainsString('/en/admin/', $result->url);
    }

    public function test_saving_only_a_reference_preserves_raw_historical_and_concurrently_changed_text(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $expansion = $this->expansion($this->catalog());
        $expansion->getConnection()->table('expansions')->where('id', $expansion->id)->update([
            'display_name' => '{"fr":"  Ancien nom  "}', 'description' => '{"fr":""}',
        ]);
        $page = Livewire::test(EditExpansion::class, ['record' => $expansion->id])
            ->set('data.translation.locale', 'fr')
            ->assertSet('data.translation.display_name', '  Ancien nom  ')
            ->fillForm(['name' => 'Reference'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['fr' => '  Ancien nom  '], $expansion->refresh()->getTranslations('display_name'));
        $this->assertSame(['fr' => ''], $expansion->getTranslations('description'));
        app(SaveTranslations::class)->handle($expansion, 'fr', ['display_name' => 'Concurrent']);
        $page->fillForm(['translation.description' => 'Texte'])->call('save')->assertHasNoFormErrors();
        $this->assertSame(['fr' => 'Concurrent'], $expansion->refresh()->getTranslations('display_name'));
    }

    public function test_invalid_translation_rolls_back_reference_and_uploaded_image(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        Storage::fake('public');
        config(['game-images.disk' => 'public']);
        $game = Game::factory()->create(['name' => 'Reference']);
        app(SaveTranslations::class)->handle($game, 'fr', ['display_name' => 'Nom']);
        Livewire::test(EditGame::class, ['record' => $game->id])
            ->set('data.translation.locale', 'fr')
            ->fillForm(['name' => 'New reference', 'translation.display_name' => '', 'image' => UploadedFile::fake()->image('logo.png')])
            ->call('save')->assertHasFormErrors(['translation.display_name']);
        $this->assertSame('Reference', $game->refresh()->name);
        $this->assertNull($game->image);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_release_modal_can_clear_reference_and_edit_a_period_without_copying_a_fallback(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin-en'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
        $edition = $this->edition($this->expansion($this->catalog()));
        $release = $this->saveCatalog($this->release($edition), ['reference_announced_period' => 'Q4']);
        app(SaveTranslations::class)->handle($release, 'fr', ['announced_period' => 'Automne']);
        $page = Livewire::test(ReleasesRelationManager::class, ['ownerRecord' => $edition, 'pageClass' => EditExpansionEdition::class])
            ->mountTableAction(EditAction::class, $release)
            ->set('mountedActions.0.data.translation.locale', 'en')
            ->assertSet('mountedActions.0.data.translation.announced_period', null)
            ->setTableActionData(['reference_announced_period' => ''])
            ->callMountedTableAction()->assertHasNoActionErrors();
        $this->assertNull($release->refresh()->getAttribute('reference_announced_period'));
        $this->assertSame(['fr' => 'Automne'], $release->getTranslations('announced_period'));
        $page->mountTableAction(EditAction::class, $release)
            ->set('mountedActions.0.data.translation.locale', 'fr')
            ->assertSet('mountedActions.0.data.translation.announced_period', 'Automne')
            ->setTableActionData(['translation.announced_period' => ''])
            ->callMountedTableAction()->assertHasNoActionErrors();
        $this->assertSame(['fr' => null], $release->refresh()->getTranslations('announced_period'));
        $page->mountTableAction(EditAction::class, $release)
            ->set('mountedActions.0.data.translation.locale', 'en')
            ->setTableActionData(['translation.suppress_announced_period' => true])
            ->callMountedTableAction()->assertHasNoActionErrors();
        $this->assertSame(['en' => null, 'fr' => null], $release->refresh()->getTranslations('announced_period'));
        $page->mountTableAction(EditAction::class, $release)
            ->set('mountedActions.0.data.translation.locale', 'en')
            ->setTableActionData(['translation.remove' => true])
            ->callMountedTableAction()->assertHasNoActionErrors();
        $this->assertSame(['fr' => null], $release->refresh()->getTranslations('announced_period'));
    }
}
