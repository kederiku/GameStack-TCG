<?php

namespace App\Console\Commands;

use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Console\Command;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;

class FinalizeLocalization extends Command
{
    protected $signature = 'localization:finalize {--dry-run : Verify without removing source tables}';

    protected $description = 'Verify the V1 conversion proof and retire the historical catalog tables';

    private const RECEIPTS = 'localization_conversion_verifications';

    private const SOURCES = ['series_localizations', 'expansion_localizations'];

    // Every input covered by the V1 receipt must remain stable until commit.
    private const TARGETS = ['languages', 'games', 'markets', 'catalogs', 'series', 'expansions', 'expansion_editions', 'expansion_releases', 'expansion_relations', 'catalog_example_bindings', 'series_images'];

    public static function isFinalized(Connection $db): bool
    {
        $schema = $db->getSchemaBuilder();

        return $schema->hasTable(self::RECEIPTS)
            && $db->table(self::RECEIPTS)->where('version', 1)->whereNotNull('verified_at')->whereNotNull('finalized_at')->exists()
            && ! $schema->hasTable(self::SOURCES[0]) && ! $schema->hasTable(self::SOURCES[1]);
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
            if ($db->table(self::RECEIPTS)->where('version', 1)->whereNotNull('finalized_at')->exists()) {
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
            $proof = (new LocalizedContentV1)->verify($db);
            if (! $proof['ok']) {
                foreach ($proof['problems'] as $problem) {
                    $this->error($problem);
                }

                return self::FAILURE;
            }
            if ($dryRun) {
                $this->info('V1 source and target digests verified. No changes made.');

                return self::SUCCESS;
            }

            // RESTRICT (the PostgreSQL default) also refuses remaining external dependencies.
            $db->statement('DROP TABLE '.implode(', ', self::SOURCES));
            $db->statement('DROP FUNCTION localization_v1_read_only()');
            $db->table(self::RECEIPTS)->where('version', 1)->update(['finalized_at' => now()]);
            $this->info('V1 conversion finalized. Digest receipt retained.');

            return self::SUCCESS;
        });
    }
}
