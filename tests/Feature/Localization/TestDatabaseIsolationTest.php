<?php

namespace Tests\Feature\Localization;

use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/** Also run last in the mixed migration/transaction test command. */
class TestDatabaseIsolationTest extends TestCase
{
    public function test_owned_schema_search_path_and_refresh_state_do_not_leak(): void
    {
        $this->assertSame('public', config('database.connections.pgsql.search_path'));
        $this->assertSame('public', DB::selectOne('SELECT current_schema() AS name')->name);
        $this->assertFalse(RefreshDatabaseState::$migrated);
        $this->assertSame([], DB::select("SELECT nspname FROM pg_namespace WHERE nspname ~ '^localized_runtime_[a-f0-9]{12}$'"));
    }
}
