<?php

namespace Tests\Feature\Localization;

use App\Actions\Catalog\SaveTranslations;
use App\Filament\Resources\ExpansionEditions\Pages\EditExpansionEdition;
use App\Filament\Resources\ExpansionEditions\RelationManagers\ReleasesRelationManager;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Expansions\Pages\EditExpansion;
use App\Filament\Resources\Expansions\Pages\ListExpansions;
use App\Filament\Resources\Games\Pages\EditGame;
use App\Models\Game;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\ForwardOnlyDatabaseMigrations;
use Tests\TestCase;

class TranslatedAdminFormsTest extends TestCase
{
    use BuildsCatalog, ForwardOnlyDatabaseMigrations;

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
