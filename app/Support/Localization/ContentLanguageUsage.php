<?php

namespace App\Support\Localization;

use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;
use App\Support\Catalog\CatalogRules;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ContentLanguageUsage
{
    /**
     * @param  list<string>  $codes
     * @return list<string>
     */
    public static function orderedCodes(array $codes): array
    {
        $codes = array_values(array_unique(array_map(static fn (string $code): string => strtolower(CatalogRules::languageCode($code)), $codes)));
        sort($codes);

        return $codes;
    }

    /** @param list<string> $codes */
    public static function lock(array $codes, ?Connection $connection = null): void
    {
        $connection ??= DB::connection();
        foreach (self::orderedCodes($codes) as $code) {
            if ($connection->table('languages')->whereRaw('lower(code) = ?', [$code])->lockForUpdate()->first() === null) {
                throw ValidationException::withMessages(['locale' => __('validation.catalog.exists')]);
            }
        }
    }

    public static function assertDeletable(Language $language): void
    {
        $code = CatalogRules::languageCode($language->code);
        if (in_array($code, array_map(CatalogRules::languageCode(...), array_keys(Locales::all())), true)) {
            self::reject();
        }
        foreach ([Game::class, Catalog::class, Series::class, Expansion::class, Market::class, ExpansionRelease::class] as $model) {
            $record = new $model;
            foreach (TranslationPatch::attributes($record) as $attribute) {
                // jsonb_exists includes present nulls. Identifiers are model-owned constants.
                if ($language->getConnection()->table($record->getTable())->whereJsonContainsKey($attribute.'->'.$code)->exists()) {
                    self::reject();
                }
            }
        }
    }

    private static function reject(): never
    {
        throw ValidationException::withMessages(['record' => __('validation.catalog.language_in_use')]);
    }
}
