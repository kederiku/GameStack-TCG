<?php

namespace Tests\Concerns;

use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use LogicException;
use Throwable;

trait UsesIsolatedSchema
{
    protected string $isolatedSchema;

    private mixed $previousConfiguredSearchPath;

    private string $previousSessionSearchPath;

    protected function createIsolatedSchema(bool $registerCleanup = true): void
    {
        if (isset($this->isolatedSchema)) {
            throw new LogicException('This test already owns an isolated schema.');
        }

        $connection = $this->isolatedSchemaConnection();
        $this->assertIsolatedDatabase($connection);
        $schema = 'isolated_test_'.bin2hex(random_bytes(6));
        $this->previousConfiguredSearchPath = config('database.connections.pgsql.search_path');
        $this->previousSessionSearchPath = (string) $connection->scalar("SELECT current_setting('search_path')");
        $connection->statement('CREATE SCHEMA '.$schema);
        $this->isolatedSchema = $schema;

        try {
            config(['database.connections.pgsql.search_path' => $schema]);
            $connection->statement('SET search_path TO '.$schema);
        } catch (Throwable $error) {
            $this->dropIsolatedSchema();

            throw $error;
        }

        if ($registerCleanup) {
            $this->beforeApplicationDestroyed(fn () => $this->dropIsolatedSchema());
        }
    }

    protected function dropIsolatedSchema(): void
    {
        if (! isset($this->isolatedSchema)) {
            return;
        }
        if (! preg_match('/\Aisolated_test_[a-f0-9]{12}\z/', $this->isolatedSchema)) {
            throw new LogicException('Cleanup requires the owned isolated testing schema.');
        }

        $connection = $this->isolatedSchemaConnection();
        try {
            // Finish any open test transaction and its cleanup callbacks before dropping the schema.
            $connection->rollBack(0);
            $this->assertIsolatedDatabase($connection);
            $connection->statement('DROP SCHEMA '.$this->isolatedSchema.' CASCADE');
            unset($this->isolatedSchema);
        } finally {
            config(['database.connections.pgsql.search_path' => $this->previousConfiguredSearchPath]);
            $connection->statement("SELECT set_config('search_path', ?, false)", [$this->previousSessionSearchPath]);
        }
    }

    private function isolatedSchemaConnection(): Connection
    {
        $connection = DB::connection();
        if ($connection->getName() !== 'pgsql' || $connection->getDatabaseName() !== 'gamestackr_testing') {
            throw new LogicException('Isolated schemas require the dedicated gamestackr_testing PostgreSQL database.');
        }

        return $connection;
    }

    private function assertIsolatedDatabase(Connection $connection): void
    {
        if ($connection->scalar('SELECT current_database()', [], false) !== 'gamestackr_testing') {
            throw new LogicException('Isolated schemas require the dedicated gamestackr_testing PostgreSQL database.');
        }
    }
}
