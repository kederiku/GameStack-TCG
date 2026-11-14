<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use DOMDocument;
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

    public function test_svg_stays_vector_with_the_game_filename(): void
    {
        $game = $this->createSvg();
        $this->assertStringEndsWith('/pokemon.svg', $game->image);
        $this->assertSame('image/svg+xml', Storage::disk('public')->mimeType($game->image));
        $document = new DOMDocument;
        $this->assertTrue($document->loadXML(Storage::disk('public')->get($game->image)));
        $this->assertSame('0 0 100 100', $document->documentElement->getAttribute('viewBox'));
        $this->assertSame('40', $document->getElementsByTagName('circle')->item(0)->getAttribute('r'));
    }

    public function test_svg_rename_preserves_its_format_and_deletion_removes_it(): void
    {
        $game = $this->createSvg();
        $old = $game->image;
        $contents = Storage::disk('public')->get($old);
        $game = app(SaveGame::class)->handle($game, ['name' => 'Magic']);

        $this->assertStringEndsWith('/magic.svg', $game->image);
        $this->assertSame($contents, Storage::disk('public')->get($game->image));
        Storage::disk('public')->assertMissing($old);
        $game->delete();
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public function test_replacing_between_svg_and_webp_cleans_each_previous_file(): void
    {
        $game = $this->createSvg();
        $svg = $game->image;
        $game = app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->image('logo.png')]);
        $webp = $game->image;
        $this->assertStringEndsWith('.webp', $webp);
        Storage::disk('public')->assertMissing($svg);

        $game = app(SaveGame::class)->handle($game, ['name' => 'Pokémon', 'image' => UploadedFile::fake()->createWithContent('logo.svg', self::SVG)]);
        $this->assertStringEndsWith('.svg', $game->image);
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

    public function test_svg_active_content_and_external_references_are_removed(): void
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" onload="alert(1)"><script>alert(2)</script><foreignObject><div>unsafe</div></foreignObject><image href="https://example.com/tracker.png"/><circle r="10"/></svg>';
        $game = $this->createSvg($svg);
        $contents = Storage::disk('public')->get($game->image);

        $this->assertStringNotContainsString('onload', $contents);
        $this->assertStringNotContainsString('<script', $contents);
        $this->assertStringNotContainsString('foreignObject', $contents);
        $this->assertStringNotContainsString('https://example.com', $contents);
        $this->assertStringContainsString('<circle', $contents);
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

    private function createSvg(string $contents = self::SVG): Game
    {
        return app(SaveGame::class)->handle(new Game, [
            'name' => 'Pokémon',
            'image' => UploadedFile::fake()->createWithContent('logo.SVG', $contents),
        ]);
    }
}
