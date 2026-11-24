<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use App\Support\PublicGames;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameImageStorageTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['game-images.disk' => 's3']);
        Storage::fake('public');
        Storage::fake('s3', ['url' => 'http://images.localhost:3902']);
    }

    public function test_the_configured_disk_handles_the_whole_image_lifecycle(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Storage test', 'image' => UploadedFile::fake()->image('logo.png')]);
        $original = $game->image;
        Storage::disk('s3')->assertExists($original);
        $this->assertSame('http://images.localhost:3902/'.$original, PublicGames::present($game)['imageUrl']);

        $game = app(SaveGame::class)->handle($game, ['name' => 'Renamed']);
        Storage::disk('s3')->assertMissing($original);
        $this->assertStringEndsWith('/renamed.avif', $game->image);
        $renamed = $game->image;

        $game = app(SaveGame::class)->handle($game, ['name' => 'Renamed', 'image' => UploadedFile::fake()->image('replacement.png')]);
        Storage::disk('s3')->assertMissing($renamed);
        Storage::disk('s3')->assertExists($game->image);
        $game->delete();

        $this->assertSame([], Storage::disk('s3')->allFiles());
        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_a_failed_remote_write_preserves_the_previous_image(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Storage test', 'image' => UploadedFile::fake()->image('logo.png')]);
        $path = $game->image;
        $disk = Storage::disk('s3');
        $failing = \Mockery::mock($disk)->makePartial();
        $failing->shouldReceive('put')->andReturn(false);
        Storage::set('s3', $failing);

        try {
            app(SaveGame::class)->handle($game, ['name' => 'Changed', 'image' => UploadedFile::fake()->image('new.png')]);
            $this->fail('Failed S3 write was accepted.');
        } catch (ValidationException) {
            $this->assertSame('Storage test', $game->fresh()->name);
            $this->assertSame($path, $game->fresh()->image);
            $this->assertSame([$path], $disk->allFiles());
        }
    }

    #[DataProvider('unsupportedStoredExtensions')]
    public function test_renaming_a_game_leaves_unmanaged_files_untouched(string $extension): void
    {
        $path = 'games/'.Str::uuid().'/logo.'.$extension;
        $disk = Storage::disk('s3');
        $disk->put($path, 'Unmanaged file');
        $game = Game::factory()->create(['name' => 'Original', 'image' => $path]);

        $renamed = app(SaveGame::class)->handle($game, ['name' => 'Renamed'])->refresh();

        $this->assertSame('Renamed', $renamed->name);
        $this->assertNull($renamed->image);
        $this->assertSame([$path], $disk->allFiles());
        $this->assertSame('Unmanaged file', $disk->get($path));
    }

    public static function unsupportedStoredExtensions(): array
    {
        return ['WebP' => ['webp'], 'SVG' => ['svg']];
    }
}
