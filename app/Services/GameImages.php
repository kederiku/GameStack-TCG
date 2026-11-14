<?php

namespace App\Services;

use App\Models\Game;
use enshrined\svgSanitize\Sanitizer;
use GdImage;
use Illuminate\Database\Connection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class GameImages
{
    public function store(UploadedFile $upload, string $name, Connection $connection): string
    {
        $contents = $upload->get();

        if (is_string($contents) && $upload->getMimeType() === 'image/svg+xml') {
            $sanitizer = new Sanitizer;
            $sanitizer->removeRemoteReferences(true);
            $svg = $sanitizer->sanitize($contents);

            if ($svg === false || $svg === '') {
                throw ValidationException::withMessages(['image' => 'Ce fichier SVG est invalide ou ne peut pas être nettoyé.']);
            }

            return $this->write($svg, $name, $connection, 'svg');
        }

        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw ValidationException::withMessages(['image' => 'Cette image est illisible ou non prise en charge.']);
        }

        if ($upload->getMimeType() === 'image/jpeg') {
            $exif = @exif_read_data($upload->getPathname());
            $image = $this->orient($image, (int) ($exif['Orientation'] ?? 1));
        }

        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        ob_start();
        try {
            $encoded = imagewebp($image, null, 85);
            $webp = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        if (! $encoded || ! is_string($webp) || $webp === '') {
            throw ValidationException::withMessages(['image' => 'La conversion WebP a échoué.']);
        }

        return $this->write($webp, $name, $connection);
    }

    public function rename(string $path, string $name, Connection $connection): ?string
    {
        $disk = Storage::disk('public');

        if (! $this->isManagedPath($path) || ! $disk->exists($path)) {
            return null;
        }

        $contents = $disk->get($path);

        if ($contents === null) {
            throw ValidationException::withMessages(['image' => 'Impossible de lire l’image existante.']);
        }

        return $this->write($contents, $name, $connection, pathinfo($path, PATHINFO_EXTENSION));
    }

    public function deleteUnused(?string $path): void
    {
        if ($path === null || ! $this->isManagedPath($path) || Game::query()->where('image', $path)->exists()) {
            return;
        }

        $disk = Storage::disk('public');

        // Each revision owns its directory. Never delete an arbitrary submitted path.
        if (! $disk->deleteDirectory(dirname($path))) {
            throw new RuntimeException('Impossible de supprimer l’image du jeu : '.$path);
        }
    }

    private function isManagedPath(string $path): bool
    {
        return preg_match('/\Agames\/[a-f0-9-]{36}\/[a-z0-9-]+\.(?:webp|svg)\z/', $path) === 1;
    }

    private function write(string $contents, string $name, Connection $connection, string $extension = 'webp'): string
    {
        $filename = substr(Str::slug($name), 0, 180) ?: 'jeu';
        $path = 'games/'.Str::uuid().'/'.$filename.'.'.$extension;

        // Register before writing: even a partially failed write must be removed.
        $connection->afterRollBack(fn () => rescue(fn () => $this->deleteUnused($path)));

        if (! Storage::disk('public')->put($path, $contents, 'public')) {
            throw ValidationException::withMessages(['image' => 'Impossible d’enregistrer l’image. Veuillez réessayer.']);
        }

        return $path;
    }

    private function orient(GdImage $image, int $orientation): GdImage
    {
        if (in_array($orientation, [2, 4, 5, 7], true)) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        }

        $angle = match ($orientation) {
            3, 4 => 180,
            5, 8 => 90,
            6, 7 => -90,
            default => 0,
        };

        if ($angle === 0) {
            return $image;
        }

        return imagerotate($image, $angle, 0) ?: $image;
    }
}
