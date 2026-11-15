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
            try {
                $changed = $game->getConnection()->transaction(function () use ($game, $images, $dryRun): bool {
                    $record = Game::query()->lockForUpdate()->find($game->id);
                    $path = $record?->image;

                    if ($record === null || $path === null || str_ends_with($path, '.avif')) {
                        return false;
                    }

                    $disk = Storage::disk('public');

                    if (! $images->isManagedPath($path) || ! $disk->exists($path)) {
                        throw new RuntimeException('Image absente ou chemin non géré : '.$path);
                    }

                    $upload = new UploadedFile($disk->path($path), basename($path), test: true);
                    Validator::make(['image' => $upload], [
                        'image' => ['required', 'image:allow_svg', 'mimetypes:image/webp,image/svg+xml', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
                    ])->validate();

                    if ($dryRun) {
                        $this->line("À convertir : #{$record->id} {$record->name} — {$path}");

                        return true;
                    }

                    $contents = $disk->get($path);
                    $backup = 'game-images-before-avif/'.$path;
                    $private = Storage::disk('local');

                    // Verify existing backups too: never replace a record without its exact original.
                    if ($contents === null || (! $private->exists($backup) && ! $private->put($backup, $contents, 'private')) || $private->get($backup) !== $contents) {
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
            }
        }

        $this->info($dryRun ? "{$converted} image(s) à convertir ; {$failed} erreur(s)." : "{$converted} image(s) convertie(s) ; {$failed} erreur(s).");

        if (! $dryRun && $converted > 0) {
            $this->info('Originaux conservés sur le disque privé : game-images-before-avif/.');
        }

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
