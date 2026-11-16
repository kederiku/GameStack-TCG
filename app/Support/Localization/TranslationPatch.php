<?php

namespace App\Support\Localization;

use App\Support\Catalog\CatalogRules;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use JsonException;

/** @phpstan-type TranslationOperation array{attribute: string, locale: string, operation: 'set'|'remove', value: ?string} */
final class TranslationPatch
{
    /**
     * @param  array<string, array<string, ?string>>  $original
     * @param  array<string, array<string, ?string>>  $pending
     * @return list<TranslationOperation>
     */
    public static function between(array $original, array $pending): array
    {
        $operations = [];
        foreach ($pending as $attribute => $translations) {
            $before = $original[$attribute] ?? [];
            foreach ($translations as $locale => $value) {
                if (! array_key_exists($locale, $before) || $before[$locale] !== $value) {
                    $operations[] = ['attribute' => $attribute, 'locale' => $locale, 'operation' => 'set', 'value' => $value];
                }
            }
            foreach (array_diff_key($before, $translations) as $locale => $value) {
                $operations[] = ['attribute' => $attribute, 'locale' => $locale, 'operation' => 'remove', 'value' => null];
            }
        }

        return $operations;
    }

    /** @param array<string, mixed> $pending
     * @return list<TranslationOperation>
     */
    public static function forRecord(Model $record, array $pending): array
    {
        $before = $after = [];
        foreach (self::attributes($record) as $attribute) {
            if (array_key_exists($attribute, $pending)) {
                $before[$attribute] = self::decode($record->getRawOriginal($attribute) ?? '{}', $attribute);
                $after[$attribute] = self::decode($pending[$attribute], $attribute);
            }
        }

        $operations = self::between($before, $after);
        foreach ($operations as &$operation) {
            if ($operation['operation'] === 'set') {
                $operation['value'] = self::value($operation['attribute'], $operation['value']);
            }
        }

        return $operations;
    }

    /** @return list<string> */
    public static function attributes(Model $record): array
    {
        return method_exists($record, 'getTranslatableAttributes') ? $record->getTranslatableAttributes() : [];
    }

    /** @return array<string, ?string> */
    public static function decode(mixed $raw, string $attribute): array
    {
        try {
            $value = is_string($raw) ? json_decode($raw, false, flags: JSON_THROW_ON_ERROR) : $raw;
        } catch (JsonException) {
            throw ValidationException::withMessages([$attribute => __('validation.json', ['attribute' => $attribute])]);
        }
        if ((is_string($raw) && ! $value instanceof \stdClass) || (! $value instanceof \stdClass && (! is_array($value) || ($value !== [] && array_is_list($value))))) {
            throw ValidationException::withMessages([$attribute => __('validation.array', ['attribute' => $attribute])]);
        }
        $translations = [];
        foreach ((array) $value as $locale => $text) {
            if (! is_string($locale) || ! CatalogRules::validLanguageCode($locale)) {
                throw ValidationException::withMessages([$attribute => __('validation.catalog.regex')]);
            }
            $locale = CatalogRules::languageCode($locale);
            if (array_key_exists($locale, $translations)) {
                throw ValidationException::withMessages([$attribute => __('validation.catalog.duplicate_locale')]);
            }
            self::value($attribute, $text);
            // Validate historical values, but normalize only changed operations.
            $translations[$locale] = $text;
        }

        return $translations;
    }

    public static function value(string $attribute, mixed $value): ?string
    {
        if ($value !== null && ! is_string($value)) {
            throw ValidationException::withMessages([$attribute => __('validation.catalog.string')]);
        }
        $value = is_string($value) ? trim($value) : null;
        $value = $value === '' ? null : $value;
        if ($attribute === 'display_name' && ($value === null || mb_strlen($value) > 255)) {
            throw ValidationException::withMessages([$attribute => __('validation.catalog.required')]);
        }
        if ($attribute === 'announced_period' && $value !== null && mb_strlen($value) > 255) {
            throw ValidationException::withMessages([$attribute => __('validation.catalog.max_string', ['max' => 255])]);
        }

        return $value;
    }

    /** @param list<TranslationOperation> $operations */
    public static function apply(Model $record, array $operations): void
    {
        $attributes = $record->getAttributes();
        foreach (self::attributes($record) as $attribute) {
            $translations = self::decode($attributes[$attribute] ?? '{}', $attribute);
            foreach ($operations as $operation) {
                if ($operation['attribute'] === $attribute) {
                    if ($operation['operation'] === 'remove') {
                        unset($translations[$operation['locale']]);
                    } else {
                        $translations[$operation['locale']] = $operation['value'];
                    }
                }
            }
            $attributes[$attribute] = json_encode((object) $translations, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        $record->setRawAttributes($attributes);
    }
}
