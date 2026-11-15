<?php

namespace Tests\Concerns;

use App\Actions\Catalog\SaveCatalogRecord;
use App\Models\Catalog;
use App\Models\CatalogRecord;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;

trait BuildsCatalog
{
    protected function language(string $code = 'en', string $name = 'Anglais'): Language
    {
        return Language::query()->where('code', $code)->first()
            ?? $this->saveCatalog(new Language, ['code' => $code, 'name' => $name]);
    }

    protected function catalog(?Game $game = null, ?Language $language = null, string $key = 'international'): Catalog
    {
        return $this->saveCatalog(new Catalog, [
            'game_id' => ($game ?? Game::factory()->create())->id,
            'default_language_id' => ($language ?? $this->language())->id,
            'key' => $key,
            'name' => 'International',
        ]);
    }

    protected function series(Catalog $catalog, string $key = 'scarlet-and-violet'): Series
    {
        return $this->saveCatalog(new Series, [
            'catalog_id' => $catalog->id,
            'key' => $key,
            'name' => 'Scarlet & Violet',
            'type' => 'series',
        ]);
    }

    protected function expansion(Catalog $catalog, ?Series $series = null, string $key = 'base-set'): Expansion
    {
        return $this->saveCatalog(new Expansion, [
            'catalog_id' => $catalog->id,
            'series_id' => $series?->id,
            'key' => $key,
            'name' => 'Base Set',
            'type' => 'main',
        ]);
    }

    protected function edition(Expansion $expansion, ?Language $language = null, string $status = 'announced'): ExpansionEdition
    {
        return $this->saveCatalog(new ExpansionEdition, [
            'expansion_id' => $expansion->id,
            'language_id' => ($language ?? $this->language())->id,
            'name' => 'Scarlet & Violet',
            'status' => $status,
        ]);
    }

    protected function market(string $key = 'france', string $name = 'France'): Market
    {
        return Market::query()->where('key', $key)->first()
            ?? $this->saveCatalog(new Market, ['key' => $key, 'name' => $name]);
    }

    protected function release(ExpansionEdition $edition, ?Market $market = null, string $channel = 'general'): ExpansionRelease
    {
        return $this->saveCatalog(new ExpansionRelease, [
            'edition_id' => $edition->id,
            'market_id' => ($market ?? $this->market())->id,
            'channel' => $channel,
            'status' => 'announced',
        ]);
    }

    /**
     * @template T of CatalogRecord
     *
     * @param  T  $record
     * @param  array<string, mixed>  $data
     * @return T
     */
    protected function saveCatalog(CatalogRecord $record, array $data): CatalogRecord
    {
        return app(SaveCatalogRecord::class)->handle($record, $data);
    }
}
