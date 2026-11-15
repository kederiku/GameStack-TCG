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
use RuntimeException;
use Tests\TestCase;

class GameImageStorageTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        config(['game-images.disk' => 's3']);
        Storage::fake('public');
        Storage::fake('local');
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

    public function test_legacy_conversion_reads_remote_contents_without_using_a_local_path(): void
    {
        $path = 'games/'.Str::uuid().'/legacy.webp';
        $contents = UploadedFile::fake()->image('legacy.webp', 20, 10)->get();
        Storage::disk('s3')->put($path, $contents);
        $game = Game::factory()->create(['image' => $path]);
        $remote = \Mockery::mock(Storage::disk('s3'))->makePartial();
        $remote->shouldReceive('path')->andThrow(new RuntimeException('Remote storage has no local path'));
        Storage::set('s3', $remote);

        $this->artisan('games:convert-images-to-avif')->assertSuccessful();

        $this->assertStringEndsWith('.avif', $game->fresh()->image);
        $this->assertSame('image/avif', $remote->mimeType($game->fresh()->image));
        $this->assertSame($contents, Storage::disk('local')->get('game-images-before-avif/'.$path));
        $remote->assertMissing($path);
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
