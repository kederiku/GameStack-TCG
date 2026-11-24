<?php

namespace App\Services;

use DOMDocument;
use DOMXPath;
use enshrined\svgSanitize\Sanitizer;
use GdImage;
use Illuminate\Database\Connection;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Process\Exception\ExceptionInterface as ProcessException;
use Symfony\Component\Process\Process;

abstract class AvifImages
{
    protected const FALLBACK_FILENAME = 'image';

    abstract protected function directory(): string;

    abstract public function deleteUnused(?string $path, ?string $connection = null): void;

    public static function diskName(): string
    {
        return (string) config('game-images.disk', 'public');
    }

    public static function disk(): FilesystemAdapter
    {
        return Storage::disk(self::diskName());
    }

    public static function validateUpload(UploadedFile $upload): void
    {
        Validator::make(['image' => $upload], [
            'image' => ['required', 'image:allow_svg', 'mimetypes:image/jpeg,image/png,image/webp,image/avif,image/svg+xml', 'max:5120', 'dimensions:max_width=4096,max_height=4096'],
        ])->validate();
    }

    public function store(UploadedFile $upload, string $name, Connection $connection): string
    {
        return $this->write($this->encode($upload), $name, $connection);
    }

    protected function encode(UploadedFile $upload): string
    {
        if (! (gd_info()['AVIF Support'] ?? false)) {
            throw ValidationException::withMessages(['image' => __('validation.images.avif_unsupported')]);
        }

        $contents = $upload->get();

        if (is_string($contents) && $upload->getMimeType() === 'image/svg+xml') {
            $contents = $this->rasterizeSvg($contents);
        }

        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw ValidationException::withMessages(['image' => __('validation.images.unreadable')]);
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
            throw ValidationException::withMessages(['image' => __('validation.images.conversion_failed')]);
        }

        return $avif;
    }

    private function rasterizeSvg(string $contents): string
    {
        $sanitizer = new Sanitizer;
        $sanitizer->removeRemoteReferences(true);
        $svg = $sanitizer->sanitize($contents);

        if ($svg === false || $svg === '') {
            throw ValidationException::withMessages(['image' => __('validation.images.invalid_svg')]);
        }

        $this->validateEmbeddedImages($svg);

        // stdin has no base URI, preventing librsvg from resolving local files.
        // Bound native allocations as well as the output size and rendering time.
        $process = new Process(['prlimit', '--as=536870912', '--', 'rsvg-convert', '--format=png', '--width=2048', '--height=2048', '--keep-aspect-ratio']);
        $process->setInput($svg)->setTimeout(10);

        try {
            $process->mustRun();
        } catch (ProcessException) {
            throw ValidationException::withMessages(['image' => __('validation.images.svg_conversion_failed')]);
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
                throw ValidationException::withMessages(['image' => __('validation.images.svg-dimensions')]);
            }

            $pixels += $size[0] * $size[1];

            if ($pixels > 4096 * 4096) {
                throw ValidationException::withMessages(['image' => __('validation.images.svg-size')]);
            }
        }
    }

    protected function write(string $contents, string $name, Connection $connection, string $extension = 'avif'): string
    {
        $filename = substr(Str::slug($name), 0, 180) ?: static::FALLBACK_FILENAME;
        $path = $this->directory().'/'.Str::uuid().'/'.$filename.'.'.$extension;

        // Register before writing: even a partially failed write must be removed.
        $connection->afterRollBack(fn () => rescue(fn () => $this->deleteUnused($path, $connection->getName())));

        if (! self::disk()->put($path, $contents, 'public')) {
            throw ValidationException::withMessages(['image' => __('validation.images.save-image')]);
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
