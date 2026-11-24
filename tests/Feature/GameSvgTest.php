<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class GameSvgTest extends TestCase
{
    use DatabaseMigrations;

    private const SVG = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="red"/></svg>';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_svg_becomes_avif_with_transparency_and_the_game_filename(): void
    {
        $game = $this->createSvg();
        $this->assertStringEndsWith('/pokemon.avif', $game->image);
        $this->assertSame('image/avif', Storage::disk('public')->mimeType($game->image));
        $decoded = imagecreatefromavif(Storage::disk('public')->path($game->image));
        $this->assertSame([2048, 2048], [imagesx($decoded), imagesy($decoded)]);
        $this->assertSame(127, imagecolorsforindex($decoded, imagecolorat($decoded, 0, 0))['alpha']);
        $center = imagecolorsforindex($decoded, imagecolorat($decoded, 1024, 1024));
        $this->assertGreaterThan(240, $center['red']);
        $this->assertLessThan(10, $center['green']);
        $this->assertSame(0, $center['alpha']);
    }

    public function test_svg_rename_preserves_its_format_and_deletion_removes_it(): void
    {
        $game = $this->createSvg();
        $old = $game->image;
        $contents = Storage::disk('public')->get($old);
        $game = app(SaveGame::class)->handle($game, ['name' => 'Magic']);

        $this->assertStringEndsWith('/magic.avif', $game->image);
        $this->assertSame($contents, Storage::disk('public')->get($game->image));
        Storage::disk('public')->assertMissing($old);
        $game->delete();
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_replacing_svg_and_png_uploads_cleans_each_previous_file(): void
    {
        $game = $this->createSvg();
        $svg = $game->image;
        $game = app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->image('logo.png')]);
        $webp = $game->image;
        $this->assertStringEndsWith('.avif', $webp);
        Storage::disk('public')->assertMissing($svg);

        $game = app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->createWithContent('logo.svg', self::SVG)]);
        $this->assertStringEndsWith('.avif', $game->image);
        Storage::disk('public')->assertMissing($webp);
        app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => null]);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_rollback_removes_a_new_svg_and_keeps_the_previous_image(): void
    {
        $game = $this->createSvg();
        $old = $game->image;
        DB::beginTransaction();
        app(SaveGame::class)->handle($game, ['name' => 'Renamed', 'image' => UploadedFile::fake()->createWithContent('logo.svg', self::SVG)]);
        DB::rollBack();

        $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
        $this->assertSame($old, $game->fresh()->image);
    }

    public function test_svg_active_content_and_external_references_do_not_survive_conversion(): void
    {
        $localImage = UploadedFile::fake()->image('private.png');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 50" onload="alert(1)"><script>alert(2)</script><foreignObject><div>unsafe</div></foreignObject><image href="https://example.com/tracker.png"/><image href="file://'.htmlspecialchars($localImage->getPathname()).'" width="100" height="50"/><circle cx="50" cy="25" r="10" fill="red"/></svg>';
        $game = $this->createSvg($svg);
        $contents = Storage::disk('public')->get($game->image);
        $decoded = imagecreatefromstring($contents);

        $this->assertSame('image/avif', Storage::disk('public')->mimeType($game->image));
        $this->assertSame([2048, 1024], [imagesx($decoded), imagesy($decoded)]);
        $this->assertGreaterThan(240, imagecolorsforindex($decoded, imagecolorat($decoded, 1024, 512))['red']);
        $this->assertSame(127, imagecolorsforindex($decoded, imagecolorat($decoded, 0, 0))['alpha']);
        $this->assertStringNotContainsString('<script', $contents);
    }

    public function test_malformed_svg_and_dtd_entities_are_rejected_without_storing_files(): void
    {
        foreach ([
            '<svg xmlns="http://www.w3.org/2000/svg"><path>',
            '<!DOCTYPE svg [<!ENTITY xxe SYSTEM "file:///etc/passwd">]><svg xmlns="http://www.w3.org/2000/svg"><text>&xxe;</text></svg>',
            '<html xmlns="http://www.w3.org/1999/xhtml"><script>alert(1)</script></html>',
        ] as $contents) {
            try {
                $this->createSvg($contents);
                $this->fail('Invalid SVG was accepted.');
            } catch (ValidationException $exception) {
                $this->assertArrayHasKey('image', $exception->errors());
            }
        }
        $this->assertDatabaseCount('games', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_svg_uploads_keep_the_five_megabyte_limit(): void
    {
        $this->expectException(ValidationException::class);
        app(SaveGame::class)->handle(new Game, ['name' => 'Large', 'image' => UploadedFile::fake()->createWithContent('logo.svg', self::SVG)->size(5121)]);
    }

    public function test_oversized_embedded_rasters_are_rejected_without_replacing_the_original(): void
    {
        $game = $this->createSvg();
        $old = $game->image;

        // Highly compressed, valid PNG: large intrinsic dimensions without a large fixture.
        $png = $this->monochromePng(8192);
        $svg = $this->embeddedSvg($png);

        try {
            app(SaveGame::class)->handle($game, ['name' => 'Changed', 'image' => UploadedFile::fake()->createWithContent('large.svg', $svg)]);
            $this->fail('An SVG with an oversized embedded raster was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('image', $exception->errors());
        }

        $this->assertSame('Pokémon', $game->fresh()->name);
        $this->assertSame($old, $game->fresh()->image);
        $this->assertSame([$old], Storage::disk('public')->allFiles('games'));
    }

    public function test_small_embedded_rasters_are_rendered_in_the_avif(): void
    {
        $game = $this->createSvg($this->embeddedSvg($this->monochromePng(64)));
        $decoded = imagecreatefromavif(Storage::disk('public')->path($game->image));
        $pixel = imagecolorsforindex($decoded, imagecolorat($decoded, 1024, 1024));
        $this->assertSame(0, $pixel['alpha']);
        $this->assertLessThan(10, $pixel['red']);
    }

    public function test_many_embedded_rasters_cannot_exceed_the_combined_pixel_budget(): void
    {
        $image = '<image width="100" height="100" href="data:image/png;base64,'.base64_encode($this->monochromePng(2048)).'"/>';
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">'.str_repeat($image, 5).'</svg>';
        $this->expectException(ValidationException::class);
        $this->createSvg($svg);
    }

    private function embeddedSvg(string $png): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 100 100"><image width="100" height="100" xlink:href="data:image/png;base64,'.base64_encode($png).'"/></svg>';
    }

    private function monochromePng(int $size): string
    {
        $chunk = fn (string $type, string $data): string => pack('N', strlen($data)).$type.$data.pack('N', crc32($type.$data));

        return "\x89PNG\r\n\x1a\n"
            .$chunk('IHDR', pack('NNCCCCC', $size, $size, 1, 0, 0, 0, 0))
            .$chunk('IDAT', gzcompress(str_repeat("\0", (intdiv($size + 7, 8) + 1) * $size), 9))
            .$chunk('IEND', '');
    }

    private function createSvg(string $contents = self::SVG): Game
    {
        return app(SaveGame::class)->handle(new Game, [
            'name' => 'Pokémon',
            'image' => UploadedFile::fake()->createWithContent('logo.SVG', $contents),
        ]);
    }
}
