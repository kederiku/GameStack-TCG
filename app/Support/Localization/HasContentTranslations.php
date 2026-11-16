<?php

namespace App\Support\Localization;

use App\Support\Catalog\CatalogRules;
use Illuminate\Validation\ValidationException;
use Spatie\Translatable\HasTranslations;

trait HasContentTranslations
{
    use HasTranslations {
        setTranslation as private spatieSetTranslation;
        setTranslations as private spatieSetTranslations;
    }

    public function setTranslation(string $key, string $locale, mixed $value): self
    {
        if (! CatalogRules::validLanguageCode($locale)) {
            throw ValidationException::withMessages([$key => __('validation.catalog.regex')]);
        }

        return $this->spatieSetTranslation($key, CatalogRules::languageCode($locale), TranslationPatch::value($key, $value));
    }

    /** @param array<string, mixed> $translations */
    public function setTranslations(string $key, array $translations): self
    {
        $translations = TranslationPatch::decode($translations, $key);
        $this->spatieSetTranslations($key, $translations);
        if ($translations === []) {
            $this->attributes[$key] = '{}';
        }

        return $this;
    }

    public function forgetTranslation(string $key, string $locale): self
    {
        $translations = $this->getTranslations($key);
        unset($translations[CatalogRules::languageCode($locale)]);
        $this->attributes[$key] = json_encode((object) $translations, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this;
    }
}
