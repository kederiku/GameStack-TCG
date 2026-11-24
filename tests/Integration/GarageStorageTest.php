<?php

namespace Tests\Integration;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use App\Models\SeriesImage;
use App\Services\CatalogImages;
use App\Services\GameImages;
use App\Support\PublicGames;
use Database\Seeders\ContentLanguagesSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\UsesIsolatedSchema;
use Tests\TestCase;

// Explicit opt-in: php artisan test tests/Integration/GarageStorageTest.php
// Uses the dedicated testing database and removes only its own UUID objects.
class GarageStorageTest extends TestCase
{
    use BuildsCatalog;
    use UsesIsolatedSchema;

    /** @var list<string> */
    private array $paths = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->createIsolatedSchema();
        $this->artisan('migrate', ['--seed' => true, '--seeder' => ContentLanguagesSeeder::class])->assertSuccessful();
        config(['game-images.disk' => 's3']);
    }

    protected function tearDown(): void
    {
        try {
            foreach ($this->paths as $path) {
                GameImages::disk()->delete($path);
            }
        } finally {
            parent::tearDown();
        }
    }

    public function test_real_storage_supports_the_image_lifecycle_public_urls_and_cors(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Garage integration', 'image' => UploadedFile::fake()->image('test.png', 32, 16)]);
        $this->paths[] = $original = $game->image;
        $disk = GameImages::disk();
        $this->assertTrue($disk->exists($original));
        $this->assertContains($original, $disk->allFiles(dirname($original)));
        $this->assertSame('image/avif', $disk->mimeType($original));
        $contents = $disk->get($original);
        $url = PublicGames::present($game)['imageUrl'];
        $this->assertStringStartsWith(config('filesystems.disks.s3.url').'/', $url);
        $response = Http::withHeaders([
            'Host' => parse_url($url, PHP_URL_HOST), 'Origin' => config('app.url'),
        ])->get('http://garage:3902/'.$original);
        $response->throw();
        $this->assertSame($contents, $response->body());
        $this->assertSame('image/avif', $response->header('Content-Type'));
        $this->assertSame(config('app.url'), $response->header('Access-Control-Allow-Origin'));

        DB::beginTransaction();
        $updated = app(SaveGame::class)->handle($game, ['name' => 'Rolled back']);
        $this->paths[] = $updated->image;
        DB::rollBack();
        $this->assertTrue($disk->exists($original));
        $this->assertFalse($disk->exists($updated->image));
        $game->refresh();

        $game = app(SaveGame::class)->handle($game, ['name' => 'Renamed']);
        $this->paths[] = $game->image;
        $this->assertSame($contents, $disk->get($game->image));
        $this->assertFalse($disk->exists($original));
        $renamed = $game->image;
        $game = app(SaveGame::class)->handle($game, ['name' => 'Renamed', 'image' => UploadedFile::fake()->image('replacement.png', 10, 10)]);
        $this->paths[] = $game->image;
        $this->assertFalse($disk->exists($renamed));
        $game->delete();
        $this->assertFalse($disk->exists($game->image));
    }

    public function test_catalog_images_follow_the_database_lifecycle_on_real_storage(): void
    {
        $series = $this->series($this->catalog());
        $localization = $this->saveCatalog(new SeriesImage, [
            'series_id' => $series->id,
            'language_id' => $this->language()->id,
            'image' => UploadedFile::fake()->image('catalogue.png', 32, 16),
        ]);
        $this->paths[] = $original = $localization->image;
        $disk = CatalogImages::disk();
        $this->assertStringStartsWith('catalog/', $original);
        $this->assertSame('image/avif', $disk->mimeType($original));

        DB::beginTransaction();
        try {
            $updated = $this->saveCatalog($localization, ['image' => UploadedFile::fake()->image('rollback.png', 10, 10)]);
            $this->paths[] = $rolledBack = $updated->image;
            $this->assertTrue($disk->exists($rolledBack));
        } finally {
            DB::rollBack();
        }

        $this->assertSame($original, $localization->fresh()->image);
        $this->assertTrue($disk->exists($original));
        $this->assertFalse($disk->exists($rolledBack));

        $updated = $this->saveCatalog($localization, ['image' => UploadedFile::fake()->image('replacement.png', 12, 12)]);
        $this->paths[] = $replacement = $updated->image;
        $this->assertFalse($disk->exists($original));
        $this->assertTrue($disk->exists($replacement));

        $series->delete();
        $this->assertFalse($disk->exists($replacement));
    }
}
