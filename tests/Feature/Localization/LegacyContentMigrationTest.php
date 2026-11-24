<?php

namespace Tests\Feature\Localization;

use Database\Migrations\Support\LocalizedContentV1;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Database\Connection;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\ArrayMaintenanceMode;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LegacyContentMigrationTest extends TestCase
{
    private Connection $connection;

    private string $schema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->connection = DB::connection();
        $this->assertSame('gamestackr_testing', $this->connection->getDatabaseName());
        $this->schema = 'localization_test_'.bin2hex(random_bytes(6));
        $this->connection->statement('CREATE SCHEMA '.$this->schema);
        $this->connection->statement('SET search_path TO '.$this->schema);
        foreach (['2026_11_14_120000_create_games_table', '2026_11_15_120000_add_details_to_games_table', '2026_11_15_130000_add_visibility_to_games_table', '2026_11_15_140000_create_tcg_catalog_tables', '2026_11_15_150000_create_catalog_example_bindings_table'] as $file) {
            (require database_path('migrations/'.$file.'.php'))->up();
        }
        foreach (require base_path('tests/Fixtures/Localization/legacy-catalog.php') as $table => $rows) {
            if ($rows !== []) {
                $this->connection->table($table)->insert($rows);
            }
        }
        Storage::shouldReceive('disk')->never();
    }

    protected function tearDown(): void
    {
        try {
            $this->connection->statement('SET search_path TO public');
            $this->connection->statement('DROP SCHEMA '.$this->schema.' CASCADE');
        } finally {
            parent::tearDown();
        }
    }

    private function converter(): object
    {
        $path = database_path('migrations/support/LocalizedContentV1.php');
        $this->assertFileExists($path, 'The versioned conversion contract must exist.');
        require_once $path;

        return new LocalizedContentV1;
    }

    public function test_legacy_null_and_reference_are_preserved(): void
    {
        $this->connection->table('expansion_localizations')->whereIn('id', [62, 63])->delete();
        $before = $this->snapshot();
        $converter = $this->converter();
        $audit = $converter->audit($this->connection);
        $this->assertTrue($audit['ok'], implode("\n", $audit['problems']));
        $converter->up($this->connection);
        $row = $this->connection->table('expansions')->where('id', 42)->first();
        $this->assertSame('参考名', $row->name);
        // JSONB object key order is not significant; values and null presence are.
        $description = json_decode($row->description, true);
        ksort($description);
        $this->assertSame(['en' => 'English description', 'fr' => null], $description);
        $this->assertArrayHasKey('fr', $description);
        $this->assertNull($description['fr']);
        foreach ($before as $table => $rows) {
            if ($table === 'catalog_example_bindings') {
                continue;
            }
            foreach ($rows as $original) {
                $current = (array) $this->connection->table($table)->where('id', $original['id'])->first();
                foreach ($original as $field => $value) {
                    $target = ($field === 'description' && in_array($table, ['games', 'catalogs'], true)) || ($field === 'announced_period' && $table === 'expansion_releases') ? 'reference_'.$field : $field;
                    $this->assertSame($value, $current[$target], "$table.{$original['id']}.$field");
                }
            }
        }
        $this->assertSame('{}', $this->connection->table('games')->value('display_name'));
        $this->assertSame('{}', $this->connection->table('games')->value('description'));
        $this->assertSame('{}', $this->connection->table('expansion_releases')->value('announced_period'));
        $this->assertSame(['catalog/shared.avif', 'catalog/shared.avif'], $this->connection->table('series_images')->orderBy('language_id')->pluck('image')->all());
        $this->assertSame([1, 2], $this->connection->table('series_images')->orderBy('language_id')->pluck('language_id')->all());
        $verification = $converter->verify($this->connection);
        $this->assertTrue($verification['ok'], implode("\n", $verification['problems']));
        $this->assertSame(2, $verification['counts']['series_images']);
        $this->assertSame(2, $verification['counts']['description_nulls']);
        $proof = $this->connection->table('localization_conversion_verifications')->where('version', 1)->first();
        $this->assertNotNull($proof->verified_at);
        $this->assertNull($proof->finalized_at);
    }

    public function test_canonical_content_codes_preserve_language_rows_and_inactive_locales(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        $names = json_decode($this->connection->table('expansions')->value('display_name'), true);
        $this->assertSame('Nom FR', $names['fr']);
        $this->assertSame('繁體', $names['zh-Hant']);
        $this->assertSame('Português', $names['pt-BR']);
        $this->assertSame(['FR', 'en', 'ja', 'zh-hant', 'pt-br'], $this->connection->table('languages')->orderBy('id')->pluck('code')->all());
    }

    public function test_bindings_use_parent_and_locale_even_for_deleted_localization_after_rename(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        foreach (['fr', 'en', 'ja'] as $locale) {
            $binding = $this->connection->table('catalog_example_bindings')->where('manifest_key', 'expansion-localization:old-game/international/set/'.$locale)->first();
            $this->assertSame('expansion', $binding->record_type);
            $this->assertSame(42, $binding->record_id);
            $this->assertSame($locale, $binding->locale);
        }
        $this->assertSame(8, $this->connection->table('catalog_example_bindings')->count());
        $this->expectException(QueryException::class);
        $this->connection->table('catalog_example_bindings')->insert(['manifest_key' => 'duplicate', 'record_type' => 'expansion', 'record_id' => 42, 'locale' => null]);
    }

    public function test_audit_blocks_translated_slug_collision_without_writing(): void
    {
        $this->connection->table('games')->update(['slug' => 'connexion']);
        $this->assertBlockedWithoutWrites('connexion');
    }

    public function test_audit_blocks_unknown_language_without_writing(): void
    {
        $this->connection->table('languages')->where('id', 1)->update(['code' => 'invalid tag!']);
        $this->assertBlockedWithoutWrites('invalid tag!');
    }

    public function test_audit_blocks_deleted_localization_without_parent_binding_with_exact_key(): void
    {
        $this->connection->table('catalog_example_bindings')->where('record_type', 'expansion')->delete();
        $this->assertBlockedWithoutWrites('expansion-localization:old-game/international/set/ja');
    }

    public function test_verification_detects_value_tampering_but_general_audit_allows_new_edits(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        $this->connection->table('expansions')->where('id', 42)->update(['description' => '{"fr":"Changed"}']);
        $this->assertFalse($converter->verify($this->connection)['ok']);
        $this->assertTrue($converter->audit($this->connection)['ok']);
        $this->connection->table('expansions')->where('id', 42)->update(['display_name' => '{"unknown":"No"}']);
        $this->assertFalse($converter->audit($this->connection)['ok']);
    }

    public function test_sources_are_read_only(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        $this->expectException(QueryException::class);
        $this->connection->table('series_localizations')->where('id', 50)->update(['name' => 'Overwrite']);
    }

    public function test_audit_command_works_on_old_converted_and_final_schemas_without_writing(): void
    {
        $before = $this->snapshot();
        $this->artisan('localization:audit')->assertSuccessful();
        $this->assertSame($before, $this->snapshot());
        $converter = $this->converter();
        $converter->up($this->connection);
        $this->artisan('localization:audit')->assertSuccessful();
        $maintenance = new ArrayMaintenanceMode;
        $this->app->instance(MaintenanceMode::class, $maintenance);
        $maintenance->activate([]);
        $this->artisan('localization:finalize')->assertSuccessful();
        $this->artisan('localization:audit')->assertSuccessful();
        $this->connection->table('games')->update(['slug' => 'connexion']);
        $this->artisan('localization:audit')->assertExitCode(1);
    }

    public function test_failed_copy_rolls_back_schema_values_and_bindings(): void
    {
        $before = $this->snapshot();
        $this->connection->unprepared("CREATE FUNCTION reject_test_copy() RETURNS trigger LANGUAGE plpgsql AS $$ BEGIN RAISE EXCEPTION 'Simulated copy failure'; END; $$");
        $this->connection->statement('CREATE TRIGGER reject_test_copy BEFORE UPDATE ON expansions FOR EACH ROW EXECUTE FUNCTION reject_test_copy()');
        try {
            $this->converter()->up($this->connection);
            $this->fail('The simulated failure must abort conversion.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('Simulated copy failure', $exception->getMessage());
        }
        $this->assertSame($before, $this->snapshot());
        $this->assertFalse($this->connection->getSchemaBuilder()->hasColumn('games', 'display_name'));
        $this->assertFalse($this->connection->getSchemaBuilder()->hasTable('series_images'));
        $this->assertFalse($this->connection->getSchemaBuilder()->hasTable('localization_conversion_verifications'));
    }

    public function test_reapplying_conversion_cannot_overwrite_new_translations(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        $this->connection->table('games')->update(['display_name' => '{"en":"New name"}']);
        try {
            $converter->up($this->connection);
            $this->fail('Repeated conversion must be refused.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('already applied', $exception->getMessage());
        }
        $this->assertSame(['en' => 'New name'], json_decode($this->connection->table('games')->value('display_name'), true));
    }

    public function test_missing_sources_without_finalization_and_missing_proof_fail_closed(): void
    {
        $converter = $this->converter();
        $converter->up($this->connection);
        $this->connection->statement('DROP TABLE series_localizations, expansion_localizations');
        $this->assertFalse($converter->audit($this->connection)['ok']);
        $this->assertFalse($converter->verify($this->connection)['ok']);
        $this->connection->table('localization_conversion_verifications')->delete();
        $this->assertFalse($converter->audit($this->connection)['ok']);
        $this->assertFalse($converter->verify($this->connection)['ok']);
    }

    public function test_audit_blocks_dangling_language_reference_without_writing(): void
    {
        $this->connection->statement('ALTER TABLE expansion_localizations DROP CONSTRAINT expansion_localizations_language_id_foreign');
        $this->connection->table('expansion_localizations')->where('id', 60)->update(['language_id' => 404]);
        $this->assertBlockedWithoutWrites('Unknown language reference');
    }

    public function test_audit_blocks_binding_collision_before_copy(): void
    {
        $this->connection->table('catalog_example_bindings')->insert([
            'manifest_key' => 'expansion-localization:old-game/international/set/FR',
            'record_type' => 'expansion-localization',
            'record_id' => 998,
        ]);
        $this->assertBlockedWithoutWrites('Binding collision');
    }

    public function test_audit_blocks_empty_translated_names_before_copy(): void
    {
        $this->connection->table('expansion_localizations')->where('id', 60)->update(['name' => '']);
        $this->assertBlockedWithoutWrites('Empty translated name');
    }

    private function assertBlockedWithoutWrites(string $problem): void
    {
        $converter = $this->converter();
        $before = $this->snapshot();
        $audit = $converter->audit($this->connection);
        $this->assertFalse($audit['ok']);
        $this->assertStringContainsString($problem, implode("\n", $audit['problems']));
        $this->assertSame($before, $this->snapshot());
        try {
            $converter->up($this->connection);
            $this->fail('Conversion must refuse an unsuccessful audit.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString($problem, $exception->getMessage());
        }
        $this->assertSame($before, $this->snapshot());
        $this->assertFalse($this->connection->getSchemaBuilder()->hasColumn('games', 'display_name'));
    }

    private function snapshot(): array
    {
        $snapshot = [];
        foreach (array_keys(require base_path('tests/Fixtures/Localization/legacy-catalog.php')) as $table) {
            $snapshot[$table] = $this->connection->table($table)->orderBy($table === 'catalog_example_bindings' ? 'manifest_key' : 'id')->get()->map(fn ($row) => (array) $row)->all();
        }

        return $snapshot;
    }
}
