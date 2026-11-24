<?php

namespace Tests\Feature\Catalog;

use App\Models\Expansion;
use App\Models\ExpansionEdition;
use App\Models\Game;
use Database\Seeders\TcgCatalogSeeder;
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

    public function test_english_display_falls_back_to_the_japanese_expansion_and_series_names(): void
    {
        $this->seed(TcgCatalogSeeder::class);

        $this->get('/en/pokemon/japanese/151?edition=ja')->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('expansion.name', 'ポケモンカード151')
                ->where('expansion.nameLanguage', 'ja')
                ->where('expansion.nameFallback', true)
                ->where('expansion.series.nameLanguage', 'ja')
                ->where('expansion.series.nameFallback', true)
                ->where('expansion.catalog.name', 'Japanese')
                ->where('expansion.catalog.nameLanguage', 'en')
                ->where('expansion.catalog.nameFallback', false));
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

    public function test_the_seed_reuses_a_preexisting_language_with_different_casing(): void
    {
        $id = DB::table('languages')->insertGetId(['code' => 'EN', 'name' => 'Existing English']);

        $this->seed(TcgCatalogSeeder::class);

        $this->assertSame([$id], DB::table('languages')->whereRaw("lower(code) = 'en'")->pluck('id')->all());
        $this->assertDatabaseHas('languages', ['id' => $id, 'code' => 'EN']);
        $this->assertDatabaseHas('expansion_editions', ['official_code' => 'OP01', 'language_id' => $id]);
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

    private function catalogueIds(): array
    {
        $ids = [];
        foreach (['languages', 'markets', 'games', 'catalogs', 'series', 'series_images', 'expansions', 'expansion_editions', 'expansion_releases'] as $table) {
            $ids[$table] = DB::table($table)->orderBy('id')->pluck('id')->all();
        }

        return $ids;
    }
}
