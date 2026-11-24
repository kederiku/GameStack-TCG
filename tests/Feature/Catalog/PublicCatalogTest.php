<?php

namespace Tests\Feature\Catalog;

use App\Models\ExpansionRelation;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class PublicCatalogTest extends TestCase
{
    use BuildsCatalog;
    use RefreshDatabase;

    public function test_only_effectively_visible_records_reach_the_list_counts_and_search(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $catalog = $this->catalog($game);
        $series = $this->series($catalog);
        $public = $this->expansion($catalog, $series);
        $this->edition($public);
        $hidden = $this->expansion($catalog, key: 'hidden');
        $hidden->update(['is_visible' => false]);
        $hiddenSeries = $this->series($catalog, key: 'hidden-series');
        $this->expansion($catalog, $hiddenSeries, key: 'hidden-parent');
        $hiddenSeries->update(['is_visible' => false]);
        $hiddenCatalog = $this->catalog($game, key: 'hidden-catalog');
        $this->expansion($hiddenCatalog);
        $hiddenCatalog->update(['is_visible' => false]);
        $privateEdition = $this->edition($public, $this->language('fr', 'Français'));
        $privateEdition->update(['name' => 'Secret edition', 'is_visible' => false]);

        $this->get('/fr/pokemon')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.total', 1)
            ->has('catalogData.catalogs', 1)
            ->where('catalogData.catalogs.0.count', 1)
            ->has('catalogData.languages', 1)
            ->has('catalogData.expansions.data', 1)
            ->where('catalogData.expansions.data.0.id', $public->id)
            ->has('catalogData.expansions.data.0.languages', 1));
        $this->get('/fr/pokemon?q=Secret')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.total', 0));
    }

    public function test_direct_urls_reject_every_hidden_ancestor_and_wrong_game(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $catalog = $this->catalog($game);
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $path = '/fr/pokemon/international/base-set';
        $this->get($path)->assertOk();
        foreach ([$expansion, $series, $catalog, $game] as $record) {
            $record->update(['is_visible' => false]);
            $this->get($path)->assertNotFound();
            $record->update(['is_visible' => true]);
        }
        Game::factory()->create(['slug' => 'other']);
        $this->get('/fr/other/international/base-set')->assertNotFound();
        $this->get('/fr/pokemon/missing/base-set')->assertNotFound();
    }

    public function test_identical_expansion_keys_in_different_catalogs_are_distinct(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $a = $this->expansion($this->catalog($game));
        $b = $this->expansion($this->catalog($game, key: 'japan'));
        $this->get('/fr/pokemon/international/base-set')->assertInertia(fn (Assert $page) => $page
            ->component('games/expansion', false)->where('expansion.id', $a->id));
        $this->get('/fr/pokemon/japan/base-set')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.id', $b->id));
    }

    public function test_interface_language_does_not_replace_the_selected_edition_or_its_missing_image(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $expansion = $this->expansion($catalog);
        $fr = $this->language('fr', 'Français');
        $expansion->update(['display_name' => ['fr' => 'Ensemble de base'], 'description' => ['fr' => 'Description française']]);
        $english = $this->edition($expansion);
        $french = $this->edition($expansion, $fr);
        DB::table('expansion_editions')->where('id', $english->id)->update(['image' => 'catalog/english.avif']);
        foreach (['fr', 'en'] as $locale) {
            $this->get('/'.$locale.'/pokemon/international/base-set?edition=fr')->assertInertia(fn (Assert $page) => $page
                ->where('expansion.selectedEdition.id', $french->id)
                ->where('expansion.selectedEdition.imageUrl', null)
                ->where('expansion.editionFallback', false)
                ->has('expansion.editions', 2));
        }
        $this->get('/fr/pokemon/international/base-set?edition=fr')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.name', 'Ensemble de base')
            ->where('expansion.nameLanguage', 'fr')
            ->where('expansion.description', 'Description française'));
    }

    public function test_missing_translations_and_unavailable_editions_have_explicit_fallbacks(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $expansion = $this->expansion($catalog);
        $expansion->setTranslation('display_name', 'en', 'English title')->save();
        $this->edition($expansion);
        $hidden = $this->edition($expansion, $this->language('fr', 'Français'));
        $hidden->update(['is_visible' => false]);
        $this->get('/fr/pokemon/international/base-set?edition=fr')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.name', 'English title')
            ->where('expansion.nameLanguage', 'en')
            ->where('expansion.nameFallback', true)
            ->where('expansion.selectedEdition.language.code', 'en')
            ->where('expansion.editionFallback', true)
            ->has('expansion.editions', 1));
    }

    public function test_expansions_without_a_series_or_visible_editions_remain_informative(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $expansion = $this->expansion($catalog);
        $this->get('/fr/pokemon/international/base-set')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.series', null)
            ->where('expansion.selectedEdition', null)
            ->has('expansion.editions', 0));
        $this->get('/fr/pokemon?language=fr')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.total', 0));
        $this->get('/fr/pokemon')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.data.0.id', $expansion->id));
    }

    public function test_releases_keep_their_markets_channels_dates_and_statuses(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $edition = $this->edition($this->expansion($catalog));
        $release = $this->release($edition);
        $release->update(['released_on' => '2023-03-31', 'status' => 'released']);
        $cancelled = $this->release($edition, $this->market('us', 'États-Unis'), 'event');
        $cancelled->update(['reference_announced_period' => '2027', 'status' => 'cancelled']);
        $this->get('/fr/pokemon/international/base-set')->assertInertia(fn (Assert $page) => $page
            ->has('expansion.selectedEdition.releases', 2)
            ->where('expansion.selectedEdition.releases.0.releasedOn', '2023-03-31')
            ->where('expansion.selectedEdition.releases.0.market.name', 'France')
            ->where('expansion.selectedEdition.releases.1.releasedOn', null)
            ->where('expansion.selectedEdition.releases.1.announcedPeriod', '2027')
            ->where('expansion.selectedEdition.releases.1.channel', 'event')
            ->where('expansion.selectedEdition.releases.1.status', 'cancelled'));
    }

    public function test_filters_search_official_names_codes_and_localizations_before_pagination(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $edition = $this->edition($expansion, $this->language('fr', 'Français'));
        $edition->update(['name' => 'Écarlate et Violet', 'official_code' => 'SV01']);
        $this->edition($this->expansion($catalog, key: 'other'));
        foreach (['ecarlate', 'SV01'] as $query) {
            $this->get('/fr/pokemon?catalog=international&language=fr&type=main&series=scarlet-and-violet&q='.$query)->assertInertia(fn (Assert $page) => $page
                ->where('catalogData.expansions.total', 1)
                ->where('catalogData.expansions.data.0.id', $expansion->id)
                ->where('catalogData.expansions.data.0.edition.language.code', 'fr'));
        }
        $this->get('/fr/pokemon?q=%25')->assertInertia(fn (Assert $page) => $page->where('catalogData.expansions.total', 0));
        $this->get('/fr/pokemon?type=special')->assertInertia(fn (Assert $page) => $page->where('catalogData.expansions.total', 0));
    }

    public function test_pagination_is_bounded_and_deterministic_with_missing_dates(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        for ($index = 1; $index <= 38; $index++) {
            $this->expansion($catalog, key: 'set-'.$index);
        }
        $this->get('/fr/pokemon?sort=recent')->assertInertia(fn (Assert $page) => $page
            ->has('catalogData.expansions.data', 36)
            ->where('catalogData.expansions.total', 38)
            ->where('catalogData.expansions.last_page', 2));
        $this->get('/fr/pokemon?sort=recent&page=2')->assertInertia(fn (Assert $page) => $page
            ->has('catalogData.expansions.data', 2)
            ->where('catalogData.expansions.data.0.key', 'set-37'));
    }

    public function test_related_extensions_never_leak_hidden_targets(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $expansion = $this->expansion($catalog);
        $target = $this->expansion($this->catalog($catalog->game, key: 'japan'));
        ExpansionRelation::create(['source_expansion_id' => $expansion->id, 'target_expansion_id' => $target->id, 'type' => 'adapted_from', 'notes' => 'Related content', 'source_url' => 'https://example.com/source']);
        $this->get('/fr/pokemon/international/base-set')->assertInertia(fn (Assert $page) => $page->has('expansion.related', 1));
        $target->catalog->update(['is_visible' => false]);
        $this->get('/fr/pokemon/international/base-set')->assertInertia(fn (Assert $page) => $page->has('expansion.related', 0));
    }

    public function test_out_of_range_pages_are_clamped_to_a_real_page(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $this->expansion($catalog);
        $this->get('/fr/pokemon?page=99999999999999999999999')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.current_page', 1)
            ->has('catalogData.expansions.data', 1));
    }

    public function test_sorting_uses_localized_names_and_non_cancelled_release_dates(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        $old = $this->expansion($catalog, key: 'old');
        $old->setTranslation('display_name', 'fr', 'Zulu')->save();
        $oldEdition = $this->edition($old);
        $this->release($oldEdition)->update(['released_on' => '2020-01-01', 'status' => 'released']);
        $this->release($oldEdition, $this->market('japan', 'Japon'))->update(['released_on' => '2030-01-01', 'status' => 'cancelled']);
        $new = $this->expansion($catalog, key: 'new');
        $new->setTranslation('display_name', 'fr', 'Alpha')->save();
        $this->release($this->edition($new))->update(['released_on' => '2023-01-01', 'status' => 'released']);
        $unknown = $this->expansion($catalog, key: 'unknown');
        $unknown->update(['name' => 'Middle']);

        $this->get('/fr/pokemon?sort=recent')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.data.0.id', $new->id)
            ->where('catalogData.expansions.data.1.id', $old->id)
            ->where('catalogData.expansions.data.2.id', $unknown->id));
        $this->get('/fr/pokemon?sort=oldest')->assertInertia(fn (Assert $page) => $page->where('catalogData.expansions.data.0.id', $old->id));
        $this->get('/fr/pokemon?sort=name')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.data.0.id', $new->id)
            ->where('catalogData.expansions.data.1.id', $unknown->id)
            ->where('catalogData.expansions.data.2.id', $old->id));
    }

    public function test_empty_games_optional_metadata_and_malformed_filters_are_supported(): void
    {
        Game::factory()->create(['slug' => 'ended-game', 'publisher' => 'Publisher', 'start_year' => 1996, 'end_year' => 2020]);
        $this->get('/fr/ended-game?catalog[]=x&q[]=x&language[]=x&sort[]=x&type[]=x&page[]=2')->assertOk()->assertInertia(fn (Assert $page) => $page
            ->where('gameDetails.publisher', 'Publisher')
            ->where('gameDetails.startYear', 1996)
            ->where('gameDetails.endYear', 2020)
            ->where('catalogData.total', 0)
            ->has('catalogData.catalogs', 0)
            ->where('catalogData.filters.q', '')
            ->where('catalogData.filters.sort', 'recent'));
    }

    public function test_language_filters_accept_the_same_complex_tags_as_the_admin(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']));
        foreach (['zh-Hant-TW', 'es-419', 'en-US-x-tcg'] as $index => $code) {
            $this->edition($this->expansion($catalog, key: 'language-'.$index), $this->language($code, $code));
        }
        foreach (['zh-Hant-TW', 'es-419', 'en-US-x-tcg'] as $code) {
            $this->get('/fr/pokemon?language='.$code)->assertInertia(fn (Assert $page) => $page
                ->where('catalogData.filters.language', $code)
                ->where('catalogData.expansions.total', 1)
                ->where('catalogData.expansions.data.0.edition.language.code', $code));
        }
    }

    public function test_all_is_a_valid_catalog_key_and_language_code_not_a_filter_sentinel(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $this->expansion($this->catalog($game));
        $catalog = $this->catalog($game, key: 'all');
        $edition = $this->edition($this->expansion($catalog), $this->language('all', 'Allar'));
        $this->get('/fr/pokemon?catalog=all&language=all')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.total', 1)
            ->where('catalogData.expansions.data.0.edition.id', $edition->id));
    }

    public function test_blank_language_filters_use_the_all_languages_option(): void
    {
        Game::factory()->create(['slug' => 'pokemon']);
        foreach (['', '%20%20'] as $value) {
            $this->get('/fr/pokemon?language='.$value)->assertInertia(fn (Assert $page) => $page
                ->where('catalogData.filters.language', '*'));
        }
    }
}
