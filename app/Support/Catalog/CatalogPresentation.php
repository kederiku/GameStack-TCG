<?php

namespace App\Support\Catalog;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\Market;
use App\Models\Series;
use App\Support\Localization\Locales;
use App\Support\Localization\LocalizedRoutes;
use App\Support\Localization\TranslatedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use InvalidArgumentException;

class CatalogPresentation
{
    /** @return array{name: string, nameLanguage: ?string, nameFallback: bool} */
    public static function displayName(Model $record, ?string $locale = null): array
    {
        $text = TranslatedContent::field($record, 'display_name', $locale ?? LocalizedRoutes::locale(request()));

        // Names have a required scalar reference even when no translation exists.
        return ['name' => (string) $text['value'], 'nameLanguage' => $text['locale'], 'nameFallback' => $text['fallback']];
    }

    /** Scalar SQL equivalent of the name resolver, used before pagination and in related-record sorts. */
    public static function nameForQuery(Model $record, string $locale): Builder
    {
        $query = $record->getConnection()->query();
        $table = match (true) {
            $record instanceof Game => 'games',
            $record instanceof Catalog => 'catalogs',
            $record instanceof Market => 'markets',
            $record instanceof Series => 'series',
            $record instanceof Expansion => 'expansions',
            default => throw new InvalidArgumentException('No translated name for this record.'),
        };
        $name = $table.'.name';
        $names = $table.'.display_name';
        $bindings = [CatalogRules::languageCode($locale), TranslatedContent::NAME_FALLBACK_LOCALE];
        if ($record instanceof Series || $record instanceof Expansion) {
            $catalogId = $table.'.catalog_id';

            // Historical language rows retain their original casing; JSON keys are canonical.
            $fallback = "(select translation.value from catalogs
                join languages on languages.id = catalogs.default_language_id
                cross join jsonb_each($names) as translation
                where catalogs.id = $catalogId and lower(translation.key) = lower(languages.code))";
        } else {
            $fallback = "$names -> ?::text";
            $bindings[] = Locales::default();
        }

        // Select in JSON before converting to text so an explicit null stops fallback.
        return $query->selectRaw("coalesce(
            coalesce($names -> ?::text, $names -> ?::text, $fallback, to_jsonb($name)) #>> '{}',
            ''
        )", $bindings);
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
