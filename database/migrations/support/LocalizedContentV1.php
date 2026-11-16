<?php

namespace Database\Migrations\Support;

use App\Support\GameSlug;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Blueprint;
use RuntimeException;

/**
 * Frozen SQL/Query Builder conversion; never invokes models, observers or storage.
 *
 * @phpstan-type LocalizationAudit array{ok: bool, counts: array<string, int>, problems: list<string>}
 * @phpstan-type Row array<string, mixed>
 * @phpstan-type Snapshot array<string, list<Row>>
 */
final class LocalizedContentV1
{
    private const TABLES = ['languages', 'games', 'markets', 'catalogs', 'series', 'expansions', 'series_localizations', 'expansion_localizations', 'expansion_editions', 'expansion_releases', 'expansion_relations', 'catalog_example_bindings'];

    private const SOURCES = ['series_localizations', 'expansion_localizations'];

    private const JSON_FIELDS = [
        'games' => ['display_name', 'description'],
        'catalogs' => ['display_name', 'description'],
        'series' => ['display_name', 'description'],
        'expansions' => ['display_name', 'description'],
        'markets' => ['display_name'],
        'expansion_releases' => ['announced_period'],
    ];

    // Frozen from the content-language domain contract at V1, not interface activation.
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

    /** @return LocalizationAudit */
    public function audit(Connection $connection): array
    {
        $schema = $connection->getSchemaBuilder();
        $converted = $schema->hasColumn('games', 'display_name');
        $problems = [];
        $required = $converted ? array_diff(self::TABLES, self::SOURCES) : self::TABLES;
        foreach ($required as $table) {
            if (! $schema->hasTable($table)) {
                $problems[] = "Missing table: {$table}.";
            }
        }
        if ($converted) {
            foreach (self::JSON_FIELDS as $table => $fields) {
                foreach ($fields as $field) {
                    if (! $schema->hasColumn($table, $field)) {
                        $problems[] = "Incomplete conversion: {$table}.{$field}.";
                    }
                }
            }
            foreach (['series_images', 'localization_conversion_verifications'] as $table) {
                if (! $schema->hasTable($table)) {
                    $problems[] = "Missing table: {$table}.";
                }
            }
            foreach (['games' => 'reference_description', 'catalogs' => 'reference_description', 'expansion_releases' => 'reference_announced_period', 'catalog_example_bindings' => 'locale'] as $table => $column) {
                if (! $schema->hasColumn($table, $column)) {
                    $problems[] = "Incomplete conversion: {$table}.{$column}.";
                }
            }
        }
        if ($problems !== []) {
            return ['ok' => false, 'counts' => [], 'problems' => $problems];
        }

        $data = $this->snapshot($connection);
        $languages = [];
        foreach ($data['languages'] as $row) {
            $code = $this->canonicalCode($row['code']);
            if (! preg_match(self::LANGUAGE_TAG, $row['code']) || in_array($code, $languages, true)) {
                $problems[] = "Unknown or duplicate language: {$row['code']} (id {$row['id']}).";
            }
            $languages[$row['id']] = $code;
        }
        foreach ($data['games'] as $game) {
            if (in_array($game['slug'], GameSlug::reserved(), true)) {
                $problems[] = "Reserved translated route slug: {$game['slug']} (game {$game['id']}).";
            }
        }
        foreach (['catalogs' => 'default_language_id', 'expansion_editions' => 'language_id', 'series_localizations' => 'language_id', 'expansion_localizations' => 'language_id', 'series_images' => 'language_id'] as $table => $column) {
            foreach ($data[$table] ?? [] as $row) {
                if (! isset($languages[$row[$column]])) {
                    $problems[] = "Unknown language reference: {$table}.{$row['id']} ({$row[$column]}).";
                }
            }
        }
        if (! $converted) {
            foreach (self::SOURCES as $table) {
                $parent = $table === 'series_localizations' ? 'series' : 'expansion';
                $parents = array_column($data[$parent === 'series' ? 'series' : 'expansions'], 'id');
                foreach ($data[$table] as $row) {
                    if (trim($row['name']) === '') {
                        $problems[] = "Empty translated name: {$table}.{$row['id']}.";
                    }
                    if (! in_array($row[$parent.'_id'], $parents, true)) {
                        $problems[] = "Missing parent: {$table}.{$row['id']}.";
                    }
                }
            }
            $this->bindings($data, $languages, $problems);
        } else {
            foreach (self::JSON_FIELDS as $table => $fields) {
                foreach ($data[$table] as $row) {
                    foreach ($fields as $field) {
                        $translations = json_decode($row[$field]);
                        if (! $translations instanceof \stdClass) {
                            $problems[] = "Expected JSON object: {$table}.{$row['id']}.{$field}.";

                            continue;
                        }
                        foreach ((array) $translations as $locale => $value) {
                            if (! in_array($locale, $languages, true) || (! is_string($value) && $value !== null) || ($field === 'display_name' && (! is_string($value) || trim($value) === ''))) {
                                $problems[] = "Invalid translation: {$table}.{$row['id']}.{$field}.{$locale}.";
                            }
                        }
                    }
                }
            }
            foreach ($data['catalog_example_bindings'] as $binding) {
                if (str_ends_with($binding['record_type'], '-localization')) {
                    $problems[] = "Unconverted binding: {$binding['manifest_key']}.";
                }
                if ($binding['locale'] !== null && (! in_array($binding['locale'], $languages, true) || ! in_array($binding['record_type'], ['series', 'expansion'], true))) {
                    $problems[] = "Invalid binding locale: {$binding['manifest_key']}.";
                }
            }
            $proof = $connection->table('localization_conversion_verifications')->where('version', 1)->first();
            if ($proof === null || $proof->verified_at === null) {
                $problems[] = 'Missing verified V1 conversion proof.';
            }
            $sources = count(array_intersect(self::SOURCES, array_keys($data)));
            if ($sources !== 2 && ($sources !== 0 || $proof === null || $proof->finalized_at === null)) {
                $problems[] = 'Source tables removed without explicit verified finalization.';
            }
        }

        return ['ok' => $problems === [], 'counts' => $this->counts($data, $converted), 'problems' => $problems];
    }

    public function up(Connection $connection): void
    {
        $connection->transaction(function () use ($connection): void {
            if ($connection->getSchemaBuilder()->hasColumn('games', 'display_name')) {
                throw new RuntimeException('V1 conversion already applied; refusing to overwrite translations.');
            }
            // Serializes writers through the complete audit/copy/proof boundary.
            $connection->statement('LOCK TABLE '.implode(', ', self::TABLES).' IN ACCESS EXCLUSIVE MODE');
            $audit = $this->audit($connection);
            if (! $audit['ok']) {
                throw new RuntimeException(implode("\n", $audit['problems']));
            }
            $before = $this->snapshot($connection);
            $expected = $this->convertedSnapshot($before);
            $schema = $connection->getSchemaBuilder();
            foreach (['games' => 'description', 'catalogs' => 'description', 'expansion_releases' => 'announced_period'] as $table => $column) {
                $schema->table($table, fn (Blueprint $blueprint) => $blueprint->renameColumn($column, 'reference_'.$column));
            }
            foreach (self::JSON_FIELDS as $table => $fields) {
                $schema->table($table, function (Blueprint $blueprint) use ($fields): void {
                    foreach ($fields as $field) {
                        $blueprint->jsonb($field)->default('{}');
                    }
                });
            }
            $schema->create('series_images', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('series_id')->constrained('series')->cascadeOnDelete();
                $table->foreignId('language_id')->index()->constrained('languages')->restrictOnDelete();
                $table->string('image');
                $table->timestamps();
                $table->unique(['series_id', 'language_id']);
            });
            $schema->table('catalog_example_bindings', function (Blueprint $table): void {
                $table->dropUnique(['record_type', 'record_id']);
                $table->string('locale')->nullable();
            });
            foreach (['series', 'expansions'] as $table) {
                foreach ($expected[$table] as $row) {
                    $connection->table($table)->where('id', $row['id'])->update(array_intersect_key($row, array_flip(self::JSON_FIELDS[$table])));
                }
            }
            foreach ($expected['series_images'] as $row) {
                $connection->table('series_images')->insert($row);
            }
            $connection->statement("SELECT setval(pg_get_serial_sequence('series_images', 'id'), COALESCE((SELECT MAX(id) FROM series_images), 1), EXISTS(SELECT 1 FROM series_images))");
            foreach ($expected['catalog_example_bindings'] as $row) {
                $connection->table('catalog_example_bindings')->where('manifest_key', $row['manifest_key'])->update($row);
            }
            $connection->statement('CREATE UNIQUE INDEX catalog_example_bindings_record_locale_unique ON catalog_example_bindings (record_type, record_id, locale) NULLS NOT DISTINCT');
            $schema->create('localization_conversion_verifications', function (Blueprint $table): void {
                $table->unsignedInteger('version')->primary();
                $table->jsonb('source_hashes');
                $table->jsonb('target_hashes');
                $table->jsonb('counts');
                $table->timestampTz('verified_at')->nullable();
                $table->timestampTz('finalized_at')->nullable();
            });
            $sourceHashes = $this->hashes(array_intersect_key($before, array_flip(self::SOURCES)));
            $targetHashes = $this->hashes(array_diff_key($expected, array_flip(self::SOURCES)));
            $actual = $this->snapshot($connection);
            if ($targetHashes !== $this->hashes(array_diff_key($actual, array_flip(self::SOURCES))) || $sourceHashes !== $this->hashes(array_intersect_key($actual, array_flip(self::SOURCES)))) {
                throw new RuntimeException('Conversion equivalence failed; the entire transaction is rolled back.');
            }
            $connection->table('localization_conversion_verifications')->insert([
                'version' => 1,
                'source_hashes' => json_encode($sourceHashes, JSON_THROW_ON_ERROR),
                'target_hashes' => json_encode($targetHashes, JSON_THROW_ON_ERROR),
                'counts' => json_encode($this->counts($actual, true), JSON_THROW_ON_ERROR),
                'verified_at' => now(),
            ]);
            $connection->unprepared(<<<'SQL'
                CREATE FUNCTION localization_v1_read_only() RETURNS trigger LANGUAGE plpgsql AS $$
                BEGIN
                    RAISE EXCEPTION 'Historical localization sources are read-only after V1 conversion';
                END;
                $$
                SQL);
            foreach (self::SOURCES as $table) {
                $connection->statement("CREATE TRIGGER localization_v1_read_only BEFORE INSERT OR UPDATE OR DELETE OR TRUNCATE ON {$table} FOR EACH STATEMENT EXECUTE FUNCTION localization_v1_read_only()");
            }
        });
    }

    /**
     * Strict equivalence gate before reopening/finalizing. Read-only: cannot bless edits.
     * General operational audits must call audit(), which permits valid later translations.
     *
     * @return LocalizationAudit
     */
    public function verify(Connection $connection): array
    {
        $audit = $this->audit($connection);
        if (! $connection->getSchemaBuilder()->hasTable('localization_conversion_verifications')) {
            $audit['problems'][] = 'V1 conversion has not been verified.';
        } else {
            $proof = $connection->table('localization_conversion_verifications')->where('version', 1)->first();
            if ($proof !== null) {
                $data = $this->snapshot($connection);
                foreach (['source_hashes' => array_intersect_key($data, array_flip(self::SOURCES)), 'target_hashes' => array_diff_key($data, array_flip(self::SOURCES))] as $field => $snapshot) {
                    $expected = json_decode($proof->{$field}, true, flags: JSON_THROW_ON_ERROR);
                    $actual = $this->hashes($snapshot);
                    foreach (array_unique([...array_keys($expected), ...array_keys($actual)]) as $table) {
                        if (($expected[$table] ?? null) !== ($actual[$table] ?? null)) {
                            $audit['problems'][] = "Conversion proof mismatch: {$table}.";
                        }
                    }
                }
            }
        }
        $audit['ok'] = $audit['problems'] === [];

        return $audit;
    }

    /** @return Snapshot */
    private function snapshot(Connection $connection): array
    {
        $snapshot = [];
        foreach ([...self::TABLES, 'series_images'] as $table) {
            if ($connection->getSchemaBuilder()->hasTable($table)) {
                $snapshot[$table] = array_values($connection->table($table)->orderBy($table === 'catalog_example_bindings' ? 'manifest_key' : 'id')->get()->map(fn (object $row): array => (array) $row)->all());
            }
        }

        return $snapshot;
    }

    /** @param Snapshot $before
     * @return Snapshot
     */
    private function convertedSnapshot(array $before): array
    {
        $after = $before;
        $languages = [];
        foreach ($before['languages'] as $row) {
            $languages[$row['id']] = $this->canonicalCode($row['code']);
        }
        foreach (self::JSON_FIELDS as $table => $fields) {
            foreach ($after[$table] as &$row) {
                foreach ($fields as $field) {
                    if (array_key_exists($field, $row)) {
                        $row['reference_'.$field] = $row[$field];
                    }
                    $values = [];
                    if (in_array($table, ['series', 'expansions'], true)) {
                        $parent = $table === 'series' ? 'series' : 'expansion';
                        foreach ($before[$parent.'_localizations'] as $source) {
                            if ($source[$parent.'_id'] === $row['id']) {
                                $values[$languages[$source['language_id']]] = $source[$field === 'display_name' ? 'name' : 'description'];
                            }
                        }
                    }
                    $row[$field] = json_encode((object) $values, JSON_THROW_ON_ERROR);
                }
            }
            unset($row);
        }
        $after['series_images'] = [];
        foreach ($before['series_localizations'] as $row) {
            if ($row['image'] !== null) {
                $after['series_images'][] = array_intersect_key($row, array_flip(['id', 'series_id', 'language_id', 'image', 'created_at', 'updated_at']));
            }
        }
        $problems = [];
        $after['catalog_example_bindings'] = $this->bindings($before, $languages, $problems);
        if ($problems !== []) {
            throw new RuntimeException(implode("\n", $problems));
        }

        return $after;
    }

    /** @param Snapshot $data
     * @param  array<int, string>  $languages
     * @param  list<string>  $problems
     * @return list<Row>
     */
    private function bindings(array $data, array $languages, array &$problems): array
    {
        $result = [];
        $identities = [];
        $byKey = array_column($data['catalog_example_bindings'], null, 'manifest_key');
        foreach ($data['catalog_example_bindings'] as $binding) {
            $binding['locale'] = null;
            if (in_array($binding['record_type'], ['series-localization', 'expansion-localization'], true)) {
                $parent = explode('-', $binding['record_type'])[0];
                $sources = array_column($data[$parent.'_localizations'], null, 'id');
                $source = $sources[$binding['record_id']] ?? null;
                if ($source !== null) {
                    $binding['record_id'] = $source[$parent.'_id'];
                    $binding['locale'] = $languages[$source['language_id']] ?? null;
                } else {
                    // The immutable manifest key encodes the parent manifest key, NOT a mutable slug lookup.
                    $key = $binding['manifest_key'];
                    $lastSlash = strrpos($key, '/');
                    $parentKey = $lastSlash === false ? '' : $parent.substr($key, strlen($binding['record_type']), $lastSlash - strlen($binding['record_type']));
                    $parentBinding = $byKey[$parentKey] ?? null;
                    $locale = $lastSlash === false ? '' : $this->canonicalCode(substr($key, $lastSlash + 1));
                    $parentIds = array_column($data[$parent === 'series' ? 'series' : 'expansions'], 'id');
                    if ($parentBinding === null || $parentBinding['record_type'] !== $parent || ! in_array($parentBinding['record_id'], $parentIds, true) || ! in_array($locale, $languages, true)) {
                        $problems[] = "Unresolvable deleted localization binding: {$key} (parent binding {$parentKey}).";

                        continue;
                    }
                    $binding['record_id'] = $parentBinding['record_id'];
                    $binding['locale'] = $locale;
                }
                $binding['record_type'] = $parent;
            }
            $identity = json_encode([$binding['record_type'], $binding['record_id'], $binding['locale']], JSON_THROW_ON_ERROR);
            if (isset($identities[$identity])) {
                $problems[] = "Binding collision: {$binding['manifest_key']} and {$identities[$identity]}.";
            }
            $identities[$identity] = $binding['manifest_key'];
            $result[] = $binding;
        }

        return $result;
    }

    /** @param Snapshot $data
     * @return array<string, int>
     */
    private function counts(array $data, bool $converted): array
    {
        $counts = array_map(count(...), $data);
        $counts['translations'] = 0;
        $counts['description_nulls'] = 0;
        foreach (['series', 'expansion'] as $parent) {
            $rows = $data[$converted ? ($parent === 'series' ? 'series' : 'expansions') : $parent.'_localizations'];
            foreach ($rows as $row) {
                $values = $converted ? json_decode($row['description'], true) : [$row['description']];
                if (is_array($values)) {
                    $counts['translations'] += count($values);
                    $counts['description_nulls'] += count(array_filter($values, fn ($value): bool => $value === null));
                }
            }
        }
        if (! $converted) {
            $counts['series_images'] = count(array_filter($data['series_localizations'], fn (array $row): bool => $row['image'] !== null));
        }

        return $counts;
    }

    /** @param Snapshot $snapshot
     * @return array<string, string>
     */
    private function hashes(array $snapshot): array
    {
        $hashes = [];
        foreach ($snapshot as $table => $rows) {
            foreach ($rows as &$row) {
                foreach (self::JSON_FIELDS[$table] ?? [] as $field) {
                    // Only converted snapshots reach here for targets; source tables have no JSON fields.
                    if (isset($row['display_name']) || ($table === 'expansion_releases' && array_key_exists('reference_announced_period', $row))) {
                        $value = (array) json_decode($row[$field], flags: JSON_THROW_ON_ERROR);
                        ksort($value);
                        $row[$field] = $value;
                    }
                }
                ksort($row);
            }
            unset($row);
            $hashes[$table] = hash('sha256', json_encode($rows, JSON_THROW_ON_ERROR));
        }
        ksort($hashes);

        return $hashes;
    }

    private function canonicalCode(string $code): string
    {
        $parts = explode('-', strtolower(trim($code)));
        $index = 1;
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
}
