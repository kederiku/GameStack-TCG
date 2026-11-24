<?php

namespace Tests\Feature\Localization;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\UsesIsolatedSchema;
use Tests\TestCase;

class TestDatabaseIsolationTest extends TestCase
{
    use UsesIsolatedSchema;

    public function test_owned_schema_cleanup_restores_search_path_and_preserves_refresh_state(): void
    {
        $refreshState = RefreshDatabaseState::$migrated;
        $configuredSearchPath = config('database.connections.pgsql.search_path');
        $sessionSearchPath = DB::scalar("SELECT current_setting('search_path')");

        $this->createIsolatedSchema(registerCleanup: false);
        $schema = $this->isolatedSchema;
        try {
            $this->assertSame($schema, config('database.connections.pgsql.search_path'));
            $this->assertSame($schema, DB::scalar('SELECT current_schema()'));
            DB::beginTransaction();
        } finally {
            $this->dropIsolatedSchema();
        }

        $this->assertSame(0, DB::transactionLevel());
        $this->assertSame($configuredSearchPath, config('database.connections.pgsql.search_path'));
        $this->assertSame($sessionSearchPath, DB::scalar("SELECT current_setting('search_path')"));
        $this->assertSame($refreshState, RefreshDatabaseState::$migrated);
        $this->assertFalse(DB::table('pg_namespace')->where('nspname', $schema)->exists());
    }
}
