<?php

namespace Tests\Concerns;

use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use LogicException;

trait UsesLocalizedSchema
{
    protected string $localizedSchema;

    private mixed $previousSearchPath;

    protected function createLocalizedSchema(bool $registerCleanup = true): void
    {
        $connection = DB::connection();
        $this->assertSame('gamestackr_testing', $connection->getDatabaseName());
        $this->localizedSchema = 'localized_runtime_'.bin2hex(random_bytes(6));
        $this->previousSearchPath = config('database.connections.pgsql.search_path');
        $connection->statement('CREATE SCHEMA '.$this->localizedSchema);
        if ($registerCleanup) {
            $this->beforeApplicationDestroyed(fn () => $this->dropLocalizedSchema());
        }
        config(['database.connections.pgsql.search_path' => $this->localizedSchema]);
        $connection->statement('SET search_path TO '.$this->localizedSchema);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->app[Kernel::class]->setArtisan(null);
        require_once database_path('migrations/support/LocalizedContentV1.php');
        $verification = (new LocalizedContentV1)->verify($connection);
        $this->assertTrue($verification['ok'], implode("\n", $verification['problems']));
        // Test-only final schema. Task 11 owns production finalization and must
        // replace this simulation with its verified migration before cutover.
        $connection->statement('DROP TABLE series_localizations, expansion_localizations');
        $connection->statement('DROP FUNCTION localization_v1_read_only()');
        $connection->table('localization_conversion_verifications')->update(['finalized_at' => now()]);
    }

    protected function dropLocalizedSchema(): void
    {
        $connection = DB::connection();
        if ($connection->getDatabaseName() !== 'gamestackr_testing'
            || ! preg_match('/\Alocalized_runtime_[a-f0-9]{12}\z/', $this->localizedSchema)) {
            throw new LogicException('Cleanup requires the owned isolated testing schema.');
        }
        // A failed test can leave an explicit transaction open; never drop while
        // that transaction still owns test data or filesystem cleanup callbacks.
        $connection->rollBack(0);
        $connection->statement('SET search_path TO public');
        config(['database.connections.pgsql.search_path' => $this->previousSearchPath]);
        $connection->statement('DROP SCHEMA '.$this->localizedSchema.' CASCADE');
        RefreshDatabaseState::$migrated = false;
    }
}
