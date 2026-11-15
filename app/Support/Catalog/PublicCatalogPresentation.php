<?php

namespace App\Support\Catalog;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Series;
use App\Services\CatalogImages;

class PublicCatalogPresentation
{
    /** @return array<string, mixed> */
    public function card(Expansion $expansion, string $language = '*'): array
    {
        $catalog = $expansion->catalog;
        $edition = $this->selectEdition($expansion, $language);
        $releases = $expansion->editions
            ->filter(fn (ExpansionEdition $item) => $language === '*' || $item->language->code === $language)
            ->flatMap(fn (ExpansionEdition $item) => $item->releases->map(fn (ExpansionRelease $release) => [
                ...$this->release($release), 'language' => $item->language->code,
            ]))
            ->filter(fn (array $release) => $release['status'] !== 'cancelled' && $release['releasedOn'] !== null)
            ->sortBy('releasedOn');

        return [
            'id' => $expansion->id,
            'key' => $expansion->key,
            ...$this->name($expansion, $catalog),
            'type' => $expansion->type,
            'url' => route('games.expansions.show', ['game' => $catalog->game->slug, 'catalogKey' => $catalog->key, 'expansionKey' => $expansion->key], absolute: false),
            'catalog' => ['id' => $catalog->id, 'key' => $catalog->key, 'name' => $catalog->name],
            'series' => $expansion->series ? $this->series($expansion->series, $catalog) : null,
            'languages' => $expansion->editions->map(fn (ExpansionEdition $item) => [
                'code' => $item->language->code, 'name' => $item->language->name,
            ])->values()->all(),
            'edition' => $edition ? $this->edition($edition) : null,
            'firstRelease' => $releases->first(),
        ];
    }

    public function selectEdition(Expansion $expansion, string $requested): ?ExpansionEdition
    {
        return $expansion->editions->first(fn (ExpansionEdition $item) => $item->language->code === $requested)
            ?? $expansion->editions->first(fn (ExpansionEdition $item) => $item->language_id === $expansion->catalog->default_language_id)
            ?? $expansion->editions->first();
    }

    /** @return array<string, mixed> */
    public function edition(ExpansionEdition $edition): array
    {
        return [
            'id' => $edition->id,
            'name' => $edition->name,
            'language' => ['code' => $edition->language->code, 'name' => $edition->language->name],
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
        return [
            'id' => $release->id,
            'market' => ['key' => $release->market->key, 'name' => $release->market->name],
            'channel' => $release->channel,
            'releasedOn' => $release->released_on?->format('Y-m-d'),
            'announcedPeriod' => $release->announced_period,
            'status' => $release->status,
        ];
    }

    /** @return array<string, mixed> */
    private function series(Series $series, Catalog $catalog): array
    {
        return [
            'id' => $series->id, 'key' => $series->key,
            ...$this->name($series, $catalog),
            'url' => route('games.show', ['game' => $catalog->game->slug, 'catalog' => $catalog->key, 'series' => $series->key], absolute: false),
        ];
    }

    /** @return array{name: string, nameLanguage: ?string, nameFallback: bool, description: ?string} */
    private function name(Series|Expansion $record, Catalog $catalog): array
    {
        $locale = app()->getLocale();
        $translation = $record->localizations->first(fn ($item) => $item->language->code === $locale)
            ?? $record->localizations->first(fn ($item) => $item->language_id === $catalog->default_language_id);

        return [
            'name' => $translation->name ?? $record->name,
            'nameLanguage' => $translation?->language->code,
            'nameFallback' => $translation?->language->code !== $locale,
            'description' => $translation?->description,
        ];
    }
}
