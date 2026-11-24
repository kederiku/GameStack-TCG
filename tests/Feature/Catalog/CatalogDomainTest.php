<?php

namespace Tests\Feature\Catalog;

use App\Models\Expansion;
use App\Models\ExpansionRelation;
use App\Models\ExpansionRelease;
use App\Models\Game;
use App\Models\Language;
use App\Models\SeriesImage;
use App\Support\Catalog\CatalogPresentation;
use Closure;
use DateTimeImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\Concerns\BuildsCatalog;
use Tests\TestCase;

class CatalogDomainTest extends TestCase
{
    use BuildsCatalog;
    use RefreshDatabase;

    public function test_games_retain_their_identity_when_catalogs_are_added(): void
    {
        $game = Game::factory()->create(['slug' => 'pokemon', 'is_visible' => false]);
        $catalog = $this->catalog($game);

        $this->assertTrue($game->catalogs()->sole()->is($catalog));
        $this->assertSame('pokemon', $game->fresh()->slug);
        $this->assertFalse($game->fresh()->is_visible);
    }

    public function test_expansion_requires_its_series_to_belong_to_its_catalog(): void
    {
        $catalog = $this->catalog();
        $other = $this->series($this->catalog($catalog->game, key: 'japan'));

        $this->assertInvalidField('series_id', fn () => $this->expansion($catalog, $other));
        $this->assertInvalidField('series_id', fn () => Expansion::create([
            'catalog_id' => $catalog->id, 'series_id' => $other->id,
            'key' => 'set', 'name' => 'セット', 'type' => 'main',
        ]));

        $set = $this->expansion($catalog);
        $this->expectException(QueryException::class);
        DB::transaction(fn () => DB::table('expansions')->where('id', $set->id)->update(['series_id' => $other->id]));
    }

    public function test_stable_keys_are_unique_within_their_parent(): void
    {
        $catalog = $this->catalog();
        $this->expansion($catalog);
        $this->catalog($catalog->game, key: 'japan');

        $this->assertInvalidField('key', fn () => $this->catalog($catalog->game));
        $this->assertInvalidField('key', fn () => $this->expansion($catalog));
        $this->assertSame('base-set', $this->expansion($this->catalog())->key);
    }

    public function test_clashes_on_generated_keys_get_distinct_keys_without_renaming_the_names(): void
    {
        $catalog = $this->catalog();
        $data = ['catalog_id' => $catalog->id, 'name' => '寶可夢卡牌 １５１', 'type' => 'special'];

        $first = $this->saveCatalog(new Expansion, $data);
        $second = $this->saveCatalog(new Expansion, $data);

        $this->assertMatchesRegularExpression('/\A[a-z0-9]+(?:-[a-z0-9]+)*\z/', $first->key);
        $this->assertNotSame($first->key, $second->key);
        $this->assertSame('寶可夢卡牌 １５１', $first->fresh()->name);
        $this->assertSame($first->key, $this->saveCatalog($first, ['name' => 'ポケモンカード151'])->fresh()->key);
    }

    public function test_language_tags_preserve_scripts_and_accept_new_languages(): void
    {
        $simplified = $this->language('zh-hans', '简体中文');
        $traditional = $this->language('zh-Hant', '繁體中文');
        $spanish = $this->language('es-419', 'Español latinoamericano');

        $this->assertSame('zh-Hans', $simplified->fresh()->code);
        $this->assertSame('zh-Hant', $traditional->fresh()->code);
        $this->assertSame('es-419', $spanish->fresh()->code);
        $this->assertInvalidField('code', fn () => Language::create(['code' => 'ZH-HANS', 'name' => 'Doublon']));
        $this->assertInvalidField('code', fn () => Language::create(['code' => 'zh_Hant', 'name' => 'Invalide']));
    }

    public function test_each_expansion_accepts_only_one_localization_and_edition_per_language(): void
    {
        $expansion = $this->expansion($this->catalog());
        $french = $this->language('fr', 'Français');
        $expansion->setTranslation('display_name', 'FR', 'Premier nom')->save();
        $expansion->setTranslation('display_name', 'fr', 'Écarlate et Violet')->save();
        $this->assertSame(['fr' => 'Écarlate et Violet'], $expansion->fresh()->getTranslations('display_name'));
        $this->assertSame(0, $expansion->editions()->count());

        $edition = $this->edition($expansion, $french);
        $edition->update(['official_code' => 'SV1']);
        $this->assertInvalidField('language_id', fn () => $this->edition($expansion, $french));

        $english = $this->edition($expansion);
        $english->update(['official_code' => 'SV1']);
        $this->assertSame(2, $expansion->editions()->where('official_code', 'SV1')->count());
    }

    public function test_standalone_editions_and_release_channels_do_not_invent_dates_or_states(): void
    {
        $edition = $this->edition($this->expansion($this->catalog()), status: 'released');
        $this->assertSame('released', $edition->fresh()->status);
        $this->assertSame(0, $edition->releases()->count());

        $general = $this->release($edition);
        $general->update(['announced_period' => 'Juin 2027']);
        $prerelease = $this->release($edition, channel: 'prerelease');
        $prerelease->update(['released_on' => new DateTimeImmutable('2001-01-02T01:23:00+14:00')]);

        $this->assertNull($general->fresh()->released_on);
        $this->assertSame('Juin 2027', $general->fresh()->announced_period);
        $this->assertSame('2001-01-02', $prerelease->fresh()->released_on->format('Y-m-d'));
        $this->assertSame('announced', $prerelease->fresh()->status);
        $this->assertInvalidField('channel', fn () => $this->release($edition));
        $this->assertInvalidField('released_on', fn () => $general->update(['released_on' => '2027-02-30']));
        $this->assertTrue($this->release($edition, $this->market('japan', 'Japon'))->exists);
    }

    public function test_a_published_release_promotes_only_its_edition_and_blocks_a_retrograde_state(): void
    {
        $expansion = $this->expansion($this->catalog());
        $edition = $this->edition($expansion);
        $anotherEdition = $this->edition($expansion, $this->language('fr', 'Français'));
        $general = $this->release($edition);
        $specialist = $this->release($edition, channel: 'specialist');
        $specialist->update(['status' => 'released']);

        $this->assertSame('released', $edition->fresh()->status);
        $this->assertSame('announced', $anotherEdition->fresh()->status);
        $this->assertSame('announced', $general->fresh()->status);
        $this->assertInvalidField('status', fn () => $this->saveCatalog($edition, ['status' => 'announced']));
        $this->assertInvalidField('status', fn () => $edition->update(['status' => 'cancelled']));

        // A stale form editing a different field must not undo the promotion.
        $edition->update(['name' => 'New title']);
        $this->assertSame('released', $edition->fresh()->status);

        $specialist->update(['status' => 'cancelled']);
        $this->assertSame('released', $edition->fresh()->status);
        $this->assertSame('announced', $this->saveCatalog($edition, ['status' => 'announced'])->fresh()->status);
    }

    public function test_related_extensions_are_canonical_without_transitive_edges(): void
    {
        $catalog = $this->catalog();
        $a = $this->expansion($catalog);
        $b = $this->expansion($this->catalog($catalog->game, key: 'japan'));
        $c = $this->expansion($catalog, key: 'another-set');
        $relation = $this->relate($b, $a);
        $this->relate($c, $b);

        $this->assertTrue($relation->fresh()->sourceExpansion->is($a));
        $this->assertTrue($relation->fresh()->targetExpansion->is($b));
        $this->assertInvalidField('target_expansion_id', fn () => $this->relate($a, $b));
        $this->assertSame([$b->id], $a->relations()->pluck('target_expansion_id')->all());
        $this->assertTrue($this->relate($b, $a, 'adapted_from')->sourceExpansion->is($b));
        $this->assertTrue($this->relate($a, $b, 'adapted_from')->sourceExpansion->is($a));
    }

    public function test_related_extensions_require_matching_games_distinct_endpoints_and_documentation(): void
    {
        $a = $this->expansion($this->catalog());
        $b = $this->expansion($this->catalog());
        $c = $this->expansion($a->catalog, key: 'local');
        $this->assertInvalidField('target_expansion_id', fn () => $this->relate($a, $b));
        $this->assertInvalidField('target_expansion_id', fn () => $this->relate($a, $a));
        $this->assertInvalidField('notes', fn () => $this->relate($a, $c, notes: '    '));
        $this->assertInvalidField('source_url', fn () => $this->relate($a, $c, source: 'not-a-url'));
        $this->assertSame(0, ExpansionRelation::count());
    }

    public function test_persisted_identities_cannot_change_but_series_membership_can(): void
    {
        $catalog = $this->catalog();
        $otherCatalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $edition = $this->edition($expansion);
        $release = $this->release($edition);
        $language = $this->language();
        $market = $this->market();

        $this->assertInvalidField('game_id', fn () => $this->saveCatalog($catalog, ['game_id' => $otherCatalog->game_id]));
        $this->assertInvalidField('catalog_id', fn () => $series->update(['catalog_id' => $otherCatalog->id]));
        $this->assertInvalidField('catalog_id', fn () => $expansion->update(['catalog_id' => $otherCatalog->id]));
        $this->assertInvalidField('key', fn () => $this->saveCatalog($expansion->fresh(), ['key' => 'new-key']));
        $this->assertInvalidField('expansion_id', fn () => $edition->update(['expansion_id' => $this->expansion($otherCatalog)->id]));
        $this->assertInvalidField('language_id', fn () => $edition->fresh()->update(['language_id' => $this->language('fr', 'Français')->id]));
        $this->assertInvalidField('channel', fn () => $release->update(['channel' => 'prerelease']));
        $this->assertInvalidField('code', fn () => $language->update(['code' => 'en-GB']));
        $this->assertInvalidField('key', fn () => $market->update(['key' => 'europe']));

        $this->saveCatalog($expansion->fresh(), ['series_id' => null]);
        $this->assertNull($expansion->fresh()->series);
    }

    public function test_deleting_a_parent_never_discards_editorial_children_implicitly(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $edition = $this->edition($expansion);
        $release = $this->release($edition);
        $series->setTranslation('display_name', 'en', 'Scarlet & Violet')->save();
        Storage::fake('public');
        $seriesImage = $this->saveCatalog(new SeriesImage, [
            'series_id' => $series->id,
            'language_id' => $this->language()->id,
            'image' => UploadedFile::fake()->image('series.png'),
        ]);
        $this->assertInvalidField('record', fn () => $catalog->game->delete());
        $this->assertInvalidField('record', fn () => $catalog->delete());
        $this->assertInvalidField('record', fn () => $series->delete());
        $this->assertInvalidField('record', fn () => $expansion->delete());
        $this->assertInvalidField('record', fn () => $edition->language->delete());
        $this->assertInvalidField('record', fn () => $release->market->delete());

        $edition->delete();
        $this->assertModelMissing($release);
        $target = $this->expansion($catalog, key: 'second-set');
        $link = $this->relate($expansion, $target);
        $this->assertInvalidField('record', fn () => $expansion->delete());
        $this->assertInvalidField('record', fn () => $target->delete());
        $link->delete();

        $this->saveCatalog($expansion, ['series_id' => null]);
        $series->delete();
        $this->assertModelMissing($series);
        $this->assertModelMissing($seriesImage);

        $expansion->delete();
        $target->delete();
        $catalog->delete();
        $this->assertTrue($catalog->game->delete());
    }

    public function test_visibility_is_inherited_from_game_catalog_series_and_edition(): void
    {
        $catalog = $this->catalog();
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $edition = $this->edition($expansion);
        $release = $this->release($edition);

        $this->assertTrue($release->isEffectivelyVisible());
        foreach ([$catalog->game, $catalog, $series, $expansion, $edition] as $ancestor) {
            $ancestor->update(['is_visible' => false]);
            $this->assertFalse($release->isEffectivelyVisible());
            $ancestor->update(['is_visible' => true]);
        }
        $catalog->update(['is_visible' => false]);
        $this->assertFalse($this->expansion($catalog, key: 'no-series')->isEffectivelyVisible());
        $this->assertTrue($edition->fresh()->is_visible);
    }

    public function test_localized_presentation_keeps_the_selected_names_language_and_its_own_image(): void
    {
        $english = $this->language();
        $catalog = $this->catalog(language: $english);
        $series = $this->series($catalog);
        $expansion = $this->expansion($catalog, $series);
        $presentation = app(CatalogPresentation::class);

        Storage::fake('public');
        $series->update(['display_name' => ['en' => 'Scarlet & Violet', 'fr' => 'Écarlate et Violet']]);
        $englishImage = $this->saveCatalog(new SeriesImage, [
            'series_id' => $series->id,
            'language_id' => $english->id,
            'image' => UploadedFile::fake()->image('series.png'),
        ]);
        $expansion->setTranslation('display_name', 'en', 'Scarlet & Violet—151')->save();

        $this->assertSame(['name' => 'Scarlet & Violet—151', 'language' => 'en', 'fallback' => true], $presentation->name($expansion, 'fr'));
        $this->assertSame(['name' => 'Écarlate et Violet', 'language' => 'fr', 'fallback' => false], $presentation->name($series, 'fr'));
        $this->assertNotNull($englishImage->image);
        $this->assertSame($englishImage->image, $presentation->seriesImage($series, 'en'));
        $this->assertSame($englishImage->image, $presentation->seriesImage($series, ' EN '));
        $this->assertNull($presentation->seriesImage($series, 'fr'));
        $this->assertSame(['name' => 'Scarlet & Violet', 'language' => 'en', 'fallback' => true], $presentation->name($series, 'de'));
        $this->assertNull($presentation->seriesImage($series, 'de'));
        $this->assertSame(['name' => 'Base Set', 'language' => null, 'fallback' => true], $presentation->name($this->expansion($catalog, key: 'untranslated'), 'fr'));
        $this->assertSame(0, $expansion->editions()->count());
    }

    public function test_references_and_domain_values_are_validated_on_ordinary_writes(): void
    {
        $catalog = $this->catalog();
        $expansion = $this->expansion($catalog);
        $edition = $this->edition($expansion);

        $this->assertInvalidField('default_language_id', fn () => $this->saveCatalog($catalog, ['default_language_id' => 98765]));
        $this->assertInvalidField('type', fn () => $expansion->update(['type' => 'product']));
        $this->assertInvalidField('status', fn () => $edition->update(['status' => 'inferred']));
        $this->assertInvalidField('channel', fn () => ExpansionRelease::create([
            'edition_id' => $edition->id, 'market_id' => $this->market()->id, 'channel' => 'print-run', 'status' => 'released',
        ]));
        $this->assertInvalidField('language_id', fn () => SeriesImage::create([
            'series_id' => $this->series($catalog)->id, 'language_id' => 98765,
        ]));
    }

    private function relate(Expansion $sourceExpansion, Expansion $target, string $type = 'related_to', string $notes = 'Les ensembles partagent une sélection de cartes.', string $source = 'https://example.com/source'): ExpansionRelation
    {
        return $this->saveCatalog(new ExpansionRelation, [
            'source_expansion_id' => $sourceExpansion->id,
            'target_expansion_id' => $target->id,
            'type' => $type,
            'notes' => $notes,
            'source_url' => $source,
        ]);
    }

    private function assertInvalidField(string $field, Closure $write): void
    {
        try {
            $write();
            $this->fail("La validation devrait refuser le champ {$field}.");
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }
    }
}
