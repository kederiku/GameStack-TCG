<?php

namespace Tests\Feature\Localization;

use App\Actions\Catalog\SaveTranslations;
use App\Actions\Games\SaveGame;
use App\Models\CatalogExampleBinding;
use App\Models\Expansion;
use App\Models\Game;
use App\Models\Language;
use App\Models\Series;
use App\Support\Localization\TranslatedContent;
use App\Support\Localization\TranslationPatch;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\UsesLocalizedSchema;
use Tests\TestCase;

class TranslatableContentTest extends TestCase
{
    use BuildsCatalog;
    use UsesLocalizedSchema;

    public function test_missing_translation_falls_back_with_provenance(): void
    {
        $this->assertTrue(class_exists(TranslatedContent::class), 'The presence-sensitive resolver must exist.');
        $this->assertSame(['value' => 'English', 'locale' => 'en', 'fallback' => true], TranslatedContent::resolve(['en' => 'English'], ['fr', 'en'], 'Base'));
        $this->assertSame(['value' => 'Base', 'locale' => null, 'fallback' => true], TranslatedContent::resolve(['ja' => '無視'], ['fr', 'en'], 'Base'));
        $this->assertSame(['value' => null, 'locale' => null, 'fallback' => true], TranslatedContent::resolve([], ['fr', 'en']));
    }

    public function test_explicit_null_stops_fallback(): void
    {
        $this->assertTrue(class_exists(TranslatedContent::class), 'The presence-sensitive resolver must exist.');
        $this->assertSame(['value' => null, 'locale' => 'fr', 'fallback' => false], TranslatedContent::resolve(['fr' => null, 'en' => 'English'], ['fr', 'en'], 'Base'));
    }

    public function test_patch_distinguishes_removal_null_and_unchanged_values(): void
    {
        $this->assertTrue(class_exists(TranslationPatch::class), 'Per-locale deltas must exist.');
        $this->assertSame([
            ['attribute' => 'description', 'locale' => 'fr', 'operation' => 'set', 'value' => null],
            ['attribute' => 'description', 'locale' => 'en', 'operation' => 'remove', 'value' => null],
        ], TranslationPatch::between(['description' => ['fr' => 'Avant', 'en' => 'Before']], ['description' => ['fr' => null]]));
    }

    public function test_nullable_text_and_inactive_content_locale_survive_round_trip(): void
    {
        $this->createLocalizedSchema();
        $language = $this->language('zh-Hant', 'Chinese');
        DB::table('languages')->where('id', $language->id)->update(['code' => 'zh-hant']);
        $game = Game::factory()->create(['reference_description' => 'Base']);
        $game = app(SaveTranslations::class)->handle($game, 'ZH-hant', ['display_name' => '繁體', 'description' => '  ']);
        $this->assertSame(['zh-Hant' => null], $game->fresh()->getTranslations('description'));
        $this->assertSame(['zh-Hant' => '繁體'], $game->fresh()->getTranslations('display_name'));
        $this->assertSame(['value' => null, 'locale' => 'zh-Hant', 'fallback' => false], TranslatedContent::field($game->fresh(), 'description', 'zh-hant'));
        $language->refresh()->update(['name' => 'Traditional Chinese']);
        $this->assertSame('zh-hant', $language->fresh()->code);
        $this->assertSame(1, Language::whereRaw('lower(code) = ?', ['zh-hant'])->count());
    }

    public function test_full_map_delta_sets_a_new_blank_locale_and_skips_an_existing_null(): void
    {
        $game = new Game;
        $game->setRawAttributes(['description' => '{"fr":null,"de":"Alt"}'], sync: true);

        $this->assertSame([
            ['attribute' => 'description', 'locale' => 'en', 'operation' => 'set', 'value' => null],
            ['attribute' => 'description', 'locale' => 'de', 'operation' => 'remove', 'value' => null],
        ], TranslationPatch::forRecord($game, ['description' => ['fr' => '  ', 'en' => '  ']]));
    }

    public function test_each_content_model_uses_the_right_fallback_without_persisting_it(): void
    {
        $this->createLocalizedSchema();
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog);
        $release = $this->release($this->edition($expansion));
        foreach ([$catalog->game, $catalog, $series, $expansion, $this->market()] as $record) {
            app(SaveTranslations::class)->handle($record, 'en', ['display_name' => 'English']);
            $record->refresh();
            $resolved = TranslatedContent::field($record, 'display_name', 'fr');
            $this->assertSame($record instanceof Series || $record instanceof Expansion ? 'English' : $record->name, $resolved['value']);
            $this->assertArrayNotHasKey('fr', $record->getTranslations('display_name'));
        }
        app(SaveTranslations::class)->handle($release, 'en', ['announced_period' => 'Spring']);
        $this->assertSame(['en' => 'Spring'], $release->fresh()->getTranslations('announced_period'));
    }

    public function test_empty_display_name_and_unknown_locale_or_field_are_rejected(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create();
        foreach ([['fr', ['display_name' => ' ']], ['zz', ['description' => 'Unknown']], ['fr', ['name' => 'Wrong field']], ['fr', ['description' => ['nested']]]] as [$locale, $values]) {
            try {
                app(SaveTranslations::class)->handle($game, $locale, $values);
                $this->fail('Invalid translation must be rejected.');
            } catch (ValidationException) {
                $this->assertSame([], $game->fresh()->getTranslations('display_name'));
            }
        }
    }

    public function test_language_delete_checks_active_and_json_only_usage(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create();
        $japanese = $this->language('ja', 'Japanese');
        $game->setTranslation('description', 'ja', null)->save();
        foreach ([Language::where('code', 'fr')->firstOrFail(), $japanese] as $language) {
            try {
                $language->delete();
                $this->fail('An active or referenced language cannot be deleted.');
            } catch (ValidationException) {
                $this->assertNotNull($language->fresh());
            }
        }
        $game->forgetTranslation('description', 'ja')->save();
        $this->assertSame('{}', DB::table('games')->where('id', $game->id)->value('description'));
        $this->assertTrue($japanese->delete());
    }

    public function test_stale_ordinary_and_service_saves_merge_only_modified_locale_keys(): void
    {
        $this->createLocalizedSchema();
        foreach ([Game::factory()->create(), $this->series($this->catalog())] as $record) {
            $a = $record->fresh();
            $b = $record->fresh();
            $a->setTranslation('display_name', 'fr', 'Français')->save();
            $b->setTranslation('display_name', 'en', 'English')->save();
            $this->assertEquals(['fr' => 'Français', 'en' => 'English'], $record->fresh()->getTranslations('display_name'));
            $a = $record->fresh();
            $b = $record->fresh();
            app(SaveTranslations::class)->handle($a, 'fr', ['description' => null]);
            app(SaveTranslations::class)->handle($b, 'en', ['description' => 'English description']);
            $this->assertEquals(['fr' => null, 'en' => 'English description'], $record->fresh()->getTranslations('description'));
            $a = $record->fresh();
            $b = $record->fresh();
            $a->setTranslation('display_name', 'en', 'New English')->save();
            $b->forgetTranslation('display_name', 'fr')->save();
            $this->assertSame(['en' => 'New English'], $record->fresh()->getTranslations('display_name'));
            app(SaveTranslations::class)->handle($b, 'en', [], remove: true);
            $this->assertSame(['fr' => null], $record->fresh()->getTranslations('description'));
        }
    }

    public function test_unknown_locale_is_rejected_even_for_empty_or_remove_requests(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create();
        foreach ([false, true] as $remove) {
            try {
                app(SaveTranslations::class)->handle($game, 'zz', [], $remove);
                $this->fail('Unknown locales must not be silently accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('locale', $exception->errors());
            }
        }
    }

    public function test_raw_invalid_json_is_rejected_and_catalog_action_merges_stale_maps(): void
    {
        $this->createLocalizedSchema();
        $series = $this->series($this->catalog());
        $stale = $series->fresh();
        $this->saveCatalog($series, ['description' => ['fr' => 'Français']]);
        $this->saveCatalog($stale, ['description' => ['en' => 'English']]);
        $this->assertEquals(['fr' => 'Français', 'en' => 'English'], $series->fresh()->getTranslations('description'));
        foreach (['[]', 'null', '{"fr":[]}', '{"unknown code":"Bad"}', '{bad'] as $raw) {
            $copy = $series->fresh();
            $copy->setRawAttributes(array_replace($copy->getAttributes(), ['description' => $raw]));
            try {
                $copy->save();
                $this->fail('Malformed translation JSON must be rejected.');
            } catch (ValidationException) {
                $this->assertEquals(['fr' => 'Français', 'en' => 'English'], $series->fresh()->getTranslations('description'));
            }
        }
        $series->refresh()->setTranslations('description', [])->save();
        $this->assertSame('{}', DB::table('series')->where('id', $series->id)->value('description'));
    }

    public function test_binding_only_usage_keeps_its_language_and_binding_writes_require_known_languages(): void
    {
        $this->createLocalizedSchema();
        $japanese = $this->language('ja', 'Japanese');
        $series = $this->series($this->catalog());
        $binding = CatalogExampleBinding::create(['manifest_key' => 'series:example/ja', 'record_type' => 'series', 'record_id' => $series->id, 'locale' => 'JA']);
        $this->assertSame('ja', $binding->fresh()->locale);
        try {
            $japanese->delete();
            $this->fail('Binding-only locale usage must remain valid.');
        } catch (ValidationException) {
            $this->assertNotNull($japanese->fresh());
        }
        $binding->delete();
        $japanese->delete();
        $this->expectException(ValidationException::class);
        CatalogExampleBinding::create(['manifest_key' => 'series:example/ja', 'record_type' => 'series', 'record_id' => $series->id, 'locale' => 'ja']);
    }

    public function test_saving_another_field_or_locale_preserves_exact_historical_values(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create();
        DB::table('games')->where('id', $game->id)->update(['description' => '{"fr":"  original  ","en":""}']);
        $game->refresh();
        $this->assertSame(['en' => '', 'fr' => '  original  '], $game->getTranslations('description'));
        $game->name = 'Renamed';
        $game->save();
        $this->assertSame(['en' => '', 'fr' => '  original  '], $game->fresh()->getTranslations('description'));
        $game->setTranslation('description', 'en', '  New  ')->save();
        $this->assertSame(['en' => 'New', 'fr' => '  original  '], $game->fresh()->getTranslations('description'));
        $game->forgetTranslation('description', 'en')->save();
        $this->assertSame(['fr' => '  original  '], $game->fresh()->getTranslations('description'));
    }

    public function test_reference_changes_and_game_service_preserve_other_translations(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create(['reference_description' => 'Reference']);
        $stale = $game->fresh();
        $game->setTranslation('description', 'en', 'English')->save();
        $updated = app(SaveGame::class)->handle($stale, ['name' => $game->name, 'reference_description' => null, 'description' => ['fr' => null]]);
        $this->assertNull($updated->reference_description);
        $this->assertEquals(['fr' => null, 'en' => 'English'], $updated->getTranslations('description'));
        $this->assertSame($game->name, $updated->name);
    }

    public function test_full_map_preserves_unchanged_historical_text_and_normalizes_the_edit(): void
    {
        $this->createLocalizedSchema();
        $game = Game::factory()->create();
        DB::table('games')->where('id', $game->id)->update(['description' => '{"fr":"  original  ","en":"Old"}']);

        $game->refresh()->setTranslations('description', ['FR' => '  original  ', 'en' => '  New  '])->save();

        $this->assertSame(['en' => 'New', 'fr' => '  original  '], $game->fresh()->getTranslations('description'));
    }
}
