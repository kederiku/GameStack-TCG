<?php

namespace App\Services;

use App\Models\ExpansionEdition;
use App\Models\SeriesLocalization;
use Illuminate\Database\Connection;
use Illuminate\Http\UploadedFile;
use RuntimeException;

class CatalogImages extends AvifImages
{
    protected function directory(): string
    {
        return 'catalog';
    }

    public function store(UploadedFile $upload, string $name, Connection $connection): string
    {
        self::validateUpload($upload);

        return parent::store($upload, $name, $connection);
    }

    public function deleteUnused(?string $path, ?string $connection = null): void
    {
        if ($path === null || ! $this->isManagedPath($path)
            || SeriesLocalization::on($connection)->where('image', $path)->exists()
            || ExpansionEdition::on($connection)->where('image', $path)->exists()) {
            return;
        }

        if (! self::disk()->delete($path)) {
            throw new RuntimeException(__('validation.images.delete-catalog', ['path' => $path]));
        }
    }

    public function isManagedPath(string $path): bool
    {
        return preg_match('/\Acatalog\/[a-f0-9]{8}(?:-[a-f0-9]{4}){3}-[a-f0-9]{12}\/[a-z0-9-]+\.avif\z/', $path) === 1;
    }
}
