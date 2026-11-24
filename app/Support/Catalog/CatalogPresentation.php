<?php

namespace App\Support\Catalog;

use App\Models\Expansion;
use App\Models\Series;
use App\Support\Localization\LocalizedRoutes;
use App\Support\Localization\TranslatedContent;
use Illuminate\Database\Eloquent\Model;

class CatalogPresentation
{
    /** @return array{name: string, nameLanguage: ?string, nameFallback: bool} */
    public static function displayName(Model $record, ?string $locale = null): array
    {
        $text = TranslatedContent::field($record, 'display_name', $locale ?? LocalizedRoutes::locale(request()));

        // Names have a required scalar reference even when no translation exists.
        return ['name' => (string) $text['value'], 'nameLanguage' => $text['locale'], 'nameFallback' => $text['fallback']];
    }

    /** @return array{description: ?string, descriptionLanguage: ?string, descriptionFallback: bool} */
    public static function description(Model $record): array
    {
        $text = TranslatedContent::field($record, 'description', LocalizedRoutes::locale(request()));

        return ['description' => $text['value'], 'descriptionLanguage' => $text['locale'], 'descriptionFallback' => $text['fallback']];
    }

    /** @return array{name: string, language: ?string, fallback: bool} */
    public function name(Series|Expansion $record, string $languageCode): array
    {
        $text = self::displayName($record, $languageCode);

        return ['name' => $text['name'], 'language' => $text['nameLanguage'], 'fallback' => $text['nameFallback']];
    }

    public function seriesImage(Series $record, string $languageCode): ?string
    {
        return $record->imageForLocale(CatalogRules::languageCode($languageCode))?->image;
    }
}
