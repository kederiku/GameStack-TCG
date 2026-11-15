<?php

namespace App\Services;

use App\Models\Game;
use DOMDocument;
use DOMXPath;
use enshrined\svgSanitize\Sanitizer;
use GdImage;
use Illuminate\Database\Connection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Symfony\Component\Process\Process;

class GameImages
{
    public function store(UploadedFile $upload, string $name, Connection $connection): string
    {
        if (! (gd_info()['AVIF Support'] ?? false)) {
            throw ValidationException::withMessages(['image' => 'Le serveur ne prend pas en charge la conversion AVIF.']);
        }

        $contents = $upload->get();

        if (is_string($contents) && $upload->getMimeType() === 'image/svg+xml') {
            $contents = $this->rasterizeSvg($contents);
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
            $encoded = imageavif($image, null, 85);
            $avif = ob_get_contents();
        } finally {
            ob_end_clean();
        }

        $dimensions = is_string($avif) && $avif !== '' ? @getimagesizefromstring($avif) : false;

        if (! $encoded || ! is_string($avif) || $dimensions === false || $dimensions['mime'] !== 'image/avif') {
            throw ValidationException::withMessages(['image' => 'La conversion AVIF a échoué.']);
        }

        return $this->write($avif, $name, $connection);
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

    public function isManagedPath(string $path): bool
    {
        return preg_match('/\Agames\/[a-f0-9-]{36}\/[a-z0-9-]+\.(?:avif|webp|svg)\z/', $path) === 1;
    }

    private function rasterizeSvg(string $contents): string
    {
        $sanitizer = new Sanitizer;
        $sanitizer->removeRemoteReferences(true);
        $svg = $sanitizer->sanitize($contents);

        if ($svg === false || $svg === '') {
            throw ValidationException::withMessages(['image' => 'Ce fichier SVG est invalide ou ne peut pas être nettoyé.']);
        }

        $this->validateEmbeddedImages($svg);

        // stdin has no base URI, preventing librsvg from resolving local files.
        // Bound native allocations as well as the output size and rendering time.
        $process = new Process(['prlimit', '--as=536870912', '--', 'rsvg-convert', '--format=png', '--width=2048', '--height=2048', '--keep-aspect-ratio']);
        $process->setInput($svg)->setTimeout(10);

        try {
            $process->mustRun();
        } catch (ProcessException) {
            throw ValidationException::withMessages(['image' => 'Ce SVG ne peut pas être converti en AVIF.']);
        }

        return $process->getOutput();
    }

    private function validateEmbeddedImages(string $svg): void
    {
        $document = new DOMDocument;
        $document->loadXML($svg, LIBXML_NONET);
        $pixels = 0;

        // SVG image/feImage can carry raster data despite the outer SVG's small dimensions.
        // Validate both href and xlink:href before handing any data to native decoders.
        foreach ((new DOMXPath($document))->query('//@*[local-name() = "href"]') ?: [] as $attribute) {
            $uri = trim($attribute->nodeValue ?? '');

            if (! str_starts_with(strtolower($uri), 'data:')) {
                continue;
            }

            [$header, $payload] = array_pad(explode(',', $uri, 2), 2, '');
            $contents = str_ends_with(strtolower($header), ';base64')
                ? base64_decode(rawurldecode($payload), true)
                : rawurldecode($payload);
            $size = is_string($contents) && $contents !== '' ? @getimagesizefromstring($contents) : false;

            if ($size === false || $size[0] < 1 || $size[1] < 1 || $size[0] > 4096 || $size[1] > 4096) {
                throw ValidationException::withMessages(['image' => 'Les images intégrées au SVG doivent être lisibles et limitées à 4096 × 4096 pixels.']);
            }

            $pixels += $size[0] * $size[1];

            if ($pixels > 4096 * 4096) {
                throw ValidationException::withMessages(['image' => 'Les images intégrées au SVG sont trop volumineuses au total.']);
            }
        }
    }

    private function write(string $contents, string $name, Connection $connection, string $extension = 'avif'): string
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
