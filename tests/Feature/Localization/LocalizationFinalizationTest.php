<?php

namespace Tests\Feature\Localization;

use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\ArrayMaintenanceMode;
use Illuminate\Foundation\MaintenanceModeManager;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class LocalizationFinalizationTest extends TestCase
{
    private MaintenanceMode $maintenance;

    protected function setUp(): void
    {
        parent::setUp();
        $connection = DB::connection();
        $this->assertSame('gamestackr_testing', $connection->getDatabaseName());
        $schema = 'localization_install_'.bin2hex(random_bytes(6));
        $connection->statement('CREATE SCHEMA '.$schema);
        $connection->statement('SET search_path TO '.$schema);
        config(['database.connections.pgsql.search_path' => $schema]);
        $this->beforeApplicationDestroyed(function () use ($connection, $schema): void {
            $connection->statement('SET search_path TO public');
            $connection->statement('DROP SCHEMA '.$schema.' CASCADE');
        });
        $this->maintenance = new ArrayMaintenanceMode;
        $this->app->instance(MaintenanceMode::class, $this->maintenance);
    }

    public function test_dry_run_preserves_proof_and_finalization_is_atomic_and_idempotent_after_edits(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        Storage::shouldReceive('disk')->never();
        $this->assertArrayHasKey('localization:finalize', Artisan::all());
        $receipt = DB::table('localization_conversion_verifications');
        $before = (array) $receipt->first();
        $this->artisan('localization:finalize')->assertFailed();
        $this->artisan('localization:finalize', ['--dry-run' => true])->assertSuccessful();
        $this->assertFalse($this->maintenance->active());
        $this->maintenance->activate([]);
        $this->assertSame($before, (array) $receipt->first());
        $this->assertTrue(Schema::hasTable('series_localizations'));
        $this->artisan('localization:finalize')->assertSuccessful();
        $after = (array) $receipt->first();
        $this->assertNotNull($after['finalized_at']);
        $this->assertSame([...$before, 'finalized_at' => $after['finalized_at']], $after);
        $this->assertFalse(Schema::hasTable('series_localizations'));
        $this->assertFalse(Schema::hasTable('expansion_localizations'));
        $this->assertTrue(Schema::hasTable('series_images'));
        DB::table('languages')->insert(['code' => 'ja', 'name' => '日本語']);
        $this->artisan('localization:audit')->assertSuccessful();
        $this->artisan('localization:finalize')->assertSuccessful();
        $this->assertSame($after, (array) $receipt->first());
    }

    public function test_divergence_and_incomplete_proof_cannot_destroy_sources(): void
    {
        $this->assertArrayHasKey('localization:finalize', Artisan::all());
        $this->maintenance->activate([]);
        $this->artisan('localization:finalize')->assertFailed();
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::table('languages')->insert(['code' => 'ja', 'name' => '日本語']);
        foreach ([true, false] as $dry) {
            $this->artisan('localization:finalize', ['--dry-run' => $dry])->assertFailed();
        }
        $this->assertNull(DB::table('localization_conversion_verifications')->value('finalized_at'));
        DB::table('localization_conversion_verifications')->delete();
        $this->artisan('localization:finalize')->assertFailed();
        $this->assertTrue(Schema::hasTable('expansion_localizations'));
        $this->assertTrue(Schema::hasTable('series_localizations'));
    }

    #[DataProvider('partialRestorations')]
    public function test_partial_restoration_or_unverified_receipt_preserves_sources(string $alteration, string $receiptState): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        // Empty target tables have the same row digests even when a column is missing.
        DB::statement($alteration);
        $receipts = DB::table('localization_conversion_verifications');
        if ($receiptState === 'unverified') {
            $receipts->update(['verified_at' => null]);
        } elseif ($receiptState === 'missing') {
            $receipts->delete();
        }
        $original = $receipts->first();
        $this->maintenance->activate([]);
        foreach ([true, false] as $dryRun) {
            $this->artisan('localization:finalize', ['--dry-run' => $dryRun])->assertFailed();
            $this->assertTrue(Schema::hasTable('series_localizations'));
            $this->assertTrue(Schema::hasTable('expansion_localizations'));
            $this->assertEquals($original, $receipts->first());
        }
    }

    public static function partialRestorations(): array
    {
        return [
            'partial target schema' => ['ALTER TABLE games DROP COLUMN display_name', 'verified'],
            'missing image target' => ['ALTER TABLE series_images DROP COLUMN image', 'verified'],
            'wrong translation type' => ['ALTER TABLE markets ALTER COLUMN display_name DROP DEFAULT, ALTER COLUMN display_name TYPE text USING display_name::text', 'verified'],
            'absent verification' => ['ALTER TABLE games DROP COLUMN display_name', 'unverified'],
            'absent receipt' => ['ALTER TABLE games DROP COLUMN display_name', 'missing'],
        ];
    }

    #[DataProvider('incompleteReceipts')]
    public function test_incomplete_receipt_counts_cannot_be_finalized(string $countsExpression): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $receipt = DB::table('localization_conversion_verifications');
        $receipt->update(['counts' => DB::raw($countsExpression)]);
        $original = (array) $receipt->first();
        $this->maintenance->activate([]);
        foreach ([true, false] as $dryRun) {
            $this->artisan('localization:finalize', ['--dry-run' => $dryRun])->assertFailed();
            $this->assertTrue(Schema::hasTable('series_localizations'));
            $this->assertTrue(Schema::hasTable('expansion_localizations'));
            $this->assertSame($original, (array) $receipt->first());
        }
    }

    public static function incompleteReceipts(): array
    {
        return [
            'missing counts' => ["'{}'::jsonb"],
            'changed count' => ["jsonb_set(counts, '{languages}', '0'::jsonb)"],
            'wrong count type' => ["jsonb_set(counts, '{languages}', to_jsonb('2'::text))"],
        ];
    }

    public function test_suppressed_receipt_update_aborts_both_source_drops(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::unprepared('CREATE FUNCTION skip_receipt() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RETURN NULL; END; $$');
        DB::statement('CREATE TRIGGER skip_receipt BEFORE UPDATE ON localization_conversion_verifications FOR EACH ROW EXECUTE FUNCTION skip_receipt()');
        $this->maintenance->activate([]);
        $error = null;
        try {
            $this->artisan('localization:finalize')->run();
        } catch (RuntimeException $exception) {
            $error = $exception->getMessage();
        }
        $this->assertSame('V1 receipt update must mark exactly one verified conversion.', $error);
        $this->assertTrue(Schema::hasTable('series_localizations'));
        $this->assertTrue(Schema::hasTable('expansion_localizations'));
        $this->assertNull(DB::table('localization_conversion_verifications')->value('finalized_at'));
    }

    public function test_fresh_install_reports_missing_database_maintenance_cache_before_writing(): void
    {
        config(['app.maintenance.driver' => 'cache', 'app.maintenance.store' => 'database']);
        $this->app->instance(MaintenanceMode::class, app(MaintenanceModeManager::class)->driver('cache'));
        $this->artisan('localization:install')
            ->expectsOutputToContain('Missing maintenance cache table [cache]')
            ->assertFailed();
        $this->assertFalse(Schema::hasTable('migrations'));
        $this->assertFalse(Schema::hasTable('cache'));
        // Exercise the documented bootstrap without touching Laravel's shared down file.
        $this->artisan('migrate', ['--path' => 'database/migrations/0001_01_01_000001_create_cache_table.php', '--force' => true])->assertSuccessful();
        $maintenance = app(MaintenanceMode::class);
        $maintenance->activate(['retry' => 180]);
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->artisan('localization:finalize', ['--dry-run' => true])->assertSuccessful();
        $this->artisan('localization:finalize')->assertSuccessful();
        $this->artisan('localization:install')->assertSuccessful();
        $this->assertTrue($maintenance->active());
        $this->assertSame(['retry' => 180], $maintenance->data());
    }

    public function test_dependent_trigger_rolls_back_both_drops_without_marking_the_receipt(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::statement('CREATE TABLE dependent_table (id int)');
        DB::statement('CREATE TRIGGER dependent BEFORE INSERT ON dependent_table FOR EACH STATEMENT EXECUTE FUNCTION localization_v1_read_only()');
        $this->maintenance->activate([]);
        try {
            $this->artisan('localization:finalize')->run();
            $this->fail('The external trigger must prevent finalization.');
        } catch (QueryException $exception) {
            $this->assertSame('2BP01', $exception->getCode());
        }
        $this->assertTrue(Schema::hasTable('series_localizations'));
        $this->assertTrue(Schema::hasTable('expansion_localizations'));
        $this->assertNull(DB::table('localization_conversion_verifications')->value('finalized_at'));
    }

    public function test_inconsistent_finalized_receipt_with_surviving_sources_is_refused(): void
    {
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        DB::table('localization_conversion_verifications')->update(['finalized_at' => now()]);
        $this->maintenance->activate([]);
        $this->artisan('localization:finalize')->assertFailed();
        $this->assertTrue(Schema::hasTable('series_localizations'));
    }

    public function test_install_finalizes_only_a_new_schema_and_preserves_maintenance(): void
    {
        $this->assertArrayHasKey('localization:install', Artisan::all());
        $this->maintenance->activate(['retry' => 180]);
        $this->artisan('localization:install')->assertSuccessful();
        $this->assertSame(['retry' => 180], $this->maintenance->data());
        $this->assertTrue($this->maintenance->active());
        $this->assertFalse(Schema::hasTable('series_localizations'));
        $this->assertNotNull(DB::table('localization_conversion_verifications')->value('finalized_at'));
        $this->maintenance->deactivate();
        $this->artisan('localization:install')->assertSuccessful();
        $this->assertFalse($this->maintenance->active());
    }

    public function test_install_stays_down_if_verification_fails(): void
    {
        $this->assertArrayHasKey('localization:install', Artisan::all());
        Event::listen(MigrationsEnded::class, function (): void {
            DB::table('languages')->insert(['code' => 'ja', 'name' => '日本語']);
        });
        $this->artisan('localization:install')->assertFailed();
        $this->assertTrue($this->maintenance->active());
        $this->assertTrue(Schema::hasTable('series_localizations'));
        $this->assertNull(DB::table('localization_conversion_verifications')->value('finalized_at'));
    }

    public function test_new_install_reopens_on_success(): void
    {
        $this->assertArrayHasKey('localization:install', Artisan::all());
        $this->artisan('localization:install')->assertSuccessful();
        $this->assertFalse($this->maintenance->active());
        $this->assertFalse(Schema::hasTable('series_localizations'));
    }

    public function test_install_refuses_existing_unfinalized_schema_without_changes(): void
    {
        $this->assertArrayHasKey('localization:install', Artisan::all());
        DB::statement('CREATE TABLE existing_table (id integer)');
        $this->artisan('localization:install')->assertFailed();
        $this->assertFalse(Schema::hasTable('migrations'));
        $this->assertFalse($this->maintenance->active());
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->artisan('localization:install')->assertFailed();
        $this->assertTrue(Schema::hasTable('series_localizations'));
    }
}
