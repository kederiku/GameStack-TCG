<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Catalogs\Pages\EditCatalog;
use App\Filament\Resources\Expansions\Pages\CreateExpansion;
use App\Filament\Resources\Expansions\Pages\ListExpansions;
use App\Filament\Resources\Series\Pages\CreateSeries;
use App\Filament\Resources\Series\Pages\EditSeries;
use App\Filament\Resources\Series\Pages\ListSeries;
use App\Filament\Widgets\CatalogOverview;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Livewire\Livewire;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class AdminUsabilityTest extends TestCase
{
    use BuildsCatalog, DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_the_overview_uses_real_counts_including_an_empty_catalogue(): void
    {
        Livewire::test(CatalogOverview::class)
            ->assertViewHas('totals', ['games' => 0, 'catalogs' => 0, 'expansions' => 0, 'editions' => 0])
            ->assertSee(url('/admin/games/create'), false);

        $catalog = $this->catalog();
        $this->edition($this->expansion($catalog));
        $this->expansion($catalog, key: 'second');

        Livewire::test(CatalogOverview::class)
            ->assertViewHas('totals', ['games' => 1, 'catalogs' => 1, 'expansions' => 2, 'editions' => 1])
            ->assertSee(url('/admin/expansions'), false);
        $this->get('/admin')->assertOk()->assertSee(url('/admin/games/create'), false);
    }

    public function test_the_overview_rejects_direct_access_and_revoked_permissions(): void
    {
        $widget = Livewire::test(CatalogOverview::class)->assertOk();
        $this->actingAs(User::factory()->create());
        $widget->call('$refresh')->assertForbidden();
        Livewire::test(CatalogOverview::class)->assertForbidden();
        $this->actingAs(User::factory()->unverified()->create(['is_admin' => true]));
        Livewire::test(CatalogOverview::class)->assertForbidden();
        auth()->logout();
        Livewire::test(CatalogOverview::class)->assertForbidden();
    }

    public function test_french_admin_labels_do_not_change_the_public_locale(): void
    {
        app()->setLocale('en');
        $this->get('/admin/games')->assertOk()->assertSee('lang="fr"', false)->assertSee('Rechercher');
        $this->assertSame('en', app()->getLocale());
        $this->get('/')->assertOk()->assertSee('lang="en"', false);
    }

    public function test_catalogue_shortcuts_show_only_its_series_and_expansions(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $other = $this->catalog();
        $otherSeries = $this->series($other);
        $otherExpansion = $this->expansion($other, $otherSeries);
        $page = Livewire::test(EditCatalog::class, ['record' => $catalog->id]);

        $url = $page->instance()->getAction('series')?->getUrl();
        $this->assertNotNull($url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(ListSeries::class)
            ->assertCanSeeTableRecords([$series])->assertCanNotSeeTableRecords([$otherSeries]);

        $url = $page->instance()->getAction('expansions')?->getUrl();
        $this->assertNotNull($url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(ListExpansions::class)
            ->assertCanSeeTableRecords([$expansion])->assertCanNotSeeTableRecords([$otherExpansion]);
    }

    public function test_series_shortcut_and_create_action_keep_the_current_context(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $unrelated = $this->expansion($catalog, key: 'unrelated');
        $page = Livewire::test(EditSeries::class, ['record' => $series->id]);
        $url = $page->instance()->getAction('expansions')?->getUrl();
        $this->assertNotNull($url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        $list = Livewire::withQueryParams($query)->test(ListExpansions::class)
            ->assertCanSeeTableRecords([$expansion])->assertCanNotSeeTableRecords([$unrelated]);

        $url = $list->instance()->getAction('create')?->getUrl();
        $this->assertNotNull($url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(CreateExpansion::class)
            ->assertFormSet(['catalog_id' => $catalog->id, 'series_id' => $series->id])
            ->fillForm(['name' => 'Contextual expansion', 'type' => 'main'])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('expansions', ['name' => 'Contextual expansion', 'catalog_id' => $catalog->id, 'series_id' => $series->id]);
    }

    public function test_a_filtered_series_list_prefills_its_catalogue_when_creating(): void
    {
        $catalog = $this->catalog();
        $list = Livewire::test(ListSeries::class)->filterTable('catalog', $catalog->id);
        $url = $list->instance()->getAction('create')?->getUrl();
        $this->assertNotNull($url);
        parse_str((string) parse_url($url, PHP_URL_QUERY), $query);
        Livewire::withQueryParams($query)->test(CreateSeries::class)
            ->assertFormSet(['catalog_id' => $catalog->id])
            ->fillForm(['name' => 'Contextual series', 'type' => 'series'])->call('create')->assertHasNoFormErrors();
        $this->assertDatabaseHas('series', ['name' => 'Contextual series', 'catalog_id' => $catalog->id]);
    }

    public function test_creation_ignores_malformed_or_incompatible_context(): void
    {
        $catalog = $this->catalog();
        $foreignSeries = $this->series($this->catalog());
        Livewire::withQueryParams(['catalog' => $catalog->id, 'series' => $foreignSeries->id])->test(CreateExpansion::class)
            ->assertFormSet(['catalog_id' => $catalog->id, 'series_id' => null]);
        Livewire::withQueryParams(['catalog' => 'invalid', 'series' => 'invalid'])->test(CreateExpansion::class)
            ->assertFormSet(['catalog_id' => null, 'series_id' => null]);
        Livewire::withQueryParams(['catalog' => 999999])->test(CreateSeries::class)
            ->assertFormSet(['catalog_id' => null]);
    }
}
