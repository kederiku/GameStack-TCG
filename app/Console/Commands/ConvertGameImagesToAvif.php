<?php

namespace App\Console\Commands;

use App\Models\Game;
use App\Services\GameImages;
use Illuminate\Console\Command;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use RuntimeException;
use Throwable;

class ConvertGameImagesToAvif extends Command
{
    protected $signature = 'games:convert-images-to-avif {--dry-run : Afficher les images à convertir sans les modifier}';

    protected $description = 'Convertir les images existantes en AVIF avec sauvegarde privée des originaux';

    public function handle(GameImages $images): int
    {
        $converted = 0;
        $failed = 0;
        $dryRun = (bool) $this->option('dry-run');

        foreach (Game::query()->whereNotNull('image')->lazyById() as $game) {
            $temporary = null;
            try {
                $changed = $game->getConnection()->transaction(function () use ($game, $images, $dryRun, &$temporary): bool {
                    $record = Game::query()->lockForUpdate()->find($game->id);
                    $path = $record?->image;

                    if ($record === null || $path === null || str_ends_with($path, '.avif')) {
                        return false;
                    }

                    $disk = GameImages::disk();

                    if (! $images->isManagedPath($path) || ! $disk->exists($path)) {
                        throw new RuntimeException('Image absente ou chemin non géré : '.$path);
                    }

                    $contents = $disk->get($path);
                    $temporary = tempnam(sys_get_temp_dir(), 'game-image-');

                    if ($contents === null || $temporary === false || file_put_contents($temporary, $contents) !== strlen($contents)) {
                        throw new RuntimeException('Impossible de préparer l’image pour conversion : '.$path);
                    }

                    $upload = new UploadedFile($temporary, basename($path), test: true);
                    Validator::make(['image' => $upload], [
                        'image' => ['required', 'image:allow_svg', 'mimetypes:image/webp,image/svg+xml', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
                    ])->validate();

                    if ($dryRun) {
                        $this->line("À convertir : #{$record->id} {$record->name} — {$path}");

                        return true;
                    }

                    $backup = 'game-images-before-avif/'.$path;
                    $private = Storage::disk('local');

                    // Verify existing backups too: never replace a record without its exact original.
                    if ((! $private->exists($backup) && ! $private->put($backup, $contents, 'private')) || $private->get($backup) !== $contents) {
                        throw new RuntimeException('Impossible de sauvegarder l’original : '.$path);
                    }

                    $record->image = $images->store($upload, $record->name, $record->getConnection());

                    if (! $record->save()) {
                        throw new RuntimeException('L’enregistrement du jeu a été annulé.');
                    }

                    $this->line("Converti : #{$record->id} {$record->name} — {$record->image}");

                    return true;
                });
                $converted += (int) $changed;
            } catch (Throwable $exception) {
                $failed++;
                $this->error("Jeu #{$game->id} : {$exception->getMessage()}");
                report($exception);
            } finally {
                if (is_string($temporary) && is_file($temporary)) {
                    unlink($temporary);
                }
            }
        }

        $this->info($dryRun ? "{$converted} image(s) à convertir ; {$failed} erreur(s)." : "{$converted} image(s) convertie(s) ; {$failed} erreur(s).");

        if (! $dryRun && $converted > 0) {
            $this->info('Originaux conservés sur le disque privé : game-images-before-avif/.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
