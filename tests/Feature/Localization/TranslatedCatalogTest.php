<?php

namespace Tests\Feature\Localization;

use App\Models\Game;
use App\Support\Catalog\PublicCatalog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class TranslatedCatalogTest extends TestCase
{
    use BuildsCatalog;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutExceptionHandling();
    }

    public function test_missing_translation_does_not_change_edition(): void
    {
        $ja = $this->language('ja', '日本語');
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']), $ja);
        $series = $this->series($catalog);
        $series->update(['display_name' => ['ja' => 'シリーズ'], 'description' => ['fr' => 'Une série']]);
        $expansion = $this->expansion($catalog, $series);
        $expansion->update(['display_name' => ['ja' => '初版'], 'description' => ['ja' => '説明', 'fr' => null]]);
        $fr = $this->edition($expansion, $this->language('fr'));
        DB::table('expansion_editions')->where('id', $fr->id)->update(['image' => 'catalog/fr.avif']);
        $edition = $this->edition($expansion, $ja);
        $path = '/fr/pokemon/international/base-set';
        $this->get($path.'?edition=ja')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.name', '初版')->where('expansion.nameLanguage', 'ja')->where('expansion.nameFallback', true)
            ->where('expansion.description', null)->where('expansion.descriptionLanguage', 'fr')->where('expansion.descriptionFallback', false)
            ->where('expansion.series.nameLanguage', 'ja')->where('expansion.series.descriptionLanguage', 'fr')
            ->where('expansion.selectedEdition.language.code', 'ja')->where('expansion.selectedEdition.imageUrl', null)->where('expansion.editionFallback', false));
        $this->get($path.'?edition=fr')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.selectedEdition.id', $fr->id)->where('expansion.selectedEdition.imageUrl', url('/storage/catalog/fr.avif')));
        $expansion->forgetTranslation('description', 'fr')->save();
        $this->get($path.'?edition=absent')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.description', '説明')->where('expansion.descriptionLanguage', 'ja')->where('expansion.descriptionFallback', true)
            ->where('expansion.selectedEdition.id', $edition->id)->where('expansion.editionFallback', true));
        $edition->update(['is_visible' => false]);
        $expansion->forgetTranslation('display_name', 'ja')->save();
        $this->get($path.'?edition=ja')->assertInertia(fn (Assert $page) => $page
            ->where('expansion.name', 'Base Set')->where('expansion.nameLanguage', null)->where('expansion.nameFallback', true)
            ->where('expansion.selectedEdition.id', $fr->id)->where('expansion.editionFallback', true));
    }

    public function test_each_public_field_exposes_its_own_value_and_language(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon', 'display_name' => ['fr' => 'Jeu', 'en' => 'Game'],
            'reference_description' => 'Historical game description']);
        $catalog = $this->catalog($game);
        $catalog->update(['display_name' => ['fr' => 'Catalogue', 'en' => 'Catalog'], 'description' => ['fr' => 'Présentation']]);
        $market = $this->market();
        $market->update(['display_name' => ['fr' => 'Marché', 'en' => 'Market']]);
        $release = $this->release($this->edition($this->expansion($catalog)), $market);
        $release->update(['announced_period' => ['fr' => 'Septembre', 'en' => null]]);
        // A framework locale may differ from the route's content tag.
        config(['localization.locales.en.laravelLocale' => 'fr']);
        foreach (['fr' => ['Jeu', 'Catalogue', 'Marché', 'Septembre'], 'en' => ['Game', 'Catalog', 'Market', null]] as $locale => [$name, $catalogName, $marketName, $period]) {
            $this->get("/$locale/pokemon")->assertInertia(fn (Assert $page) => $page
                ->where('games.0.name', $name)->where('currentGame.nameLanguage', $locale)->where('currentGame.nameFallback', false)
                ->where('gameDetails.description', 'Historical game description')->where('gameDetails.descriptionLanguage', null)->where('gameDetails.descriptionFallback', true)
                ->where('catalogData.catalogs.0.name', $catalogName)->where('catalogData.catalogs.0.description', 'Présentation')
                ->where('catalogData.catalogs.0.descriptionLanguage', 'fr')->where('catalogData.catalogs.0.descriptionFallback', $locale === 'en'));
            $this->get("/$locale/pokemon/international/base-set")->assertInertia(fn (Assert $page) => $page
                ->where('expansion.catalog.nameLanguage', $locale)->where('expansion.catalog.name', $catalogName)
                ->where('expansion.selectedEdition.releases.0.market.name', $marketName)->where('expansion.selectedEdition.releases.0.market.nameLanguage', $locale)
                ->where('expansion.selectedEdition.releases.0.announcedPeriod', $period)->where('expansion.selectedEdition.releases.0.announcedPeriodLanguage', $locale)
                ->where('expansion.selectedEdition.releases.0.announcedPeriodFallback', false));
        }
    }

    public function test_search_matches_names_across_content_languages_and_escapes_literal_characters(): void
    {
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']), $this->language('ja', '日本語'));
        $expansion = $this->expansion($catalog);
        $names = ['fr' => 'Étoile 100%_A\\B', 'ja' => '閃光'];
        $expansion->update(['display_name' => $names]);
        $this->expansion($catalog, key: 'other')->update(['description' => ['ja' => '閃光']]);
        $this->expansion($catalog, key: 'hidden')->update(['display_name' => $names, 'is_visible' => false]);
        foreach (['etoile', '%', '_', '\\', '閃光'] as $query) {
            $this->get('/en/pokemon?q='.rawurlencode($query))->assertInertia(fn (Assert $page) => $page
                ->where('catalogData.expansions.total', 1)->where('catalogData.expansions.data.0.id', $expansion->id)
                ->where('catalogData.total', 2)->where('catalogData.catalogs.0.count', 2));
        }
    }

    public function test_card_queries_stay_bounded_as_series_editions_and_releases_grow(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon']);
        $catalog = $this->catalog($game);
        $addCard = function (int $index) use ($catalog): void {
            $series = $this->series($catalog, key: 'series-'.$index);
            $edition = $this->edition($this->expansion($catalog, $series, key: 'set-'.$index));
            $this->release($edition)->update(['released_on' => '2023-01-01']);
        };
        $readQueries = function () use ($game): int {
            DB::enableQueryLog();
            DB::flushQueryLog();
            try {
                app(PublicCatalog::class)->index($game, request());

                return count(DB::getQueryLog());
            } finally {
                DB::disableQueryLog();
                DB::flushQueryLog();
            }
        };
        $addCard(1);
        $oneCard = $readQueries();
        $addCard(2);
        $addCard(3);
        $this->assertGreaterThan(0, $oneCard);
        $this->assertSame($oneCard, $readQueries());
    }

    public function test_sort_matches_displayed_names_including_historical_language_case_and_stable_ties(): void
    {
        $fr = $this->language('fr');
        DB::table('languages')->where('id', $fr->id)->update(['code' => 'FR']);
        $catalog = $this->catalog(Game::factory()->create(['slug' => 'pokemon']), $fr);
        $a = $this->expansion($catalog, key: 'first');
        $a->update(['name' => 'Zulu', 'display_name' => ['fr' => 'Alpha']]);
        $b = $this->expansion($catalog, key: 'second');
        $b->update(['display_name' => ['en' => 'Alpha']]);
        $this->edition($a, $fr);
        $this->edition($b, $fr);
        $this->expansion($catalog, key: 'third')->update(['name' => 'Middle']);
        $this->get('/en/pokemon?sort=name')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.data.0.id', $a->id)->where('catalogData.expansions.data.0.name', 'Alpha')
            ->where('catalogData.expansions.data.1.id', $b->id)->where('catalogData.expansions.data.2.name', 'Middle'));
        $this->get('/en/pokemon?language=invalid%2Ftag')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.total', 0));
        $this->get('/en/pokemon?language=fr')->assertInertia(fn (Assert $page) => $page
            ->where('catalogData.expansions.total', 2)->where('catalogData.languages.0.code', 'fr')
            ->where('catalogData.expansions.data.0.edition.language.code', 'fr'));
    }
}
