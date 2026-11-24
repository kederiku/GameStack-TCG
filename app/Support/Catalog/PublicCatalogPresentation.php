<?php

namespace App\Support\Catalog;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Language;
use App\Models\Series;
use App\Services\CatalogImages;
use App\Support\Localization\LocalizedRoutes;
use App\Support\Localization\TranslatedContent;

class PublicCatalogPresentation
{
    /** @return array<string, mixed> */
    public function card(Expansion $expansion, string $language = '*'): array
    {
        $catalog = $expansion->catalog;
        $edition = $this->selectEdition($expansion, $language);
        $releases = $expansion->editions
            ->filter(fn (ExpansionEdition $item) => $language === '*' || CatalogRules::languageCode($item->language->code) === $language)
            ->flatMap(fn (ExpansionEdition $item) => $item->releases->map(fn (ExpansionRelease $release) => [
                ...$this->release($release), 'language' => CatalogRules::languageCode($item->language->code),
            ]))
            ->filter(fn (array $release) => $release['status'] !== 'cancelled' && $release['releasedOn'] !== null)
            ->sortBy('releasedOn');

        return [
            'id' => $expansion->id,
            'key' => $expansion->key,
            ...CatalogPresentation::displayName($expansion),
            ...CatalogPresentation::description($expansion),
            'type' => $expansion->type,
            'url' => LocalizedRoutes::url('games.expansions.show', ['game' => $catalog->game->slug, 'catalogKey' => $catalog->key, 'expansionKey' => $expansion->key], absolute: false),
            'catalog' => $this->catalog($catalog),
            'series' => $expansion->series ? $this->series($expansion->series, $catalog) : null,
            'languages' => $expansion->editions->map(fn (ExpansionEdition $item) => self::language($item->language))->values()->all(),
            'edition' => $edition ? $this->edition($edition) : null,
            'firstRelease' => $releases->first(),
        ];
    }

    public function selectEdition(Expansion $expansion, string $requested): ?ExpansionEdition
    {
        $requested = CatalogRules::languageCode($requested);

        return $expansion->editions->first(fn (ExpansionEdition $item) => CatalogRules::languageCode($item->language->code) === $requested)
            ?? $expansion->editions->first(fn (ExpansionEdition $item) => $item->language_id === $expansion->catalog->default_language_id)
            ?? $expansion->editions->first();
    }

    /** @return array<string, mixed> */
    public function edition(ExpansionEdition $edition): array
    {
        return [
            'id' => $edition->id,
            'name' => $edition->name,
            'language' => self::language($edition->language),
            'officialCode' => $edition->official_code,
            'imageUrl' => $edition->image ? CatalogImages::disk()->url($edition->image) : null,
            'status' => $edition->status,
            'releases' => $edition->releases->sortBy(fn (ExpansionRelease $release) => $release->released_on?->format('Y-m-d') ?? '9999-12-31')
                ->map(fn (ExpansionRelease $release) => $this->release($release))->values()->all(),
        ];
    }

    /** @return array<string, mixed> */
    private function release(ExpansionRelease $release): array
    {
        $period = TranslatedContent::field($release, 'announced_period', LocalizedRoutes::locale(request()));

        return [
            'id' => $release->id,
            'market' => ['key' => $release->market->key, ...CatalogPresentation::displayName($release->market)],
            'channel' => $release->channel,
            'releasedOn' => $release->released_on?->format('Y-m-d'),
            'announcedPeriod' => $period['value'],
            'announcedPeriodLanguage' => $period['locale'],
            'announcedPeriodFallback' => $period['fallback'],
            'status' => $release->status,
        ];
    }

    /** @return array<string, mixed> */
    private function series(Series $series, Catalog $catalog): array
    {
        $series->setRelation('catalog', $catalog);

        return [
            'id' => $series->id, 'key' => $series->key,
            ...CatalogPresentation::displayName($series),
            ...CatalogPresentation::description($series),
            'url' => LocalizedRoutes::url('games.show', ['game' => $catalog->game->slug, 'catalog' => $catalog->key, 'series' => $series->key], absolute: false),
        ];
    }

    /** @return array<string, mixed> */
    public function catalog(Catalog $catalog): array
    {
        return [
            'id' => $catalog->id, 'key' => $catalog->key,
            ...CatalogPresentation::displayName($catalog),
            ...CatalogPresentation::description($catalog),
        ];
    }

    /** @return array{code: string, name: string} */
    public static function language(Language $language): array
    {
        return ['code' => CatalogRules::languageCode($language->code), 'name' => $language->name];
    }
}
