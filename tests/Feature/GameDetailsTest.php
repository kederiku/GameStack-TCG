<?php

namespace Tests\Feature;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class GameDetailsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    public function test_game_details_are_optional(): void
    {
        $game = app(SaveGame::class)->handle(new Game, ['name' => 'Magic']);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'start_year' => null,
            'end_year' => null,
            'publisher' => null,
            'description' => null,
        ]);
    }

    public function test_game_details_are_saved_and_years_are_integers(): void
    {
        $game = app(SaveGame::class)->handle(new Game, [
            'name' => 'Un TCG',
            'start_year' => '2024',
            'end_year' => '2026',
            'publisher' => ' Un éditeur ',
            'description' => " Une description.\nUne seconde ligne. ",
        ])->fresh();

        $this->assertSame(2024, $game->start_year);
        $this->assertSame(2026, $game->end_year);
        $this->assertSame('Un éditeur', $game->publisher);
        $this->assertSame("Une description.\nUne seconde ligne.", $game->description);
    }

    public function test_omitted_details_are_preserved_and_blank_details_can_be_cleared(): void
    {
        $game = Game::factory()->create([
            'start_year' => 2024,
            'end_year' => 2026,
            'publisher' => 'Un éditeur',
            'description' => 'Une description.',
        ]);

        app(SaveGame::class)->handle($game, ['name' => 'Nouveau nom']);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'start_year' => 2024,
            'end_year' => 2026,
            'publisher' => 'Un éditeur',
            'description' => 'Une description.',
        ]);

        app(SaveGame::class)->handle($game, [
            'name' => 'Nouveau nom',
            'start_year' => '',
            'end_year' => null,
            'publisher' => '  ',
            'description' => '',
        ]);

        $this->assertDatabaseHas('games', [
            'id' => $game->id,
            'start_year' => null,
            'end_year' => null,
            'publisher' => null,
            'description' => null,
        ]);
    }

    #[DataProvider('validYears')]
    public function test_either_year_can_be_omitted_and_equal_years_are_allowed(?int $startYear, ?int $endYear): void
    {
        $game = app(SaveGame::class)->handle(new Game, [
            'name' => 'Un TCG',
            'start_year' => $startYear,
            'end_year' => $endYear,
        ])->fresh();

        $this->assertSame($startYear, $game->start_year);
        $this->assertSame($endYear, $game->end_year);
    }

    public static function validYears(): array
    {
        return [
            'creation only' => [1993, null],
            'end only' => [null, 2026],
            'same year' => [2026, 2026],
            'four digit boundaries' => [1000, 9999],
        ];
    }

    #[DataProvider('invalidDetails')]
    public function test_invalid_details_are_rejected_before_storing_an_image(array $details, string $field): void
    {
        try {
            app(SaveGame::class)->handle(new Game, [
                'name' => 'Un TCG',
                'image' => UploadedFile::fake()->image('logo.png'),
                ...$details,
            ]);
            $this->fail('Invalid game details were accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey($field, $exception->errors());
        }

        $this->assertDatabaseCount('games', 0);
        $this->assertSame([], Storage::disk('public')->allFiles('games'));
    }

    public static function invalidDetails(): array
    {
        return [
            'fractional creation year' => [['start_year' => 1993.5], 'start_year'],
            'full creation date' => [['start_year' => '1993-08-05'], 'start_year'],
            'short creation year' => [['start_year' => 999], 'start_year'],
            'large creation year' => [['start_year' => 10000], 'start_year'],
            'fractional end year' => [['end_year' => 2026.5], 'end_year'],
            'non numeric end year' => [['end_year' => 'inconnu'], 'end_year'],
            'short end year' => [['end_year' => 0], 'end_year'],
            'large end year' => [['end_year' => 10000], 'end_year'],
            'end before creation' => [['start_year' => 2024, 'end_year' => 2023], 'end_year'],
            'publisher too long' => [['publisher' => str_repeat('a', 256)], 'publisher'],
            'publisher is not text' => [['publisher' => ['Konami']], 'publisher'],
            'description is not text' => [['description' => ['Description']], 'description'],
        ];
    }

    public function test_updating_only_creation_still_checks_the_existing_end_year(): void
    {
        $game = Game::factory()->create(['start_year' => 2024, 'end_year' => 2026]);

        try {
            app(SaveGame::class)->handle($game, ['name' => $game->name, 'start_year' => 2027]);
            $this->fail('An inconsistent period was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('end_year', $exception->errors());
        }

        $this->assertSame(2024, $game->fresh()->start_year);
        $this->assertSame(2026, $game->fresh()->end_year);
    }

    public function test_creation_can_be_cleared_while_preserving_the_end_year(): void
    {
        $game = Game::factory()->create(['start_year' => 2024, 'end_year' => 2026]);

        $updated = app(SaveGame::class)->handle($game, ['name' => $game->name, 'start_year' => null]);

        $this->assertNull($updated->start_year);
        $this->assertSame(2026, $updated->end_year);
    }
}
