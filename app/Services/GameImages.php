<?php

namespace App\Services;

use App\Models\Game;
use Illuminate\Database\Connection;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GameImages extends AvifImages
{
    protected const FALLBACK_FILENAME = 'jeu';

    protected function directory(): string
    {
        return 'games';
    }

    public function rename(string $path, string $name, Connection $connection): ?string
    {
        $disk = self::disk();

        if (! $this->isManagedPath($path) || ! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);

        if ($contents === null) {
            throw ValidationException::withMessages(['image' => __('validation.images.read-image')]);
        }

        return $this->write($contents, $name, $connection, pathinfo($path, PATHINFO_EXTENSION));
    }

    public function deleteUnused(?string $path, ?string $connection = null): void
    {
        if ($path === null || ! $this->isManagedPath($path) || Game::on($connection)->where('image', $path)->exists()) {
            return;
        }

        $disk = self::disk();

        // Each revision owns its directory. Never delete an arbitrary submitted path.
        if (! $disk->deleteDirectory(dirname($path))) {
            throw new RuntimeException(__('validation.images.delete-game', ['path' => $path]));
        }
    }

    public function isManagedPath(string $path): bool
    {
        return preg_match('/\Agames\/[a-f0-9-]{36}\/[a-z0-9-]+\.(?:avif|webp|svg)\z/', $path) === 1;
    }
}
