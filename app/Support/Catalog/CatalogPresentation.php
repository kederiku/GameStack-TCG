<?php

namespace App\Support\Catalog;

use App\Models\Expansion;
use App\Models\ExpansionLocalization;
use App\Models\Series;
use App\Models\SeriesLocalization;

class CatalogPresentation
{
    /** @return array{name: string, language: ?string, fallback: bool} */
    public function name(Series|Expansion $record, string $languageCode): array
    {
        $languageCode = CatalogRules::languageCode($languageCode);
        $localization = $this->localization($record, $languageCode);

        if ($localization !== null) {
            return ['name' => $localization->name, 'language' => $localization->language->code, 'fallback' => false];
        }

        $default = $record->catalog->defaultLanguage->code;
        $localization = $default === $languageCode ? null : $this->localization($record, $default);

        return [
            'name' => $localization->name ?? $record->name,
            'language' => $localization?->language->code,
            'fallback' => true,
        ];
    }

    public function seriesImage(Series $record, string $languageCode): ?string
    {
        $localization = $this->localization($record, CatalogRules::languageCode($languageCode));

        return $localization instanceof SeriesLocalization ? $localization->image : null;
    }

    private function localization(Series|Expansion $record, string $languageCode): SeriesLocalization|ExpansionLocalization|null
    {
        return $record->localizations()
            ->with('language')
            ->whereHas('language', fn ($languages) => $languages->where('code', $languageCode))
            ->first();
    }
}
