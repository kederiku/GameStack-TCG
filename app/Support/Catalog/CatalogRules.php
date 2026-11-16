<?php

namespace App\Support\Catalog;

use App\Models\Catalog;
use App\Models\CatalogRecord;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionLocalization;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Models\Market;
use App\Models\Series;
use App\Models\SeriesImage;
use App\Models\SeriesLocalization;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;
use Illuminate\Validation\Rules\In;
use Illuminate\Validation\ValidationException;

class CatalogRules
{
    // RFC 5646: normal language tags, private-use tags and the grandfathered forms.
    private const LANGUAGE_TAG = '/\A(?:
        (?:[a-z]{2,3}(?:-[a-z]{3}){0,3}|[a-z]{4,8})
        (?:-[a-z]{4})?(?:-[a-z]{2}|-[0-9]{3})?
        (?:-[a-z0-9]{5,8}|-[0-9][a-z0-9]{3})*
        (?:-[0-9a-wy-z](?:-[a-z0-9]{2,8})+)*
        (?:-x(?:-[a-z0-9]{1,8})+)?
        |x(?:-[a-z0-9]{1,8})+
        |en-gb-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|tao|tay|tsu)
        |sgn-(?:be-fr|be-nl|ch-de)|art-lojban|cel-gaulish|no-bok|no-nyn
        |zh-(?:guoyu|hakka|min|min-nan|xiang)
    )\z/ix';

    public static function validate(CatalogRecord $record): void
    {
        $rules = self::fields($record);
        $data = array_map(
            static fn (mixed $value): mixed => is_string($value) ? trim($value) : $value,
            array_intersect_key($record->getAttributes(), $rules),
        );

        if ($record instanceof Language && is_string($data['code'] ?? null)) {
            $data['code'] = $record->exists && strcasecmp($data['code'], (string) $record->getRawOriginal('code')) === 0 ? $record->getRawOriginal('code') : self::languageCode($data['code']);
        }

        // Validate the parent ID before attempting to look up a proposed key in its scope.
        $proposeKey = ! $record->exists && isset($rules['key']) && blank($data['key'] ?? null);
        if ($proposeKey) {
            $data['key'] = 'proposition';
        }

        foreach ($rules as $field => $fieldRules) {
            if (in_array('nullable', $fieldRules, true) && ($data[$field] ?? null) === '') {
                $data[$field] = null;
            }
        }

        $data = Validator::make($data, $rules, [
            'required' => __('validation.catalog.required'),
            'string' => __('validation.catalog.string'),
            'integer' => __('validation.catalog.integer'),
            'boolean' => __('validation.catalog.boolean'),
            'exists' => __('validation.catalog.exists'),
            'in' => __('validation.catalog.in'),
            'regex' => __('validation.catalog.regex'),
            'max.string' => __('validation.catalog.max_string'),
            'between.numeric' => __('validation.catalog.between_numeric'),
            'date_format' => __('validation.catalog.date_format'),
            'url' => __('validation.catalog.url'),
        ])->validate();

        if ($proposeKey) {
            $data['key'] = self::proposeKey($record, $data);
        }

        self::checkIdentity($record, $data);
        self::checkRelationships($record, $data);
        self::checkUniqueness($record, $data);
        $record->forceFill($data);
    }

    public static function validLanguageCode(string $code): bool
    {
        return preg_match(self::LANGUAGE_TAG, trim($code)) === 1;
    }

    public static function languageCode(string $code): string
    {
        $parts = explode('-', strtolower(trim($code)));
        $index = 1;

        // Script and region use their usual casing. Extension and private-use subtags stay lowercase.
        if (strlen($parts[0]) >= 2) {
            while ($index <= 3 && preg_match('/\A[a-z]{3}\z/', $parts[$index] ?? '')) {
                $index++;
            }

            if (preg_match('/\A[a-z]{4}\z/', $parts[$index] ?? '')) {
                $parts[$index] = ucfirst($parts[$index]);
                $index++;
            }

            if (preg_match('/\A[a-z]{2}\z/', $parts[$index] ?? '')) {
                $parts[$index] = strtoupper($parts[$index]);
            }
        }

        return implode('-', $parts);
    }

    public static function rejectDuplicate(CatalogRecord $record): never
    {
        $field = match (true) {
            $record instanceof Language => 'code',
            $record instanceof SeriesImage, $record instanceof SeriesLocalization, $record instanceof ExpansionLocalization, $record instanceof ExpansionEdition => 'language_id',
            $record instanceof ExpansionRelease => 'channel',
            $record instanceof ExpansionRelation => 'target_expansion_id',
            default => 'key',
        };
        $message = match ($field) {
            'code' => __('validation.catalog.duplicate_language'),
            'language_id' => __('validation.catalog.duplicate_locale'),
            'channel' => __('validation.catalog.duplicate_release'),
            'target_expansion_id' => __('validation.catalog.duplicate_relation'),
            default => __('validation.catalog.duplicate_key'),
        };

        self::reject($field, $message);
    }

    public static function rejectRestrictedDelete(QueryException $exception, CatalogRecord|Game $record): never
    {
        if (! in_array($exception->getCode(), ['23001', '23503'], true)) {
            throw $exception;
        }

        $message = match (true) {
            $record instanceof Game => __('validation.catalog.game_has_catalogs'),
            $record instanceof Catalog => __('validation.catalog.catalog_has_children'),
            $record instanceof Series => __('validation.catalog.series_has_expansions'),
            $record instanceof Expansion => __('validation.catalog.expansion_has_children'),
            $record instanceof Language => __('validation.catalog.language_in_use'),
            $record instanceof Market => __('validation.catalog.market_in_use'),
            default => __('validation.catalog.record_in_use'),
        };

        self::reject('record', $message);
    }

    /** @return array<string, list<ValidationRule|Exists|In|string>> */
    private static function fields(CatalogRecord $record): array
    {
        $text = ['required', 'string', 'max:255'];
        $optionalText = ['nullable', 'string', 'max:255'];
        $description = ['nullable', 'string'];
        $key = ['required', 'string', 'max:255', 'regex:/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/'];
        $visibility = ['required', 'boolean'];
        $order = ['required', 'integer', 'between:0,2147483647'];
        $url = ['string', 'url:http,https', 'max:2048'];
        $table = $record->getConnection()->getName().'.';
        $fk = static fn (string $name): array => ['bail', 'required', 'integer', Rule::exists($table.$name, 'id')];

        return match (true) {
            $record instanceof Language => ['code' => [...$text, 'regex:'.self::LANGUAGE_TAG], 'name' => $text],
            $record instanceof Market => ['key' => $key, 'name' => $text],
            $record instanceof Catalog => [
                'game_id' => $fk('games'), 'default_language_id' => $fk('languages'),
                'key' => $key, 'name' => $text, 'reference_description' => $description, 'sort_order' => $order, 'is_visible' => $visibility,
            ],
            $record instanceof Series => [
                'catalog_id' => $fk('catalogs'), 'key' => $key, 'name' => $text,
                'type' => ['required', 'string', Rule::in(Series::TYPES)], 'sort_order' => $order, 'is_visible' => $visibility,
            ],
            $record instanceof SeriesImage => ['series_id' => $fk('series'), 'language_id' => $fk('languages')],
            $record instanceof SeriesLocalization => [
                'series_id' => $fk('series'), 'language_id' => $fk('languages'),
                'name' => $text, 'description' => $description,
            ],
            $record instanceof Expansion => [
                'catalog_id' => $fk('catalogs'), 'series_id' => ['bail', 'nullable', 'integer', Rule::exists($table.'series', 'id')],
                'key' => $key, 'name' => $text, 'type' => ['required', 'string', Rule::in(Expansion::TYPES)],
                'sort_order' => $order, 'is_visible' => $visibility,
            ],
            $record instanceof ExpansionLocalization => [
                'expansion_id' => $fk('expansions'), 'language_id' => $fk('languages'), 'name' => $text, 'description' => $description,
            ],
            $record instanceof ExpansionEdition => [
                'expansion_id' => $fk('expansions'), 'language_id' => $fk('languages'),
                'name' => $text, 'official_code' => $optionalText,
                'status' => ['required', 'string', Rule::in(ExpansionEdition::STATUSES)], 'is_visible' => $visibility,
            ],
            $record instanceof ExpansionRelease => [
                'edition_id' => $fk('expansion_editions'), 'market_id' => $fk('markets'),
                'channel' => ['required', 'string', Rule::in(ExpansionRelease::CHANNELS)],
                'released_on' => ['nullable', 'string', 'date_format:Y-m-d'], 'reference_announced_period' => $optionalText,
                'status' => ['required', 'string', Rule::in(ExpansionRelease::STATUSES)], 'source_url' => ['nullable', ...$url],
            ],
            $record instanceof ExpansionRelation => [
                'source_expansion_id' => $fk('expansions'), 'target_expansion_id' => $fk('expansions'),
                'type' => ['required', 'string', Rule::in(ExpansionRelation::TYPES)],
                'notes' => ['required', 'string'], 'source_url' => ['required', ...$url],
            ],
            default => throw new \LogicException(__('validation.catalog.unknown_type')),
        };
    }

    /** @param array<string, mixed> $data */
    private static function checkIdentity(CatalogRecord $record, array $data): void
    {
        if (! $record->exists) {
            return;
        }

        $identity = match (true) {
            $record instanceof Catalog => ['game_id', 'key'],
            $record instanceof Series, $record instanceof Expansion => ['catalog_id', 'key'],
            $record instanceof SeriesImage, $record instanceof SeriesLocalization => ['series_id', 'language_id'],
            $record instanceof ExpansionLocalization, $record instanceof ExpansionEdition => ['expansion_id', 'language_id'],
            $record instanceof ExpansionRelease => ['edition_id', 'market_id', 'channel'],
            $record instanceof ExpansionRelation => ['source_expansion_id', 'target_expansion_id', 'type'],
            $record instanceof Language => ['code'],
            $record instanceof Market => ['key'],
            default => [],
        };

        foreach (['id', ...$identity] as $field) {
            $value = $data[$field] ?? $record->getAttributes()[$field] ?? null;

            if ((string) $value !== (string) $record->getRawOriginal($field)) {
                self::reject($field, __('validation.catalog.immutable_identity'));
            }
        }
    }

    /** @param array<string, mixed> $data */
    private static function checkRelationships(CatalogRecord $record, array &$data): void
    {
        if ($record instanceof Expansion && ($data['series_id'] ?? null) !== null
            && ! Series::on($record->getConnectionName())->whereKey($data['series_id'])->where('catalog_id', $data['catalog_id'])->exists()) {
            self::reject('series_id', __('validation.catalog.series_catalog_mismatch'));
        }

        if ($record instanceof ExpansionEdition && $record->exists && $data['status'] !== 'released'
            && $record->releases()->where('status', 'released')->exists()) {
            self::reject('status', __('validation.catalog.edition_has_releases'));
        }

        if ($record instanceof ExpansionRelation) {
            $source = (int) $data['source_expansion_id'];
            $target = (int) $data['target_expansion_id'];

            if ($source === $target) {
                self::reject('target_expansion_id', __('validation.catalog.self_relationship'));
            }

            $gameIds = Expansion::on($record->getConnectionName())
                ->join('catalogs', 'catalogs.id', '=', 'expansions.catalog_id')
                ->whereIn('expansions.id', [$source, $target])
                ->pluck('catalogs.game_id');

            if ($gameIds->count() !== 2 || $gameIds[0] !== $gameIds[1]) {
                self::reject('target_expansion_id', __('validation.catalog.game_mismatch'));
            }

            if ($data['type'] === 'related_to' && $source > $target) {
                [$data['source_expansion_id'], $data['target_expansion_id']] = [$target, $source];
            }
        }
    }

    /** @param array<string, mixed> $data */
    private static function checkUniqueness(CatalogRecord $record, array $data): void
    {
        $columns = match (true) {
            $record instanceof Catalog => ['game_id', 'key'],
            $record instanceof Series, $record instanceof Expansion => ['catalog_id', 'key'],
            $record instanceof SeriesImage, $record instanceof SeriesLocalization => ['series_id', 'language_id'],
            $record instanceof ExpansionLocalization, $record instanceof ExpansionEdition => ['expansion_id', 'language_id'],
            $record instanceof ExpansionRelease => ['edition_id', 'market_id', 'channel'],
            $record instanceof ExpansionRelation => ['source_expansion_id', 'target_expansion_id', 'type'],
            $record instanceof Market => ['key'],
            default => [],
        };

        $query = $record->newQuery();

        if ($record->exists) {
            $query->whereKeyNot($record->id);
        }

        if ($record instanceof Language) {
            $query->whereRaw('lower(code) = lower(?)', [$data['code']]);
        } else {
            foreach ($columns as $column) {
                $query->where($column, $data[$column]);
            }
        }

        if ($query->exists()) {
            self::rejectDuplicate($record);
        }
    }

    /** @param array<string, mixed> $data */
    private static function proposeKey(CatalogRecord $record, array $data): string
    {
        $base = trim(substr(Str::slug((string) $data['name']), 0, 240), '-');
        $base = $base === '' ? Str::kebab(class_basename($record)) : $base;
        $scope = match (true) {
            $record instanceof Catalog => ['game_id' => $data['game_id']],
            $record instanceof Series, $record instanceof Expansion => ['catalog_id' => $data['catalog_id']],
            default => [],
        };
        $key = $base;

        for ($suffix = 2; $record->newQuery()->where($scope)->where('key', $key)->exists(); $suffix++) {
            $key = $base.'-'.$suffix;
        }

        return $key;
    }

    private static function reject(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
