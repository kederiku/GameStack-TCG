<?php

namespace Tests\Integration;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use App\Services\GameImages;
use App\Support\PublicGames;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

// Explicit opt-in: php artisan test tests/Integration/GarageStorageTest.php
// Uses the dedicated testing database and removes only its own UUID objects.
class GarageStorageTest extends TestCase
{
    use DatabaseMigrations;

    /** @var list<string> */
    private array $paths = [];

    protected function setUp(): void
    {
        parent::setUp();
        config(['game-images.disk' => 's3']);
        Storage::fake('local');
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

    public function test_legacy_conversion_works_with_a_real_s3_object(): void
    {
        $path = 'games/'.Str::uuid().'/legacy.webp';
        $this->paths[] = $path;
        $contents = UploadedFile::fake()->image('legacy.webp', 32, 16)->get();
        $this->assertTrue(GameImages::disk()->put($path, $contents, 'public'));
        $game = Game::factory()->create(['image' => $path]);

        $this->artisan('games:convert-images-to-avif')->assertSuccessful();

        $converted = $game->fresh()->image;
        $this->paths[] = $converted;
        $this->assertSame('image/avif', GameImages::disk()->mimeType($converted));
        $this->assertFalse(GameImages::disk()->exists($path));
        $this->assertSame($contents, Storage::disk('local')->get('game-images-before-avif/'.$path));
    }
}
