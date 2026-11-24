<?php

namespace Tests\Concerns;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Foundation\ArrayMaintenanceMode;
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
        $maintenance = new ArrayMaintenanceMode;
        $this->app->instance(MaintenanceMode::class, $maintenance);
        $maintenance->activate([]);
        $this->artisan('localization:finalize')->assertSuccessful();
        $maintenance->deactivate();
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
