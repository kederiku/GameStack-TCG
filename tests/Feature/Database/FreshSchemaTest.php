<?php

namespace Tests\Feature\Database;

use Database\Seeders\ContentLanguagesSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\Concerns\UsesIsolatedSchema;
use Tests\TestCase;

class FreshSchemaTest extends TestCase
{
    use UsesIsolatedSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createIsolatedSchema();
        $this->artisan('migrate')->assertSuccessful();
    }

    public function test_migrate_creates_only_the_final_schema_without_business_data(): void
    {
        foreach (['series_localizations', 'expansion_localizations', 'localization_conversion_verifications', 'catalog_example_bindings'] as $table) {
            $this->assertFalse(Schema::hasTable($table), "{$table} should not exist in a fresh installation.");
        }

        foreach (['users', 'languages', 'games', 'markets', 'catalogs', 'series', 'series_images', 'expansions', 'expansion_editions', 'expansion_releases', 'expansion_relations'] as $table) {
            $this->assertDatabaseEmpty($table);
        }

        $translatedColumns = [
            'games' => ['display_name', 'description'],
            'markets' => ['display_name'],
            'catalogs' => ['display_name', 'description'],
            'series' => ['display_name', 'description'],
            'expansions' => ['display_name', 'description'],
            'expansion_releases' => ['announced_period'],
        ];
        foreach ($translatedColumns as $table => $columns) {
            foreach ($columns as $column) {
                $this->assertSame('jsonb', Schema::getColumnType($table, $column), "{$table}.{$column}");
            }
        }

        $this->assertTrue(Schema::hasColumns('users', [
            'id', 'name', 'email', 'email_verified_at', 'password', 'remember_token',
            'two_factor_secret', 'two_factor_recovery_codes', 'two_factor_confirmed_at',
            'is_admin', 'preferred_locale', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('passkeys', [
            'id', 'user_id', 'name', 'credential_id', 'credential', 'last_used_at', 'created_at', 'updated_at',
        ]));
        $this->assertTrue(Schema::hasColumns('password_reset_tokens', ['email', 'token', 'created_at']));
        $this->assertTrue(Schema::hasColumns('sessions', ['id', 'user_id', 'ip_address', 'user_agent', 'payload', 'last_activity']));
        foreach (['cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs'] as $table) {
            $this->assertTrue(Schema::hasTable($table), "{$table} should exist in a fresh installation.");
        }
    }

    public function test_language_sync_only_adds_active_interface_locales(): void
    {
        $this->assertDatabaseEmpty('languages');

        $this->artisan('db:seed', ['--class' => ContentLanguagesSeeder::class])->assertSuccessful();

        $languages = DB::table('languages')->orderBy('code')->get();
        $this->assertSame(['en' => 'English', 'fr' => 'Français'], $languages->pluck('name', 'code')->all());

        $this->artisan('localization:sync-languages')->assertSuccessful();

        $this->assertEquals($languages, DB::table('languages')->orderBy('code')->get());
        $this->assertDatabaseEmpty('users');
        $this->assertDatabaseEmpty('games');
    }

    public function test_migration_batch_can_be_rolled_back_and_reapplied(): void
    {
        $this->artisan('migrate:rollback')->assertSuccessful();

        $this->assertSame(['migrations'], Schema::getTableListing($this->isolatedSchema, schemaQualified: false));

        $this->artisan('migrate')->assertSuccessful();

        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('series_images'));
        $this->assertDatabaseEmpty('languages');
    }
}
