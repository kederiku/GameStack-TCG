<?php

namespace Tests\Feature\Catalog;

use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\Game;
use Database\Seeders\TcgCatalogSeeder;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use RuntimeException;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\UsesIsolatedSchema;
use Tests\TestCase;

class TcgCatalogSeederTest extends TestCase
{
    use BuildsCatalog;
    use UsesIsolatedSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createIsolatedSchema();
        $this->artisan('migrate')->assertSuccessful();
        config(['game-images.disk' => 's3']);
        Storage::fake('s3');
    }

    public function test_the_default_seed_loads_the_public_catalog_without_users(): void
    {
        $this->artisan('db:seed')->assertSuccessful();

        $this->assertDatabaseEmpty('users');
        $this->assertDatabaseHas('games', ['slug' => 'naruto-mythos', 'name' => 'NARUTO Mythos TCG']);
        $this->assertDatabaseMissing('games', ['slug' => 'naturo-mythos']);
        $this->get('/en/one-piece/international/romance-dawn?edition=en')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'ROMANCE DAWN')
                ->where('expansion.nameLanguage', 'en')
                ->where('expansion.nameFallback', false)
                ->where('expansion.selectedEdition.language.code', 'en')
                ->where('expansion.selectedEdition.officialCode', 'OP01')
                ->where('expansion.selectedEdition.releases.0.market.key', 'north-america')
                ->where('expansion.selectedEdition.releases.0.releasedOn', '2022-12-02'));
        $this->get('/fr/pokemon/japanese/151?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'Pokémon 151 (Japon)')
                ->where('expansion.nameLanguage', 'fr')
                ->where('expansion.nameFallback', false)
                ->where('expansion.catalog.name', 'Japonais')
                ->where('expansion.catalog.nameLanguage', 'fr')
                ->where('expansion.catalog.nameFallback', false)
                ->has('expansion.editions', 1)
                ->where('expansion.selectedEdition.language.code', 'ja')
                ->where('expansion.selectedEdition.name', 'ポケモンカード151'));
    }

    public function test_pokemon_reference_covers_history_regions_and_promotions_without_inventing_editions(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $pokemon = Game::query()->where('slug', 'pokemon')->sole();
        foreach ([
            ['international', 'base-set', ['en', 'fr']],
            ['international', 'base-set-2', ['en']],
            ['international', 'arceus', ['en']],
            ['international', 'legendary-treasures', ['en']],
            ['international', 'delta-reign', ['en', 'fr']],
            ['japanese', 'expansion-pack-base-set', ['ja']],
            ['korean', 'start-of-an-adventure-ktcg-0', ['ko']],
            ['thai', 'first-impact-set-a', ['th']],
            ['indonesian', 'first-impact-set-a', ['id']],
            ['international', 'mcdonalds-2018-france', ['fr']],
            ['international', 'mcdonalds-2018-international', ['en', 'fr']],
            ['international', 'wizards-black-star-promos', ['en']],
        ] as [$catalog, $key, $languages]) {
            $expansion = $pokemon->catalogs()->where('key', $catalog)->sole()->expansions()->where('key', $key)->sole();
            $this->assertSame($languages, $expansion->editions()->with('language')->get()->pluck('language.code')->sort()->values()->all(), "$catalog/$key");
        }

        $japanese = $pokemon->catalogs()->where('key', 'japanese')->sole();
        $this->assertFalse($japanese->expansions()->where('key', 'start-of-an-adventure-ktcg-0')->exists());
        $taiwan = $pokemon->catalogs()->where('key', 'traditional-chinese')->sole();
        $this->assertFalse($taiwan->expansions()->where('key', 'collection-sun')->exists());
    }

    public function test_pokemon_reference_preserves_month_precision_and_existing_native_titles(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $international = Game::query()->where('slug', 'pokemon')->sole()->catalogs()->where('key', 'international')->sole();
        $base = $international->expansions()->where('key', 'base-set')->sole();
        $this->assertCount(0, $base->editions()->whereHas('language', fn ($query) => $query->where('code', 'fr'))->sole()->releases);
        $this->get('/fr/pokemon/japanese/pokemon-jungle?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.selectedEdition.name', '—')
                ->where('expansion.selectedEdition.releases.0.releasedOn', null)
                ->where('expansion.selectedEdition.releases.0.announcedPeriod', '1997-03'));

        $this->get('/fr/pokemon/international/scarlet-violet?edition=fr')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.selectedEdition.name', 'Écarlate et Violet')
                ->where('expansion.selectedEdition.releases.0.releasedOn', '2023-03-31'));
        $this->get('/fr/pokemon/japanese/151?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.selectedEdition.name', 'ポケモンカード151')
                ->where('expansion.selectedEdition.releases.0.releasedOn', '2023-06-16'));
    }

    public function test_english_display_uses_english_names_for_the_japanese_expansion_and_series(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $this->get('/en/pokemon/japanese/151?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'Pokémon Card 151')
                ->where('expansion.nameLanguage', 'en')
                ->where('expansion.nameFallback', false)
                ->where('expansion.series.name', 'Scarlet & Violet')
                ->where('expansion.series.nameLanguage', 'en')
                ->where('expansion.series.nameFallback', false)
                ->has('expansion.editions', 1)
                ->where('expansion.selectedEdition.language.code', 'ja')
                ->where('expansion.selectedEdition.name', 'ポケモンカード151')
                ->where('expansion.catalog.name', 'Japanese')
                ->where('expansion.catalog.nameLanguage', 'en')
                ->where('expansion.catalog.nameFallback', false));
    }

    public function test_french_display_falls_back_to_the_english_display_name_of_scarlet_ex(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $this->get('/fr/pokemon/japanese/scarlet-ex?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'Scarlet ex')
                ->where('expansion.nameLanguage', 'en')
                ->where('expansion.nameFallback', true)
                ->has('expansion.editions', 1)
                ->where('expansion.selectedEdition.language.code', 'ja')
                ->where('expansion.selectedEdition.name', 'スカーレットex'));
    }

    public function test_the_seed_covers_selected_sets_and_their_french_print_runs(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $this->assertDatabaseCount('games', 12);
        $this->assertDatabaseCount('catalogs', 21);
        $this->assertDatabaseCount('series', 65);
        $this->assertSame(
            [
                'altered' => [5, 5, 5],
                'cyberpunk' => [1, 1, 1],
                'final-fantasy-tcg' => [10, 10, 9],
                'flesh-and-blood' => [10, 10, 4],
                'lorcana' => [10, 10, 10],
                'magic' => [10, 10, 10],
                'naruto-mythos' => [2, 2, 2],
                'one-piece' => [20, 10, 4],
                'pokemon' => [826, 493, 139],
                'riftbound' => [6, 6, 6],
                'star-wars-unlimited' => [10, 10, 10],
                'yu-gi-oh' => [20, 10, 10],
            ],
            DB::table('expansions')
                ->join('catalogs', 'catalogs.id', '=', 'expansions.catalog_id')
                ->join('games', 'games.id', '=', 'catalogs.game_id')
                ->leftJoin('expansion_editions', 'expansion_editions.expansion_id', '=', 'expansions.id')
                ->leftJoin('languages', 'languages.id', '=', 'expansion_editions.language_id')
                ->groupBy('games.slug')
                ->orderBy('games.slug')
                ->selectRaw("games.slug, count(distinct expansions.id) as rows, count(distinct expansions.key) as keys, count(distinct expansions.id) filter (where languages.code = 'fr') as french_prints")
                ->get()
                ->mapWithKeys(fn ($row) => [$row->slug => [$row->rows, $row->keys, $row->french_prints]])
                ->all(),
        );
    }

    public function test_named_printed_titles_use_their_attested_languages(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $this->assertDatabaseCount('languages', 16);
        foreach ([
            ['magic', 'international', 'foundations', ['de', 'en', 'es', 'fr', 'it', 'ja']],
            ['magic', 'international', 'khans-of-tarkir', ['en', 'fr']],
            ['one-piece', 'international', 'romance-dawn', ['en']],
            ['one-piece', 'international', 'emperors-in-the-new-world', ['en', 'fr']],
            ['one-piece', 'japanese', 'emperors-in-the-new-world', ['ja']],
            ['pokemon', 'international', 'temporal-forces', ['de', 'en', 'es', 'fr', 'it']],
            ['riftbound', 'international', 'origins', ['en', 'fr', 'ko', 'zh-Hans', 'zh-Hant']],
            ['riftbound', 'international', 'radiance', ['en', 'fr', 'zh-Hans']],
            ['final-fantasy-tcg', 'chapter', 'chapter-i', ['ja']],
            ['final-fantasy-tcg', 'opus', 'journey-of-discovery', ['en', 'fr', 'ja', 'zh-Hans']],
            ['yu-gi-oh', 'tcg', 'phantom-nightmare', ['de', 'en', 'es', 'fr', 'it', 'pt']],
        ] as [$game, $catalog, $key, $languages]) {
            $editions = DB::table('expansion_editions')
                ->join('languages', 'languages.id', '=', 'expansion_editions.language_id')
                ->join('expansions', 'expansions.id', '=', 'expansion_editions.expansion_id')
                ->join('catalogs', 'catalogs.id', '=', 'expansions.catalog_id')
                ->join('games', 'games.id', '=', 'catalogs.game_id')
                ->where('games.slug', $game)
                ->where('catalogs.key', $catalog)
                ->where('expansions.key', $key)
                ->orderBy('languages.code')
                ->pluck('languages.code');

            $this->assertSame($languages, $editions->all(), "$game/$catalog/$key");
        }

        $dominaria = Expansion::query()->where('key', 'dominaria-united')
            ->whereHas('catalog.game', fn ($query) => $query->where('slug', 'magic'))
            ->sole();
        $chineseEdition = $dominaria->editions()
            ->whereHas('language', fn ($query) => $query->where('code', 'zh-Hans'))
            ->sole();
        $this->assertSame('多明纳里亚：众志成城', $dominaria->getTranslations('display_name')['zh-Hans']);
        $this->assertSame('多明纳里亚：众志成城', $chineseEdition->name);

        $this->get('/fr/one-piece/international/emperors-in-the-new-world?edition=fr')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.selectedEdition.name', 'LES NOUVEAUX EMPEREURS')
                ->where('expansion.selectedEdition.releases.0.releasedOn', '2025-02-07'));
        $this->get('/en/yu-gi-oh/ocg/the-infinite-forbidden?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'The Infinite Forbidden')
                ->where('expansion.selectedEdition.name', 'INFINITE FORBIDDEN'));
    }

    public function test_the_manifest_references_documented_sources_languages_and_markets(): void
    {
        $directory = database_path('seeders/data');
        $manifest = require $directory.'/tcg.php';
        $sources = require $directory.'/tcg-sources.php';
        $records = array_map(fn (string $path) => require $directory.'/'.$path, $manifest['games']);
        $this->assertManifestReferences($manifest, $manifest, $sources);
        $this->assertManifestReferences($records, $manifest, $sources);

        foreach ($records as $game) {
            foreach ($game['catalogs'] as $catalog) {
                foreach ([...$catalog['series'], ...$catalog['expansions']] as $record) {
                    $name = $record['attributes']['name'];
                    if (preg_match('/[\p{Han}\p{Hiragana}\p{Katakana}\p{Hangul}]/u', $name)) {
                        $this->assertNotEmpty($record['attributes']['display_name']['en'] ?? null, $name);
                        $this->assertArrayHasKey('display_name.en', $record['sources'], $name);
                    }
                }
            }
        }
    }

    public function test_a_second_seed_reuses_records_and_encoded_media(): void
    {
        $this->seed(TcgCatalogSeeder::class);
        $ids = $this->catalogueIds();
        $disk = Storage::disk('s3');
        $files = $disk->allFiles();
        $this->assertCount(4, $files);

        $this->seed(TcgCatalogSeeder::class);

        $this->assertSame($ids, $this->catalogueIds());
        $this->assertSame($files, $disk->allFiles());
    }

    public function test_reseeding_restores_supplied_values_and_preserves_unlisted_content(): void
    {
        $this->seed(TcgCatalogSeeder::class);
        $expansion = $this->baseSet();
        $this->saveCatalog($expansion, [
            'is_visible' => false,
            'display_name' => ['fr' => 'Titre modifié', 'en' => 'Scarlet & Violet', 'ja' => 'Libellé ajouté'],
        ]);
        $foreign = $this->expansion($expansion->catalog, key: 'unlisted-expansion');

        $this->seed(TcgCatalogSeeder::class);

        $expansion->refresh();
        $this->assertTrue($expansion->is_visible);
        $this->assertSame('Écarlate et Violet', $expansion->getTranslations('display_name')['fr']);
        $this->assertSame('Libellé ajouté', $expansion->getTranslations('display_name')['ja']);
        $this->assertDatabaseHas('expansions', ['id' => $foreign->id, 'key' => 'unlisted-expansion']);
    }

    public function test_the_seed_locks_existing_languages_in_canonical_code_order(): void
    {
        $english = DB::table('languages')->insertGetId(['code' => 'EN', 'name' => 'Existing English']);
        $chinese = DB::table('languages')->insertGetId(['code' => 'zh-hans', 'name' => 'Existing Simplified Chinese']);
        $german = DB::table('languages')->insertGetId(['code' => 'de', 'name' => 'Existing German']);
        $existing = array_fill_keys([$english, $chinese, $german], true);
        $locked = [];
        DB::listen(static function (QueryExecuted $query) use ($existing, &$locked): void {
            if (str_contains($query->sql, 'from "languages"') && str_ends_with($query->sql, 'for update')
                && isset($existing[$query->bindings[0]])) {
                $locked[] = $query->bindings[0];
            }
        });

        $this->seed(TcgCatalogSeeder::class);

        $this->assertSame([$german, $english, $chinese], $locked);
        $this->assertDatabaseHas('languages', ['id' => $english, 'code' => 'EN']);
        $this->assertDatabaseHas('languages', ['id' => $chinese, 'code' => 'zh-hans']);
        $this->assertDatabaseHas('expansion_editions', ['official_code' => 'OP01', 'language_id' => $english]);
        $this->assertDatabaseHas('expansion_editions', ['official_code' => 'DMU', 'language_id' => $chinese]);
    }

    public function test_a_missing_seed_image_is_recreated_once(): void
    {
        $this->seed(TcgCatalogSeeder::class);
        $edition = $this->baseSet()->editions()->whereHas('language', fn ($query) => $query->where('code', 'fr'))->sole();
        $disk = Storage::disk('s3');
        $disk->delete($edition->image);
        $this->assertCount(3, $disk->allFiles());

        $this->seed(TcgCatalogSeeder::class);

        $restored = $edition->refresh()->image;
        $this->assertSame('image/avif', $disk->mimeType($restored));
        $this->assertCount(4, $disk->allFiles());
        $this->seed(TcgCatalogSeeder::class);
        $this->assertSame($restored, $edition->refresh()->image);
        $this->assertCount(4, $disk->allFiles());
    }

    public function test_the_seed_restores_a_replaced_cover_via_the_image_lifecycle(): void
    {
        $this->seed(TcgCatalogSeeder::class);
        $edition = $this->baseSet()->editions()->whereHas('language', fn ($query) => $query->where('code', 'fr'))->sole();
        $disk = Storage::disk('s3');
        $original = $disk->get($edition->image);
        $edited = $this->saveCatalog($edition, ['image' => UploadedFile::fake()->image('other.png')])->image;
        $this->assertNotSame($original, $disk->get($edited));

        $this->seed(TcgCatalogSeeder::class);

        $disk->assertMissing($edited);
        $this->assertSame($original, $disk->get($edition->refresh()->image));
        $this->assertCount(4, $disk->allFiles());
    }

    public function test_failure_after_image_creation_rolls_back_new_records_and_files(): void
    {
        $foreign = $this->edition($this->expansion($this->catalog(Game::factory()->create(['slug' => 'unlisted-game']))));
        $foreign = $this->saveCatalog($foreign, ['image' => UploadedFile::fake()->image('existing.png')]);
        $ids = $this->catalogueIds();
        $disk = Storage::disk('s3');
        $files = $disk->allFiles();
        $contents = $disk->get($foreign->image);

        ExpansionEdition::creating(function (ExpansionEdition $edition) use ($disk): ?bool {
            if ($edition->name !== 'Écarlate et Violet') {
                return null;
            }

            // SaveCatalogRecord has already written this edition's image and the series covers.
            $disk->assertExists($edition->image);
            $this->assertGreaterThan(2, count($disk->allFiles()));

            return false;
        });

        $failed = false;
        try {
            $this->seed(TcgCatalogSeeder::class);
        } catch (RuntimeException $exception) {
            $this->assertSame(__('validation.catalog.save_cancelled'), $exception->getMessage());
            $failed = true;
        }

        $this->assertTrue($failed, 'Cancelling the write must fail the seed.');
        $this->assertSame($ids, $this->catalogueIds());
        $this->assertSame($files, $disk->allFiles());
        $this->assertSame($contents, $disk->get($foreign->fresh()->image));
    }

    private function baseSet(): Expansion
    {
        return Expansion::query()->where('key', 'scarlet-violet')->whereHas('catalog.game', fn ($query) => $query->where('slug', 'pokemon'))->sole();
    }

    private function assertManifestReferences(array $record, array $manifest, array $sources): void
    {
        foreach (['language' => 'languages', 'default_language' => 'languages', 'market' => 'markets'] as $key => $reference) {
            if (isset($record[$key])) {
                $this->assertArrayHasKey($record[$key], $manifest[$reference]);
            }
        }

        foreach ($record['sources'] ?? [] as $id) {
            $this->assertArrayHasKey($id, $sources);
            if ($sources[$id]['url'] === null) {
                $this->assertNotEmpty($sources[$id]['document'], $id);
                $this->assertNotEmpty($sources[$id]['locator'], $id);
                $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $sources[$id]['sha256'], $id);
            } else {
                $this->assertMatchesRegularExpression('#^https?://#', $sources[$id]['url'], $id);
            }
            $this->assertNotEmpty($sources[$id]['consulted_on'], $id);
            $this->assertNotEmpty($sources[$id]['note'], $id);
        }

        if (isset($record['language']) && isset($record['attributes'])) {
            foreach (['name', 'language', 'status'] as $field) {
                $this->assertArrayHasKey($field, $record['sources']);
            }
        }
        if (isset($record['market']) && isset($record['attributes'])) {
            foreach (['market', 'channel', 'released_on', 'status'] as $field) {
                $this->assertArrayHasKey($field, $record['sources']);
            }
        }

        foreach ($record as $key => $value) {
            if ($key !== 'sources' && is_array($value)) {
                $this->assertManifestReferences($value, $manifest, $sources);
            }
        }
    }

    private function catalogueIds(): array
    {
        $ids = [];
        foreach (['languages', 'markets', 'games', 'catalogs', 'series', 'series_images', 'expansions', 'expansion_editions', 'expansion_releases'] as $table) {
            $ids[$table] = DB::table($table)->orderBy('id')->pluck('id')->all();
        }

        return $ids;
    }
}
