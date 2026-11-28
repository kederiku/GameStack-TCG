<?php

namespace Tests\Unit;

use Database\Seeders\PokemonCatalogData;
use PHPUnit\Framework\TestCase;

class PokemonCatalogDataTest extends TestCase
{
    public function test_reference_records_have_unique_identities_and_traceable_rows(): void
    {
        $sources = require __DIR__.'/../../database/seeders/data/tcg-sources.php';
        foreach (PokemonCatalogData::load()['catalogs'] as $catalog) {
            $keys = [];
            $series = array_column(array_column($catalog['series'], 'attributes'), 'key');
            $this->assertSame($series, array_values(array_unique($series)));
            foreach ($catalog['expansions'] as $expansion) {
                $key = $expansion['attributes']['key'];
                $this->assertNotContains($key, $keys, $catalog['attributes']['key'].'/'.$key);
                $keys[] = $key;
                $this->assertContains($expansion['series'], $series, $key);
                $this->assertGreaterThanOrEqual(2, $expansion['source_row']);
                $this->assertArrayHasKey($expansion['sources']['name'], $sources);
                $languages = array_column($expansion['editions'], 'language');
                $this->assertSame($languages, array_values(array_unique($languages)), $key);
                foreach ($expansion['editions'] as $edition) {
                    $releases = array_map(fn (array $release) => $release['market'].'/'.$release['attributes']['channel'], $edition['releases']);
                    $this->assertSame($releases, array_values(array_unique($releases)), $key);
                }
            }
        }
    }

    public function test_documentary_codes_and_periods_do_not_become_false_official_codes_or_dates(): void
    {
        $catalogs = [];
        foreach (PokemonCatalogData::load()['catalogs'] as $catalog) {
            $catalogs[$catalog['attributes']['key']] = collect($catalog['expansions'])->keyBy('attributes.key')->all();
        }
        foreach (['base-set-2', 'gym-heroes', 'gym-challenge', 'legendary-collection', 'skyridge', 'ex-team-rocket-returns', 'arceus', 'legendary-treasures'] as $key) {
            $this->assertSame(['en'], array_column($catalogs['international'][$key]['editions'], 'language'), $key);
        }
        $base = $catalogs['international']['base-set']['editions'][0];
        $this->assertArrayNotHasKey('official_code', $base['attributes']);
        $this->assertSame([], $base['releases']);
        $this->assertSame([], $catalogs['korean']['scarlet-ex']['editions'][0]['releases']);
        $this->assertSame([], $catalogs['traditional-chinese']['scarlet-ex']['editions'][0]['releases']);
        $this->assertSame('1997-03', $catalogs['japanese']['pokemon-jungle']['editions'][0]['releases'][0]['attributes']['reference_announced_period']);
        $this->assertNull($catalogs['japanese']['pokemon-jungle']['editions'][0]['releases'][0]['attributes']['released_on']);
        $this->assertSame('announced', $catalogs['thai']['aura-storm']['editions'][0]['attributes']['status']);
        $this->assertArrayNotHasKey('aura-seeker', $catalogs['korean']);
        $this->assertArrayNotHasKey('aura-seeker', $catalogs['traditional-chinese']);
    }

    public function test_mcdonalds_regional_contents_and_release_years_remain_distinct(): void
    {
        $catalog = PokemonCatalogData::load()['catalogs'][0];
        $sets = collect($catalog['expansions'])->keyBy('attributes.key');
        $french = $sets['mcdonalds-2018-france']['editions'];
        $international = $sets['mcdonalds-2018-international']['editions'];
        $this->assertSame(['fr'], array_column($french, 'language'));
        $this->assertSame('france', $french[0]['releases'][0]['market']);
        $this->assertSame('quebec', $international[0]['releases'][0]['market']);
        $this->assertSame('2018-10', $international[0]['releases'][0]['attributes']['reference_announced_period']);
        $editions = collect($sets['mcdonalds-2024']['editions'])->keyBy('language');
        $this->assertSame('2024-12-04', $editions['fr']['releases'][0]['attributes']['released_on']);
        $this->assertSame('2025-01-21', $editions['en']['releases'][0]['attributes']['released_on']);
        $this->assertArrayNotHasKey('mcdonalds-2020', $sets->all());
        $this->assertArrayNotHasKey('trick-or-trade-2025', $sets->all());
    }

    public function test_document_supplements_preserve_explicit_regional_dates_and_unknown_days(): void
    {
        $catalogs = collect(PokemonCatalogData::load()['catalogs'])->keyBy('attributes.key');
        foreach ([
            ['international', 'mcdonalds-2024', 'en', 'united-states', '2025-01-21', null],
            ['international', 'mcdonalds-2024', 'en', 'united-kingdom', null, '2025-05'],
            ['japanese', 'start-deck-100', 'ja', 'japan', '2021-12-17', null],
            ['japanese', 'expansion-pack-base-set', 'ja', 'japan', '1996-10-20', null],
            ['korean', 'start-deck-100', 'ko', 'south-korea', '2022-04-23', null],
            ['traditional-chinese', 'start-deck-100', 'zh-Hant', 'taiwan', '2022-02-18', null],
            ['traditional-chinese', 'ex-legend-maker', 'zh-Hant', 'taiwan', null, '2006'],
            ['traditional-chinese', 'set-a', 'zh-Hant', 'taiwan', '2019-10-09', null],
            ['thai', '30th-celebration', 'th', 'thailand', '2026-09-16', null],
            ['indonesian', '30th-celebration', 'id', 'indonesia', '2026-09-16', null],
            ['indonesian', 'first-impact-set-a', 'id', 'indonesia', null, '2019-08'],
            ['international', 'pokemon-futsal', 'en', 'united-kingdom', null, '2020-09'],
            ['simplified-chinese', 'dragonite-mewtwo-camerupt-sinistcha-happy-set', 'zh-Hans', 'mainland-china', null, '2026-07'],
        ] as [$catalog, $key, $language, $market, $date, $period]) {
            $expansion = collect($catalogs[$catalog]['expansions'])->keyBy('attributes.key')[$key];
            $edition = collect($expansion['editions'])->keyBy('language')[$language];
            $release = collect($edition['releases'])->keyBy('market')->get($market);
            $this->assertNotNull($release, "$catalog/$key/$market");
            $this->assertSame($date, $release['attributes']['released_on'], "$catalog/$key/$market");
            $this->assertSame($period, $release['attributes']['reference_announced_period'], "$catalog/$key/$market");
        }
    }

    public function test_translated_reference_titles_are_not_used_as_native_official_names(): void
    {
        $catalogs = collect(PokemonCatalogData::load()['catalogs'])->keyBy('attributes.key');
        $japanese = collect($catalogs['japanese']['expansions'])->keyBy('attributes.key');
        $this->assertSame('Expansion Pack (Base Set)', $japanese['expansion-pack-base-set']['attributes']['display_name']['en']);
        $this->assertSame('—', $japanese['expansion-pack-base-set']['editions'][0]['attributes']['name']);
        $this->assertSame('ポケモンカード151', $japanese['151']['editions'][0]['attributes']['name']);
        $indonesian = collect($catalogs['indonesian']['expansions'])->keyBy('attributes.key');
        $this->assertSame('Hantaman Pertama', $indonesian['first-impact-set-a']['editions'][0]['attributes']['name']);
        $this->assertSame('Kehadiran Juara', $indonesian['presence-of-champions']['editions'][0]['attributes']['name']);
        $chinese = collect($catalogs['simplified-chinese']['expansions'])->keyBy('attributes.key');
        $this->assertSame('启程专题包', $chinese['journey-theme-pack']['editions'][0]['attributes']['name']);
        foreach (['korean' => 'ko', 'traditional-chinese' => 'zh-Hant', 'simplified-chinese' => 'zh-Hans', 'thai' => 'th', 'indonesian' => 'id'] as $catalog => $language) {
            $promos = collect($catalogs[$catalog]['expansions'])->keyBy('attributes.key');
            $this->assertArrayHasKey('sv-p', $promos->all(), $catalog);
            $this->assertSame([$language], array_column($promos['sv-p']['editions'], 'language'));
        }
    }
}
