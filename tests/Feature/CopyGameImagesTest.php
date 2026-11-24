<?php

namespace Tests\Feature;

use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class CopyGameImagesTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('s3');
    }

    public function test_it_copies_visible_and_hidden_images_and_can_be_repeated(): void
    {
        $visible = $this->image();
        $hidden = $this->image(false);
        foreach (range(1, 2) as $_) {
            $this->artisan('games:copy-images-to-s3')->assertSuccessful();
        }

        foreach ([$visible, $hidden] as $game) {
            $this->assertSame('original bytes', Storage::disk('s3')->get($game->image));
            $this->assertSame('original bytes', Storage::disk('public')->get($game->image));
            $this->assertSame($game->image, $game->fresh()->image);
        }
        $this->assertCount(2, Storage::disk('s3')->allFiles());
        $this->assertSame('public', config('game-images.disk'));
    }

    public function test_simulation_does_not_write_anything(): void
    {
        $game = $this->image();
        $this->artisan('games:copy-images-to-s3 --dry-run')->assertSuccessful();
        $this->assertSame([], Storage::disk('s3')->allFiles());
        Storage::disk('public')->assertExists($game->image);
    }

    public function test_a_conflict_is_preserved_and_other_images_are_copied(): void
    {
        $conflict = $this->image();
        $other = $this->image();
        Storage::disk('s3')->put($conflict->image, 'different bytes');

        $this->artisan('games:copy-images-to-s3')->assertFailed();

        $this->assertSame('different bytes', Storage::disk('s3')->get($conflict->image));
        $this->assertSame('original bytes', Storage::disk('s3')->get($other->image));
        $this->assertSame('original bytes', Storage::disk('public')->get($conflict->image));
    }

    public function test_simulation_detects_a_conflict(): void
    {
        $game = $this->image();
        Storage::disk('s3')->put($game->image, 'different bytes');
        $this->artisan('games:copy-images-to-s3 --dry-run')->assertFailed();
        $this->assertSame('different bytes', Storage::disk('s3')->get($game->image));
    }

    public function test_missing_and_unmanaged_sources_fail_without_copying_them(): void
    {
        $game = $this->image();
        Storage::disk('public')->delete($game->image);
        Game::factory()->create(['image' => 'other/secret.avif']);
        Storage::disk('public')->put('other/secret.avif', 'not a managed image');

        $this->artisan('games:copy-images-to-s3')->assertFailed();

        $this->assertSame([], Storage::disk('s3')->allFiles());
        Storage::disk('public')->assertExists('other/secret.avif');
        $this->assertSame($game->image, $game->fresh()->image);
    }

    public function test_a_failed_write_keeps_the_source_and_reports_failure(): void
    {
        $game = $this->image();
        $disk = \Mockery::mock(Storage::disk('s3'))->makePartial();
        $disk->shouldReceive('put')->andReturn(false);
        Storage::set('s3', $disk);

        $this->artisan('games:copy-images-to-s3')->assertFailed();

        Storage::disk('public')->assertExists($game->image);
        $this->assertSame($game->image, $game->fresh()->image);
    }

    public function test_a_corrupt_new_copy_is_detected_and_removed_so_it_can_be_retried(): void
    {
        $game = $this->image();
        $real = Storage::disk('s3');
        $disk = \Mockery::mock($real)->makePartial();
        $disk->shouldReceive('put')->andReturnUsing(fn (string $path): bool => $real->put($path, 'corrupt'));
        Storage::set('s3', $disk);

        $this->artisan('games:copy-images-to-s3')->assertFailed();
        $real->assertMissing($game->image);
        $this->assertSame('original bytes', Storage::disk('public')->get($game->image));

        Storage::set('s3', $real);
        $this->artisan('games:copy-images-to-s3')->assertSuccessful();
        $this->assertSame('original bytes', $real->get($game->image));
    }

    private function image(bool $visible = true): Game
    {
        $path = 'games/'.Str::uuid().'/image.avif';
        Storage::disk('public')->put($path, 'original bytes');

        return Game::factory()->create(['image' => $path, 'is_visible' => $visible]);
    }
}
