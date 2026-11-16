<?php

namespace App\Actions\Catalog;

use App\Support\Catalog\CatalogRules;
use App\Support\Localization\TranslationPatch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use RuntimeException;

final class SaveTranslations
{
    /**
     * @template T of Model
     *
     * @param  T  $record
     * @param  array<string, mixed>  $values
     * @return T
     */
    public function handle(Model $record, string $locale, array $values, bool $remove = false): Model
    {
        if (! CatalogRules::validLanguageCode($locale) || ! $record->getConnection()->table('languages')->whereRaw('lower(code) = lower(?)', [CatalogRules::languageCode($locale)])->exists()) {
            throw ValidationException::withMessages(['locale' => __('validation.catalog.exists')]);
        }
        $record = clone $record;
        $attributes = TranslationPatch::attributes($record);
        if (! method_exists($record, 'setTranslation') || ! method_exists($record, 'forgetTranslation') || $attributes === [] || array_diff(array_keys($values), $attributes) !== []) {
            throw ValidationException::withMessages(['translations' => __('validation.catalog.unknown_type')]);
        }
        foreach ($remove ? $attributes : array_keys($values) as $attribute) {
            if ($remove) {
                $record->forgetTranslation($attribute, $locale);
            } else {
                $record->setTranslation($attribute, $locale, $values[$attribute]);
            }
        }
        if (! $record->save()) {
            throw new RuntimeException(__('validation.catalog.save_cancelled'));
        }

        return $record;
    }
}
