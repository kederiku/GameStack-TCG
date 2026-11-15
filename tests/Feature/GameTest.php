<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\TestCase;

class GameTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_a_game_can_be_created_without_an_image_and_gets_a_slug(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => ' Pokémon ']);

        $this->assertSame('Pokémon', $game->name);
        $this->assertSame('pokemon', $game->slug);
        $this->assertNull($game->image);
        $this->assertDatabaseHas('games', ['id' => $game->id, 'slug' => 'pokemon']);
    }

    public function test_an_upload_is_reencoded_as_avif_with_the_game_name(): void
    {
        $game = $this->createWithImage();

        $this->assertStringEndsWith('/pokemon.avif', $game->image);
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($game->image));
        $this->assertSame([64, 32], array_slice(getimagesize(Storage::disk('public')->path($game->image)), 0, 2));
    }

    public function test_replacement_removes_the_old_image_and_uses_a_fresh_url(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;

        $game = app(SaveGame::class)->handle($game, ['name' => $game->name, 'image' => UploadedFile::fake()->image('new.jpg')]);

        $this->assertNotSame($old, $game->image);
        Storage::disk('public')->assertMissing($old);
        Storage::disk('public')->assertExists($game->image);
        $this->assertCount(1, Storage::disk('public')->allFiles('games'));
    }

    public function test_renaming_a_game_renames_its_image_but_preserves_its_slug(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        $contents = Storage::disk('public')->get($old);

        $game = app(SaveGame::class)->handle($game, ['name' => 'Magic: The Gathering']);

        $this->assertSame('pokemon', $game->slug);
        $this->assertStringEndsWith('/magic-the-gathering.avif', $game->image);
        $this->assertSame($contents, Storage::disk('public')->get($game->image));
        Storage::disk('public')->assertMissing($old);
    }

    public function test_removing_an_image_keeps_the_game(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        $game = app(SaveGame::class)->handle($game, ['name' => $game->name, 'image' => null]);

        $this->assertNull($game->fresh()->image);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_deleting_a_game_cleans_up_its_image(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        $game->delete();

        $this->assertDatabaseMissing('games', ['id' => $game->id]);
        Storage::disk('public')->assertMissing($old);
    }

    public function test_deleting_a_stale_instance_removes_the_current_image(): void
    {
        $stale = $this->createWithImage();
        $updated = app(SaveGame::class)->handle($stale, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->image('new.png')]);

        $stale->delete();

        $this->assertDatabaseMissing('games', ['id' => $updated->id]);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_rollback_preserves_the_previous_image_and_removes_the_new_one(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        DB::beginTransaction();
        $updated = app(SaveGame::class)->handle($game, ['name' => 'Renamed']);
        Storage::disk('public')->assertExists([$old, $updated->image]);
        DB::rollBack();

        $this->assertSame($old, $game->fresh()->image);
        $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
    }

    public function test_rollback_of_deletion_keeps_the_image(): void
    {
        $game = $this->createWithImage();
        DB::beginTransaction();
        $game->delete();
        DB::rollBack();

        Storage::disk('public')->assertExists($game->image);
        $this->assertDatabaseHas('games', ['id' => $game->id]);
    }

    public function test_duplicate_slugs_are_rejected_before_storing_any_image(): void
    {
        $this->createWithImage();
        try {
            $this->createWithImage();
            $this->fail('Duplicate slug was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('slug', $exception->errors());
        }
        $this->assertCount(1, Storage::disk('public')->allFiles('games'));
    }

    public function test_identical_names_do_not_share_image_paths(): void
    {
        $first = $this->createWithImage();
        $second = app(SaveGame::class)->handle(new Game, ['name' => 'Pokémon', 'slug' => 'pokemon-tcg', 'image' => UploadedFile::fake()->image('logo.png')]);
        $first->delete();

        Storage::disk('public')->assertExists($second->image);
    }

    public function test_an_image_path_cannot_be_taken_from_another_game(): void
    {
        $first = $this->createWithImage();
        $this->expectException(ValidationException::class);
        app(SaveGame::class)->handle(new Game, ['name' => 'Other', 'image' => $first->image]);
    }

    public function test_invalid_images_are_rejected_without_modifying_the_game(): void
    {
        $game = $this->createWithImage();
        foreach ([UploadedFile::fake()->createWithContent('fake.png', '<?php echo 1;'), UploadedFile::fake()->createWithContent('fake.avif', 'not an image'), UploadedFile::fake()->image('wide.png', 4097, 1), UploadedFile::fake()->image('large.png')->size(5121)] as $upload) {
            try {
                app(SaveGame::class)->handle($game, ['name' => 'Changed', 'image' => $upload]);
                $this->fail('Invalid image was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
        $this->assertSame('Pokémon', $game->fresh()->name);
        Storage::disk('public')->assertExists($game->image);
    }

    public function test_a_failed_database_save_leaves_no_new_file(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        Game::updating(function (): never {
            throw new RuntimeException('Simulated database failure');
        });

        try {
            app(SaveGame::class)->handle($game, ['name' => 'Changed', 'image' => UploadedFile::fake()->image('new.png')]);
            $this->fail('The save should fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated database failure', $exception->getMessage());
        }
        $this->assertSame($old, $game->fresh()->image);
        $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
    }

    private function createWithImage(): Game
    {
        return app(SaveGame::class)->handle(new Game, [
            'name' => 'Pokémon',
            'image' => UploadedFile::fake()->image('untrusted-name.png', 64, 32),
        ]);
    }

    public function test_png_transparency_is_preserved_in_avif(): void
    {
        $image = imagecreatetruecolor(2, 2);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 255, 0, 0, 127));
        ob_start();
        imagepng($image);
        $upload = UploadedFile::fake()->createWithContent('transparent.png', ob_get_clean());

        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Transparent', 'image' => $upload]);
        $decoded = imagecreatefromavif(Storage::disk('public')->path($game->image));

        $this->assertSame(127, imagecolorsforindex($decoded, imagecolorat($decoded, 0, 0))['alpha']);
    }

    public function test_jpeg_exif_orientation_is_applied(): void
    {
        $jpeg = UploadedFile::fake()->image('rotated.jpg', 40, 20)->get();
        // Little-endian TIFF with one IFD entry: Orientation (0x0112), SHORT, 6.
        $exif = "Exif\0\0II\x2a\0\x08\0\0\0\x01\0\x12\x01\x03\0\x01\0\0\0\x06\0\0\0\0\0\0\0";
        $jpeg = substr($jpeg, 0, 2)."\xff\xe1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2);
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Rotated', 'image' => UploadedFile::fake()->createWithContent('rotated.jpg', $jpeg)]);

        $this->assertSame([20, 40], array_slice(getimagesize(Storage::disk('public')->path($game->image)), 0, 2));
    }

    public function test_long_and_non_latin_names_have_safe_filenames(): void
    {
        foreach ([str_repeat('É', 255), '🎴'] as $index => $name) {
            $game = app(SaveGame::class)->handle(new Game, ['name' => $name, 'slug' => 'game-'.$index, 'image' => UploadedFile::fake()->image('logo.webp')]);
            $this->assertLessThanOrEqual(185, strlen(basename($game->image)));
            Storage::disk('public')->assertExists($game->image);
        }
        $this->assertStringEndsWith('/jeu.avif', $game->image);
    }

    public function test_a_stale_edit_cannot_restore_a_replaced_image(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        $updated = app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->image('new.png')]);

        try {
            app(SaveGame::class)->handle($game, ['name' => 'Stale', 'image' => $old]);
            $this->fail('Stale path was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }
        $this->assertSame($updated->image, $game->fresh()->image);
        Storage::disk('public')->assertExists($updated->image);
    }

    public function test_a_storage_failure_preserves_the_game_and_its_previous_image(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        $disk = Storage::disk('public');
        $failingDisk = \Mockery::mock($disk)->makePartial();
        $failingDisk->shouldReceive('put')->andReturn(false);
        Storage::set('public', $failingDisk);

        try {
            app(SaveGame::class)->handle($game, ['name' => 'Changed', 'image' => UploadedFile::fake()->image('new.png')]);
            $this->fail('Storage failure was ignored.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }
        $this->assertSame('Pokémon', $game->fresh()->name);
        $this->assertSame([$old], $disk->allFiles('games'));
    }

    public function test_a_game_with_an_already_missing_image_can_be_renamed_and_deleted(): void
    {
        $game = $this->createWithImage();
        Storage::disk('public')->delete($game->image);
        $game = app(SaveGame::class)->handle($game, ['name' => 'New name']);

        $this->assertNull($game->image);
        $this->assertTrue($game->delete());
    }

    public function test_repeated_changes_inside_a_rolled_back_transaction_leave_only_the_original(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        DB::beginTransaction();
        $updated = app(SaveGame::class)->handle($game, ['name' => 'First']);
        app(SaveGame::class)->handle($updated, ['name' => 'Second']);
        DB::rollBack();

        $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
    }

    public function test_a_cancelled_save_rolls_back_details_and_removes_the_prepared_image(): void
    {
        $game = $this->createWithImage();
        $old = $game->image;
        Game::updating(fn (): bool => false);

        try {
            app(SaveGame::class)->handle($game, [
                'name' => 'Changed', 'is_visible' => false,
                'image' => UploadedFile::fake()->image('replacement.png'),
            ]);
            $this->fail('A cancelled save was reported as successful.');
        } catch (RuntimeException) {
            $this->assertSame('Pokémon', $game->fresh()->name);
            $this->assertTrue($game->fresh()->is_visible);
            $this->assertSame($old, $game->fresh()->image);
            $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
        }
    }
}
