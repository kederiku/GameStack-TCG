<?php

namespace Tests\Feature\Localization;

use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Events\MigrationsEnded;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\ArrayMaintenanceMode;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
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
