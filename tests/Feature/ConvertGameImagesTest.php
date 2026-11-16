<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\Concerns\ForwardOnlyDatabaseMigrations;
use Tests\TestCase;

class ConvertGameImagesTest extends TestCase
{
    use ForwardOnlyDatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('local');
    }

    public function test_dry_run_keeps_original_files_and_records_without_backups(): void
    {
        $game = $this->legacyGame()->fresh();
        $this->artisan('games:convert-images-to-avif --dry-run')->assertSuccessful();
        $this->assertSame($game->getAttributes(), $game->fresh()->getAttributes());
        $this->assertSame([$game->image], Storage::disk('public')->allFiles());
        $this->assertSame([], Storage::disk('local')->allFiles());
    }

    public function test_legacy_images_are_backed_up_and_converted_without_changing_game_details(): void
    {
        $games = [$this->legacyGame(), $this->legacyGame('svg')];
        $originals = array_map(fn (Game $game) => Storage::disk('public')->get($game->image), $games);
        $this->artisan('games:convert-images-to-avif')->assertSuccessful();

        foreach ($games as $index => $game) {
            $updated = $game->fresh();
            $this->assertSame($game->only(['name', 'slug', 'publisher', 'description', 'start_year', 'end_year', 'is_visible']), $updated->only(['name', 'slug', 'publisher', 'description', 'start_year', 'end_year', 'is_visible']));
            $this->assertStringEndsWith('.avif', $updated->image);
            $this->assertSame('image/avif', Storage::disk('public')->mimeType($updated->image));
            $this->assertNotFalse(imagecreatefromstring(Storage::disk('public')->get($updated->image)));
            $this->assertSame($originals[$index], Storage::disk('local')->get('game-images-before-avif/'.$game->image));
            Storage::disk('public')->assertMissing($game->image);
        }

        $files = Storage::disk('public')->allFiles();
        $backups = Storage::disk('local')->allFiles();
        $this->artisan('games:convert-images-to-avif')->expectsOutputToContain('0 image(s) convertie(s)')->assertSuccessful();
        $this->assertSame($files, Storage::disk('public')->allFiles());
        $this->assertSame($backups, Storage::disk('local')->allFiles());
    }

    public function test_missing_and_invalid_images_are_preserved_and_do_not_block_other_games(): void
    {
        $missing = $this->legacyGame();
        Storage::disk('public')->delete($missing->image);
        $invalid = $this->legacyGame();
        Storage::disk('public')->put($invalid->image, 'not an image');
        $valid = $this->legacyGame();

        $this->artisan('games:convert-images-to-avif')->assertFailed();
        $this->assertSame($missing->image, $missing->fresh()->image);
        $this->assertSame($invalid->image, $invalid->fresh()->image);
        $this->assertSame('not an image', Storage::disk('public')->get($invalid->image));
        $this->assertStringEndsWith('.avif', $valid->fresh()->image);
    }

    public function test_a_backup_failure_prevents_replacing_the_original(): void
    {
        $game = $this->legacyGame();
        $disk = \Mockery::mock(Storage::disk('local'))->makePartial();
        $disk->shouldReceive('put')->andReturn(false);
        Storage::set('local', $disk);

        $this->artisan('games:convert-images-to-avif')->assertFailed();
        $this->assertSame($game->image, $game->fresh()->image);
        $this->assertSame([$game->image], Storage::disk('public')->allFiles());
    }

    public function test_a_database_failure_preserves_the_original_and_cleans_up_the_new_image(): void
    {
        $game = $this->legacyGame();
        $contents = Storage::disk('public')->get($game->image);
        Game::updating(function (): never {
            throw new RuntimeException('Simulated database failure');
        });

        $this->artisan('games:convert-images-to-avif')->assertFailed();
        $this->assertSame($game->image, $game->fresh()->image);
        $this->assertSame([$game->image], Storage::disk('public')->allFiles());
        $this->assertSame($contents, Storage::disk('local')->get('game-images-before-avif/'.$game->image));
    }

    public function test_unmanaged_paths_are_not_converted_or_removed(): void
    {
        Storage::disk('public')->put('other/logo.webp', UploadedFile::fake()->image('logo.webp')->get());
        $game = Game::factory()->create(['image' => 'other/logo.webp']);
        $this->artisan('games:convert-images-to-avif')->assertFailed();
        $this->assertSame('other/logo.webp', $game->fresh()->image);
        Storage::disk('public')->assertExists('other/logo.webp');
    }

    public function test_a_different_existing_backup_prevents_conversion_without_overwriting_files(): void
    {
        $game = $this->legacyGame();
        $original = Storage::disk('public')->get($game->image);
        $backup = 'game-images-before-avif/'.$game->image;
        Storage::disk('local')->put($backup, 'A previous backup');

        $this->artisan('games:convert-images-to-avif')->assertFailed();

        $this->assertSame($game->image, $game->fresh()->image);
        $this->assertSame($original, Storage::disk('public')->get($game->image));
        $this->assertSame('A previous backup', Storage::disk('local')->get($backup));
        $this->assertSame([$game->image], Storage::disk('public')->allFiles());
    }

    public function test_conversion_can_resume_with_an_identical_existing_backup(): void
    {
        $game = $this->legacyGame();
        $original = Storage::disk('public')->get($game->image);
        $backup = 'game-images-before-avif/'.$game->image;
        Storage::disk('local')->put($backup, $original);

        $this->artisan('games:convert-images-to-avif')->assertSuccessful();

        $this->assertSame('image/avif', Storage::disk('public')->mimeType($game->fresh()->image));
        $this->assertSame($original, Storage::disk('local')->get($backup));
        Storage::disk('public')->assertMissing($game->image);
    }

    public function test_a_cancelled_save_keeps_the_original_and_backup_without_leaving_an_avif(): void
    {
        $game = $this->legacyGame();
        $original = Storage::disk('public')->get($game->image);
        Game::updating(fn (): bool => false);

        $this->artisan('games:convert-images-to-avif')->assertFailed();

        $this->assertSame($game->image, $game->fresh()->image);
        $this->assertSame([$game->image], Storage::disk('public')->allFiles());
        $this->assertSame($original, Storage::disk('local')->get('game-images-before-avif/'.$game->image));
    }

    private function legacyGame(string $extension = 'webp'): Game
    {
        $path = 'games/'.Str::uuid().'/legacy.'.$extension;
        $contents = $extension === 'svg'
            ? '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 50"><rect width="100" height="50" fill="blue"/></svg>'
            : UploadedFile::fake()->image('logo.webp', 64, 32)->get();
        Storage::disk('public')->put($path, $contents);

        return Game::factory()->create([
            'image' => $path, 'publisher' => 'An editor', 'description' => 'A game',
            'start_year' => 1993, 'end_year' => null, 'is_visible' => false,
        ]);
    }
}
