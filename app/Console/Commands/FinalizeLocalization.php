<?php

namespace App\Console\Commands;

use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinalizeLocalization extends Command
{
    protected $signature = 'localization:finalize {--dry-run : Verify without removing source tables}';

    protected $description = 'Verify the V1 conversion proof and retire the historical catalog tables';

    private const RECEIPTS = 'localization_conversion_verifications';

    private const SOURCES = ['series_localizations', 'expansion_localizations'];

    // Every input covered by the V1 receipt must remain stable until commit.
    private const TARGETS = ['languages', 'games', 'markets', 'catalogs', 'series', 'expansions', 'expansion_editions', 'expansion_releases', 'expansion_relations', 'catalog_example_bindings', 'series_images'];

    // V1 adds or renames these columns. Row digests cannot attest their shape on empty tables.
    private const CONVERSION_COLUMNS = [
        self::RECEIPTS => ['version' => 'int4', 'source_hashes' => 'jsonb', 'target_hashes' => 'jsonb', 'counts' => 'jsonb', 'verified_at' => 'timestamptz', 'finalized_at' => 'timestamptz'],
        'games' => ['display_name' => 'jsonb', 'description' => 'jsonb', 'reference_description' => 'text'],
        'catalogs' => ['display_name' => 'jsonb', 'description' => 'jsonb', 'reference_description' => 'text'],
        'series' => ['display_name' => 'jsonb', 'description' => 'jsonb'],
        'expansions' => ['display_name' => 'jsonb', 'description' => 'jsonb'],
        'markets' => ['display_name' => 'jsonb'],
        'expansion_releases' => ['announced_period' => 'jsonb', 'reference_announced_period' => 'varchar'],
        'catalog_example_bindings' => ['locale' => 'varchar'],
        'series_images' => ['id' => 'int8', 'series_id' => 'int8', 'language_id' => 'int8', 'image' => 'varchar', 'created_at' => 'timestamp', 'updated_at' => 'timestamp'],
    ];

    public static function isFinalized(Connection $db): bool
    {
        $schema = $db->getSchemaBuilder();

        return $schema->hasTable(self::RECEIPTS)
            && ! $schema->hasTable(self::SOURCES[0]) && ! $schema->hasTable(self::SOURCES[1])
            && self::conversionSchemaError($db) === null
            && $db->table(self::RECEIPTS)->where('version', 1)->whereNotNull('verified_at')->whereNotNull('finalized_at')->exists();
    }

    public function handle(MaintenanceMode $maintenance): int
    {
        require_once database_path('migrations/support/LocalizedContentV1.php');
        $db = DB::connection();
        $dryRun = (bool) $this->option('dry-run');

        return $db->transaction(function () use ($db, $maintenance, $dryRun): int {
            $schema = $db->getSchemaBuilder();
            if (! $schema->hasTable(self::RECEIPTS)) {
                $this->error('Missing V1 conversion receipt. Run the migration under maintenance first.');

                return self::FAILURE;
            }
            // Serialize finalizers, including one arriving just after another committed.
            $db->statement('LOCK TABLE '.self::RECEIPTS.' IN '.($dryRun ? 'SHARE' : 'EXCLUSIVE').' MODE');
            if (self::isFinalized($db)) {
                $this->info('V1 conversion already finalized.');

                return self::SUCCESS;
            }
            $receipt = $db->table(self::RECEIPTS)->where('version', 1)->first();
            if ($receipt === null || $receipt->verified_at === null) {
                $this->error('Missing verified V1 conversion receipt.');

                return self::FAILURE;
            }
            if ($receipt->finalized_at !== null) {
                $this->error('Inconsistent finalized receipt. Check the surviving sources and the verified timestamp.');

                return self::FAILURE;
            }
            if (! $dryRun && ! $maintenance->active()) {
                $this->error('Maintenance mode is required to finalize localization.');

                return self::FAILURE;
            }
            foreach ([...self::TARGETS, ...self::SOURCES] as $table) {
                if (! $schema->hasTable($table)) {
                    $this->error("Incomplete conversion: missing table {$table}.");

                    return self::FAILURE;
                }
            }
            $db->statement('LOCK TABLE '.implode(', ', [...self::TARGETS, ...self::SOURCES]).' IN SHARE MODE');
            $schemaError = self::conversionSchemaError($db);
            if ($schemaError !== null) {
                $this->error($schemaError);

                return self::FAILURE;
            }
            $proof = (new LocalizedContentV1)->verify($db);
            if (! $proof['ok']) {
                foreach ($proof['problems'] as $problem) {
                    $this->error($problem);
                }

                return self::FAILURE;
            }
            if (! self::countsMatch($receipt->counts, $proof['counts'])) {
                $this->error('V1 receipt counts differ from the verified conversion.');

                return self::FAILURE;
            }
            if ($dryRun) {
                $this->info('V1 source and target digests verified. No changes made.');

                return self::SUCCESS;
            }

            // RESTRICT (the PostgreSQL default) also refuses remaining external dependencies.
            $db->statement('DROP TABLE '.implode(', ', self::SOURCES));
            $db->statement('DROP FUNCTION localization_v1_read_only()');
            $marked = $db->table(self::RECEIPTS)->where('version', 1)
                ->whereNotNull('verified_at')->whereNull('finalized_at')->update(['finalized_at' => now()]);
            if ($marked !== 1) {
                throw new RuntimeException('V1 receipt update must mark exactly one verified conversion.');
            }
            $this->info('V1 conversion finalized. Digest receipt retained.');

            return self::SUCCESS;
        });
    }

    private static function conversionSchemaError(Connection $db): ?string
    {
        $schema = $db->getSchemaBuilder();
        foreach (self::TARGETS as $table) {
            if (! $schema->hasTable($table)) {
                return "Incomplete V1 target schema: missing table {$table}.";
            }
        }
        foreach (self::CONVERSION_COLUMNS as $table => $columns) {
            $actual = array_column($schema->getColumns($table), 'type_name', 'name');
            foreach ($columns as $column => $type) {
                if (($actual[$column] ?? null) !== $type) {
                    return "Incomplete V1 target schema: {$table}.{$column} requires PostgreSQL type {$type}.";
                }
            }
        }

        $foreignKeys = array_column($db->select(<<<'SQL'
            SELECT pg_get_constraintdef(oid) AS definition
            FROM pg_constraint
            WHERE conrelid = 'series_images'::regclass AND contype = 'f' AND convalidated AND conenforced
            SQL), 'definition');
        foreach (['FOREIGN KEY (series_id) REFERENCES series(id) ON DELETE CASCADE', 'FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE RESTRICT'] as $definition) {
            if (! in_array($definition, $foreignKeys, true)) {
                return "Incomplete V1 target schema: series_images requires {$definition}.";
            }
        }
        // PostgreSQL's index catalog includes NULLS NOT DISTINCT, predicates and validity.
        foreach ([
            ['series_images', 'id', true, false],
            ['series_images', 'series_id,language_id', false, false],
            ['catalog_example_bindings', 'record_type,record_id,locale', false, true],
            [self::RECEIPTS, 'version', true, false],
        ] as [$table, $columns, $primary, $nullsEqual]) {
            $found = $db->scalar(<<<'SQL'
                SELECT EXISTS (
                    SELECT FROM pg_index
                    WHERE indrelid = to_regclass(?) AND indisunique AND indisvalid AND indisready AND indimmediate
                        AND indpred IS NULL AND indexprs IS NULL AND indisprimary = ? AND indnullsnotdistinct = ?
                        AND ARRAY(SELECT pg_get_indexdef(indexrelid, n, true) FROM generate_series(1, indnkeyatts) AS n)
                            = string_to_array(?, ',')
                )
                SQL, [$table, $primary, $nullsEqual, $columns]);
            if (! $found) {
                return "Incomplete V1 target schema: missing required uniqueness on {$table} ({$columns}).";
            }
        }

        return null;
    }

    /** @param array<string, int> $actual */
    private static function countsMatch(string $json, array $actual): bool
    {
        $expected = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
        if (! is_array($expected)) {
            return false;
        }
        ksort($expected);
        ksort($actual);

        return $expected === $actual;
    }
}
