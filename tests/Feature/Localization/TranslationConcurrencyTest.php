<?php

namespace Tests\Feature\Localization;

use App\Actions\Games\SaveGame;
use App\Models\Game;
use App\Models\Language;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\Process\Process;
use Tests\Concerns\BuildsCatalog;
use Tests\Concerns\UsesLocalizedSchema;
use Tests\TestCase;

class TranslationConcurrencyTest extends TestCase
{
    use BuildsCatalog;
    use UsesLocalizedSchema;

    public static function staleFullMaps(): iterable
    {
        foreach (['eloquent', 'game action', 'catalog action'] as $writer) {
            yield "$writer, exact historical text" => [$writer, '  original  ', '  original  '];
            yield "$writer, padded original text" => [$writer, 'Original', '  Original  '];
            yield "$writer, blank for existing null" => [$writer, null, '   '];
        }
    }

    #[DataProvider('staleFullMaps')]
    public function test_stale_full_map_preserves_a_concurrent_french_edit(string $writer, ?string $original, string $submitted): void
    {
        $this->createLocalizedSchema();
        $record = $writer === 'catalog action' ? $this->series($this->catalog()) : Game::factory()->create();
        DB::table($record->getTable())->where('id', $record->id)->update(['description' => json_encode(['fr' => $original, 'en' => 'Old'], JSON_THROW_ON_ERROR)]);
        $stale = $record->fresh();
        $record->refresh()->setTranslation('description', 'fr', 'Français modifié')->save();

        $translations = ['fr' => $submitted, 'en' => '  New  '];
        if ($writer === 'eloquent') {
            $stale->setTranslations('description', $translations)->save();
        } elseif ($stale instanceof Game) {
            app(SaveGame::class)->handle($stale, ['name' => $stale->name, 'description' => $translations]);
        } else {
            $this->saveCatalog($stale, ['description' => $translations]);
        }

        $this->assertSame(['en' => 'New', 'fr' => 'Français modifié'], $record->fresh()->getTranslations('description'));
    }

    public static function races(): array
    {
        return ['game write wins' => [true, true], 'game delete wins' => [true, false], 'series write wins' => [false, true], 'series delete wins' => [false, false]];
    }

    #[DataProvider('races')]
    public function test_language_delete_and_translation_write_cannot_leave_an_orphan(bool $game, bool $writeWins): void
    {
        $this->createLocalizedSchema();
        $record = $game ? Game::factory()->create() : $this->series($this->catalog());
        $language = $this->language('ja', 'Japanese');
        $connection = DB::connection();
        $process = new Process([PHP_BINARY, base_path('tests/Fixtures/Localization/concurrent-write.php'), $this->localizedSchema, $writeWins ? 'delete' : 'write', $record::class, (string) $record->id, (string) $language->id], base_path(), ['APP_ENV' => 'testing', 'DB_DATABASE' => 'gamestackr_testing']);
        $process->setTimeout(15);
        $connection->beginTransaction();
        try {
            if ($writeWins) {
                $record->setTranslation('description', 'ja', null)->save();
            } else {
                $language->delete();
            }
            $process->start();
            $deadline = microtime(true) + 5;
            $pid = null;
            $blocked = false;
            while ($process->isRunning() && microtime(true) < $deadline) {
                $output = $process->getOutput();
                if (str_contains($output, "\n")) {
                    $pid = json_decode(explode("\n", $output)[0], true, flags: JSON_THROW_ON_ERROR)['pid'];
                    $blocked = (bool) $connection->selectOne('SELECT ?::int = ANY(pg_blocking_pids(?)) AS blocked', [$connection->selectOne('SELECT pg_backend_pid() AS pid')->pid, $pid])->blocked;
                    if ($blocked) {
                        break;
                    }
                }
                usleep(10000);
            }
            $this->assertNotNull($pid, $process->getErrorOutput());
            $this->assertNotSame($connection->selectOne('SELECT pg_backend_pid() AS pid')->pid, $pid);
            $this->assertTrue($blocked, 'The second process must wait on the first transaction: '.$process->getOutput().$process->getErrorOutput());
            $connection->commit();
            $process->wait();
            $this->assertTrue($process->isSuccessful(), $process->getErrorOutput());
            $this->assertStringContainsString('rejected:', $process->getOutput());
            $this->assertSame($writeWins, Language::whereKey($language->id)->exists());
            $this->assertSame($writeWins ? ['ja' => null] : [], $record->fresh()->getTranslations('description'));
        } finally {
            if ($connection->transactionLevel() > 0) {
                $connection->rollBack();
            }
            if ($process->isRunning()) {
                $process->stop();
            }
        }
    }
}
