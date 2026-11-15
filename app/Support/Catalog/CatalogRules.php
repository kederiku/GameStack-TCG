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
            $data['code'] = self::languageCode($data['code']);
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
            'required' => 'Le champ « :attribute » est obligatoire.',
            'string' => 'Le champ « :attribute » doit être un texte.',
            'integer' => 'Le champ « :attribute » doit être un entier.',
            'boolean' => 'Le champ « :attribute » doit être vrai ou faux.',
            'exists' => 'Le champ « :attribute » doit désigner une fiche existante.',
            'in' => 'La valeur sélectionnée pour « :attribute » est invalide.',
            'regex' => 'Le format du champ « :attribute » est invalide.',
            'max.string' => 'Le champ « :attribute » ne doit pas dépasser :max caractères.',
            'between.numeric' => 'Le champ « :attribute » doit être compris entre :min et :max.',
            'date_format' => 'Le champ « :attribute » doit être une date valide au format AAAA-MM-JJ.',
            'url' => 'Le champ « :attribute » doit être une URL HTTP ou HTTPS.',
        ], [
            'name' => 'nom',
            'key' => 'clé',
            'code' => 'code de langue',
            'game_id' => 'jeu',
            'default_language_id' => 'langue par défaut',
            'catalog_id' => 'catalogue',
            'series_id' => 'série',
            'expansion_id' => 'extension',
            'edition_id' => 'édition',
            'language_id' => 'langue',
            'market_id' => 'marché',
            'sort_order' => 'ordre',
            'is_visible' => 'visibilité',
            'official_code' => 'code officiel',
            'channel' => 'canal',
            'status' => 'état',
            'released_on' => 'date de sortie',
            'announced_period' => 'période annoncée',
            'source_url' => 'URL source',
            'source_expansion_id' => 'extension source',
            'target_expansion_id' => 'extension cible',
        ])->validate();

        if ($proposeKey) {
            $data['key'] = self::proposeKey($record, $data);
        }

        self::checkIdentity($record, $data);
        self::checkRelationships($record, $data);
        self::checkUniqueness($record, $data);
        $record->forceFill($data);
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
            $record instanceof SeriesLocalization, $record instanceof ExpansionLocalization, $record instanceof ExpansionEdition => 'language_id',
            $record instanceof ExpansionRelease => 'channel',
            $record instanceof ExpansionRelation => 'target_expansion_id',
            default => 'key',
        };
        $message = match ($field) {
            'code' => 'Ce code de langue est déjà utilisé.',
            'language_id' => 'Une fiche existe déjà dans cette langue pour ce parent.',
            'channel' => 'Une sortie existe déjà sur ce marché pour ce canal.',
            'target_expansion_id' => 'Ce rapprochement existe déjà.',
            default => 'Cette clé est déjà utilisée dans ce périmètre.',
        };

        self::reject($field, $message);
    }

    public static function rejectRestrictedDelete(QueryException $exception, CatalogRecord|Game $record): never
    {
        if (! in_array($exception->getCode(), ['23001', '23503'], true)) {
            throw $exception;
        }

        $message = match (true) {
            $record instanceof Game => 'Ce jeu contient des catalogues. Supprimez-les explicitement avant le jeu.',
            $record instanceof Catalog => 'Ce catalogue contient des séries ou des extensions.',
            $record instanceof Series => 'Cette série est rattachée à des extensions. Détachez-les avant la suppression.',
            $record instanceof Expansion => 'Cette extension contient des éditions ou des rapprochements.',
            $record instanceof Language => 'Cette langue est utilisée par des fiches du catalogue.',
            $record instanceof Market => 'Ce marché est utilisé par des sorties.',
            default => 'Cette fiche est référencée et ne peut pas être supprimée.',
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
                'key' => $key, 'name' => $text, 'description' => $description, 'sort_order' => $order, 'is_visible' => $visibility,
            ],
            $record instanceof Series => [
                'catalog_id' => $fk('catalogs'), 'key' => $key, 'name' => $text,
                'type' => ['required', 'string', Rule::in(Series::TYPES)], 'sort_order' => $order, 'is_visible' => $visibility,
            ],
            $record instanceof SeriesLocalization => [
                'series_id' => $fk('series'), 'language_id' => $fk('languages'),
                'name' => $text, 'description' => $description, 'image' => $optionalText,
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
                'name' => $text, 'official_code' => $optionalText, 'image' => $optionalText,
                'status' => ['required', 'string', Rule::in(ExpansionEdition::STATUSES)], 'is_visible' => $visibility,
            ],
            $record instanceof ExpansionRelease => [
                'edition_id' => $fk('expansion_editions'), 'market_id' => $fk('markets'),
                'channel' => ['required', 'string', Rule::in(ExpansionRelease::CHANNELS)],
                'released_on' => ['nullable', 'string', 'date_format:Y-m-d'], 'announced_period' => $optionalText,
                'status' => ['required', 'string', Rule::in(ExpansionRelease::STATUSES)], 'source_url' => ['nullable', ...$url],
            ],
            $record instanceof ExpansionRelation => [
                'source_expansion_id' => $fk('expansions'), 'target_expansion_id' => $fk('expansions'),
                'type' => ['required', 'string', Rule::in(ExpansionRelation::TYPES)],
                'notes' => ['required', 'string'], 'source_url' => ['required', ...$url],
            ],
            default => throw new \LogicException('Type de fiche du catalogue inconnu.'),
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
            $record instanceof SeriesLocalization => ['series_id', 'language_id'],
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
                self::reject($field, 'Ce champ fait partie de l’identité de la fiche et ne peut plus être modifié.');
            }
        }
    }

    /** @param array<string, mixed> $data */
    private static function checkRelationships(CatalogRecord $record, array &$data): void
    {
        if ($record instanceof Expansion && ($data['series_id'] ?? null) !== null
            && ! Series::on($record->getConnectionName())->whereKey($data['series_id'])->where('catalog_id', $data['catalog_id'])->exists()) {
            self::reject('series_id', 'La série doit appartenir au catalogue de cette extension.');
        }

        if ($record instanceof ExpansionEdition && $record->exists && $data['status'] !== 'released'
            && $record->releases()->where('status', 'released')->exists()) {
            self::reject('status', 'Cette édition doit rester publiée tant qu’une de ses sorties est publiée.');
        }

        if ($record instanceof ExpansionRelation) {
            $source = (int) $data['source_expansion_id'];
            $target = (int) $data['target_expansion_id'];

            if ($source === $target) {
                self::reject('target_expansion_id', 'Une extension ne peut pas être rapprochée d’elle-même.');
            }

            $gameIds = Expansion::on($record->getConnectionName())
                ->join('catalogs', 'catalogs.id', '=', 'expansions.catalog_id')
                ->whereIn('expansions.id', [$source, $target])
                ->pluck('catalogs.game_id');

            if ($gameIds->count() !== 2 || $gameIds[0] !== $gameIds[1]) {
                self::reject('target_expansion_id', 'Les deux extensions doivent appartenir au même jeu.');
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
            $record instanceof SeriesLocalization => ['series_id', 'language_id'],
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
