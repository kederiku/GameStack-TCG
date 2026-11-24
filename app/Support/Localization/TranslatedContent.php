<?php

namespace App\Support\Localization;

use App\Models\Expansion;
use App\Models\Series;
use App\Support\Catalog\CatalogRules;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

/** @phpstan-type ResolvedText array{value: ?string, locale: ?string, fallback: bool} */
final class TranslatedContent
{
    public const string NAME_FALLBACK_LOCALE = 'en';

    /**
     * @param  array<string, ?string>  $translations
     * @param  list<string>  $locales
     * @return ResolvedText
     */
    public static function resolve(array $translations, array $locales, ?string $reference = null): array
    {
        foreach (array_values(array_unique($locales)) as $index => $locale) {
            if (array_key_exists($locale, $translations)) {
                return ['value' => $translations[$locale], 'locale' => $locale, 'fallback' => $index !== 0];
            }
        }

        return ['value' => $reference, 'locale' => null, 'fallback' => true];
    }

    /** @return ResolvedText */
    public static function field(Model $record, string $attribute, string $locale): array
    {
        if (! method_exists($record, 'getTranslations') || ! method_exists($record, 'isTranslatableAttribute') || ! $record->isTranslatableAttribute($attribute)) {
            throw new InvalidArgumentException('Unknown translated field.');
        }
        $locale = CatalogRules::languageCode($locale);
        $fallback = $record instanceof Series || $record instanceof Expansion
            ? CatalogRules::languageCode($record->catalog->defaultLanguage->code)
            : Locales::default();
        $reference = $attribute === 'display_name' ? $record->getAttribute('name') : $record->getAttribute('reference_'.$attribute);
        $locales = $attribute === 'display_name' ? [$locale, self::NAME_FALLBACK_LOCALE, $fallback] : [$locale, $fallback];

        return self::resolve($record->getTranslations($attribute), $locales, $reference);
    }
}
