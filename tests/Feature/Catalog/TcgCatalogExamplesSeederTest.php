<?php

namespace Tests\Feature\Catalog;

use App\Actions\Catalog\SeedCatalogExamples;
use App\Actions\Games\SaveGame;
use App\Models\Catalog;
use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\ExpansionRelease;
use App\Models\Game;
use Database\Seeders\TcgCatalogExamplesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\ForwardOnlyDatabaseMigrations;
use Tests\TestCase;

class TcgCatalogExamplesSeederTest extends TestCase
{
    use BuildsCatalog;
    use ForwardOnlyDatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();

        config(['game-images.disk' => 's3']);
        Storage::fake('s3');
        Http::preventStrayRequests();
    }

    public function test_explicit_seed_covers_twelve_published_games_without_users_or_invented_editions(): void
    {
        $this->assertTrue(class_exists(TcgCatalogExamplesSeeder::class), 'The explicit catalog examples seeder must exist.');
        $this->artisan('db:seed', ['--class' => TcgCatalogExamplesSeeder::class])
            ->expectsOutput('Catalogue TCG : 165 fiches créées, 2 conservées, 0 recréées.')
            ->assertSuccessful();

        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('expansions', 29);
        $this->assertDatabaseCount('expansion_editions', 34);
        $this->assertDatabaseCount('expansion_releases', 27);
        $this->assertDatabaseCount('expansion_relations', 7);
        $this->assertSame([
            'altered', 'cyberpunk', 'final-fantasy-tcg', 'flesh-and-blood', 'lorcana', 'magic',
            'naturo-mythos', 'one-piece', 'pokemon', 'riftbound', 'star-wars-unlimited', 'yu-gi-oh',
        ], Game::query()->whereHas('catalogs')->orderBy('slug')->pluck('slug')->all());
        $this->assertSame(0, Game::query()->where('is_visible', true)->count());
        $this->assertSame(0, Catalog::query()->where('is_visible', true)->count());

        $scarlet = $this->example('pokemon', 'japanese', 'scarlet-ex');
        $violet = $this->example('pokemon', 'japanese', 'violet-ex');
        $this->assertNotSame($scarlet->id, $violet->id);
        $this->assertSame('スカーレットex', $scarlet->editions()->sole()->name);
        $this->assertSame('バイオレットex', $violet->editions()->sole()->name);
        $japanese151 = $this->example('pokemon', 'japanese', '151');
        $this->assertSame(['fr' => 'Pokémon 151 (Japon)'], $japanese151->getTranslations('display_name'));
        $this->assertSame('ja', $japanese151->editions()->sole()->language->code);

        $traditional151 = $this->example('pokemon', 'traditional-chinese', '151')->editions()->sole();
        $traditionalSetB = $this->example('pokemon', 'traditional-chinese', 'set-b')->editions()->sole();
        $this->assertSame('zh-Hant', $traditional151->language->code);
        $this->assertSame('眾星雲集組合篇 SET B', $traditionalSetB->name);
        $this->assertSame(0, $traditionalSetB->releases()->count());
        $simplifiedFirst = $this->example('pokemon', 'simplified-chinese', '151-jing')->editions()->sole();
        $simplifiedSecond = $this->example('pokemon', 'simplified-chinese', '151-ju')->editions()->sole();
        $this->assertSame('zh-Hans', $simplifiedFirst->language->code);
        $this->assertSame('收集啦151 聚', $simplifiedSecond->name);
        $this->assertSame('2025-07-18', $simplifiedFirst->releases()->sole()->released_on?->toDateString());
        $this->assertSame('2025-10-17', $simplifiedSecond->releases()->sole()->released_on?->toDateString());

        $tcg = $this->example('yu-gi-oh', 'tcg', 'darkwing-blast');
        $english = $this->printedIn($tcg, 'en');
        $this->assertSame('2022-10-19', $english->releases()->where('channel', 'specialist')->sole()->released_on?->toDateString());
        $this->assertSame('2022-10-21', $english->releases()->where('channel', 'general')->sole()->released_on?->toDateString());
        $this->assertNull($this->example('magic', 'international', 'foundations')->series_id);
        $this->assertSame('reprint', $this->example('flesh-and-blood', 'international', 'history-pack-1')->type);
        $this->assertSame('OGS', $this->example('riftbound', 'international', 'proving-grounds')->editions()->sole()->official_code);
        $this->assertSame('ja', $this->example('final-fantasy-tcg', 'chapter', 'chapter-i')->editions()->sole()->language->code);
        $this->assertSame('en', $this->example('final-fantasy-tcg', 'opus', 'dreamlike-oceans')->editions()->sole()->language->code);

        $series = $this->example('pokemon', 'international', 'scarlet-violet')->series;
        $this->assertNotNull($series);
        $images = [
            $series->images()->whereRelation('language', 'code', 'fr')->sole()->image,
            $series->images()->whereRelation('language', 'code', 'en')->sole()->image,
            $this->printedIn($this->example('pokemon', 'international', 'scarlet-violet'), 'fr')->image,
            $this->printedIn($this->example('pokemon', 'international', 'scarlet-violet'), 'en')->image,
        ];
        $this->assertCount(4, array_unique($images));
        $disk = Storage::disk('s3');
        foreach ($images as $path) {
            $this->assertNotNull($path);
            $this->assertSame('image/avif', $disk->mimeType($path));
        }
        $this->assertEqualsCanonicalizing($images, $disk->allFiles());
    }

    public function test_seed_adopts_existing_rows_then_preserves_renamed_games_and_deliberately_cleared_fields(): void
    {
        $pokemon = app(SaveGame::class)->handle(new Game, [
            'slug' => 'pokemon',
            'name' => 'Catalogue Pokémon privé',
            'is_visible' => true,
            'image' => UploadedFile::fake()->image('custom.png'),
        ]);
        $bandai = Game::factory()->create(['slug' => 'naruto-card-game', 'is_visible' => false]);
        $catalog = $this->catalog($pokemon);
        $expansion = $this->saveCatalog(new Expansion, [
            'catalog_id' => $catalog->id,
            'key' => 'scarlet-violet',
            'name' => 'Nom de ma collection',
            'type' => 'special',
            'is_visible' => true,
        ]);
        $edition = $this->edition($expansion, $this->language('fr', 'Français'), 'released');

        $first = $this->runSeed();
        $this->assertGreaterThan(0, $first['created']);
        $this->assertSame(6, $first['preserved']);
        $this->assertSame(0, $first['recreated']);
        $this->assertSame($expansion->id, $this->example('pokemon', 'international', 'scarlet-violet')->id);
        $this->assertNull($edition->fresh()?->image);
        $this->assertSame('Nom de ma collection', $expansion->fresh()?->name);
        $this->assertTrue($expansion->fresh()?->is_visible);
        $this->assertNull($expansion->fresh()?->series_id);
        $this->assertSame([], $bandai->catalogs()->get()->all());
        $this->assertFalse($bandai->fresh()?->is_visible);
        $this->assertTrue($pokemon->fresh()?->is_visible);
        $this->assertSame('Catalogue Pokémon privé', $pokemon->fresh()?->name);
        $this->assertSame($pokemon->image, $pokemon->fresh()?->image);

        $pokemon->update(['slug' => 'mon-pokemon']);
        Game::factory()->create(['slug' => 'pokemon', 'name' => 'Autre jeu']);
        $english = $this->printedIn($expansion, 'en');
        $this->saveCatalog($english, ['image' => null, 'name' => 'Nom retouché', 'is_visible' => true]);
        $files = Storage::disk('s3')->allFiles();
        $counts = $this->runSeed();

        $this->assertSame(['created' => 0, 'preserved' => $first['created'] + $first['preserved'], 'recreated' => 0], $counts);
        $this->assertEqualsCanonicalizing($files, Storage::disk('s3')->allFiles());
        $this->assertSame($catalog->id, $this->example('mon-pokemon', 'international', 'scarlet-violet')->catalog_id);
        $this->assertSame(0, Game::query()->where('slug', 'pokemon')->sole()->catalogs()->count());
        $this->assertSame('Nom retouché', $english->fresh()?->name);
        $this->assertNull($english->fresh()?->image);
        $this->assertTrue($english->fresh()?->is_visible);
        $this->assertDatabaseCount('users', 0);
    }

    public function test_seed_recreates_a_deleted_image_owner_and_its_releases_without_reusing_the_old_file(): void
    {
        $first = $this->runSeed();
        $expansion = $this->example('pokemon', 'international', 'scarlet-violet');
        $edition = $this->printedIn($expansion, 'fr');
        $oldPath = $edition->image;
        $this->assertNotNull($oldPath);
        $edition->delete();

        $this->assertSame(['created' => 0, 'preserved' => $first['created'] + $first['preserved'] - 2, 'recreated' => 2], $this->runSeed());
        $replacement = $this->printedIn($expansion, 'fr');
        $this->assertNotSame($edition->id, $replacement->id);
        $this->assertNotSame($oldPath, $replacement->image);
        $this->assertSame('2023-03-31', $replacement->releases()->sole()->released_on?->toDateString());
        $this->assertNotNull($replacement->image);
        Storage::disk('s3')->assertExists($replacement->image);
        Storage::disk('s3')->assertMissing($oldPath);
        $this->assertSame(['created' => 0, 'preserved' => $first['created'] + $first['preserved'], 'recreated' => 0], $this->runSeed());
        $this->assertSame($replacement->image, $replacement->fresh()?->image);
    }

    public function test_mismatched_binding_aborts_the_entire_seed_including_images_created_earlier(): void
    {
        $this->runSeed();
        $french = $this->printedIn($this->example('pokemon', 'international', 'scarlet-violet'), 'fr');
        $french->delete();
        $altered = Catalog::query()->whereRelation('game', 'slug', 'altered')->sole();
        $wrong = $this->catalog();
        // Existing invalid external mapping: same key and type, but attached to a different game.
        DB::table('catalog_example_bindings')->where('record_type', 'catalog')->where('record_id', $altered->id)
            ->update(['record_id' => $wrong->id]);
        $files = Storage::disk('s3')->allFiles();

        try {
            $this->runSeed();
            $this->fail('A binding to the wrong game must abort the seed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('altered', $exception->getMessage());
        }

        $this->assertSame(0, ExpansionEdition::query()->where('expansion_id', $french->expansion_id)->where('language_id', $french->language_id)->count());
        $this->assertEqualsCanonicalizing($files, Storage::disk('s3')->allFiles());
    }

    public function test_transaction_does_not_publish_an_existing_announced_edition_to_restore_a_missing_release(): void
    {
        $this->runSeed();
        $edition = $this->example('altered', 'international', 'beyond-the-gates')->editions()->sole();
        $edition->releases()->sole()->delete();
        $this->saveCatalog($edition, ['status' => 'announced']);
        $french = $this->printedIn($this->example('pokemon', 'international', 'scarlet-violet'), 'fr');
        $french->delete();
        $files = Storage::disk('s3')->allFiles();

        try {
            $this->runSeed();
            $this->fail('A released launch cannot overwrite the status of an existing edition.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('beyond-the-gates', $exception->getMessage());
        }

        $this->assertSame('announced', $edition->fresh()?->status);
        $this->assertSame(0, ExpansionRelease::query()->where('edition_id', $edition->id)->count());
        $this->assertSame(0, ExpansionEdition::query()->where('expansion_id', $french->expansion_id)->where('language_id', $french->language_id)->count());
        $this->assertEqualsCanonicalizing($files, Storage::disk('s3')->allFiles());
    }

    public function test_an_unbound_game_with_the_official_name_at_another_slug_is_not_silently_adopted_or_duplicated(): void
    {
        $existing = Game::factory()->create(['slug' => 'mon-altered', 'name' => 'Altered']);

        try {
            $this->runSeed();
            $this->fail('A possible renamed game is ambiguous before the first seed.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('altered', $exception->getMessage());
        }

        $this->assertDatabaseCount('games', 1);
        $this->assertDatabaseHas('games', ['id' => $existing->id, 'slug' => 'mon-altered']);
        $this->assertDatabaseCount('expansions', 0);
        $this->assertSame([], Storage::disk('s3')->allFiles());
    }

    /** @return array{created: int, preserved: int, recreated: int} */
    private function runSeed(): array
    {
        $this->assertTrue(class_exists(SeedCatalogExamples::class), 'The rerunnable seed action must exist.');

        return app(SeedCatalogExamples::class)->handle();
    }

    private function example(string $game, string $catalog, string $expansion): Expansion
    {
        return Expansion::query()->where('key', $expansion)->whereRelation('catalog', 'key', $catalog)
            ->whereHas('catalog.game', fn ($query) => $query->where('slug', $game))->sole();
    }

    private function printedIn(Expansion $expansion, string $language): ExpansionEdition
    {
        return $expansion->editions()->whereRelation('language', 'code', $language)->sole();
    }
}
