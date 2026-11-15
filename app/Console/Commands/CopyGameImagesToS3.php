<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameImages;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CopyGameImagesToS3 extends Command
{
    protected $signature = 'games:copy-images-to-s3 {--dry-run : Vérifier les images sans les copier}';

    protected $description = 'Copier et vérifier les images locales vers S3, sans supprimer les sources ni changer le disque actif';

    public function handle(GameImages $images): int
    {
        $source = Storage::disk('public');
        $target = Storage::disk('s3');
        $dryRun = (bool) $this->option('dry-run');
        $copied = $identical = $failed = 0;

        foreach (Game::query()->whereNotNull('image')->select('image')->distinct()->cursor() as $game) {
            $path = $game->image;
            $attempted = false;

            try {
                if ($path === null || ! $images->isManagedPath($path) || ! $source->exists($path)) {
                    throw new RuntimeException('Source absente ou chemin non géré.');
                }

                $contents = $source->get($path);
                if ($contents === null) {
                    throw new RuntimeException('Impossible de lire la source.');
                }
                $hash = hash('sha256', $contents);

                if ($target->exists($path)) {
                    $existing = $target->get($path);
                    if ($existing === null || ! hash_equals($hash, hash('sha256', $existing))) {
                        throw new RuntimeException('Conflit : la destination est différente, aucun écrasement.');
                    }
                    $identical++;

                    continue;
                }

                if (! $dryRun) {
                    // Migration requires writers to be stopped. Only clean objects
                    // created by this attempt; never touch a pre-existing conflict.
                    $attempted = true;
                    if (! $target->put($path, $contents, ['visibility' => 'public', 'ContentType' => $source->mimeType($path) ?: 'application/octet-stream'])) {
                        throw new RuntimeException('Échec de la copie.');
                    }
                    $copiedContents = $target->get($path);
                    if ($copiedContents === null || ! hash_equals($hash, hash('sha256', $copiedContents))) {
                        throw new RuntimeException('La vérification de la copie a échoué.');
                    }
                }

                $copied++;
                $this->line(($dryRun ? 'À copier : ' : 'Copié et vérifié : ').$path);
            } catch (Throwable $exception) {
                if ($attempted && $path !== null) {
                    rescue(function () use ($target, $path): void {
                        if (! $target->delete($path)) {
                            throw new RuntimeException('Impossible de nettoyer la copie incomplète : '.$path);
                        }
                    });
                }
                $failed++;
                $this->error(($path ?? '(sans chemin)').' : '.$exception->getMessage());
                report($exception);
            }
        }

        $this->info("{$copied} image(s) ".($dryRun ? 'à copier' : 'copiée(s)')." ; {$identical} identique(s) ; {$failed} erreur(s).");
        $this->info('Sources conservées. Le disque actif est inchangé.');

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
