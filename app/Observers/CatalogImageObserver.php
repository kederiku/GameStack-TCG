<?php

namespace App\Observers;

use App\Models\ExpansionEdition;
use App\Models\SeriesImage;
use App\Services\CatalogImages;

class CatalogImageObserver
{
    public function updated(SeriesImage|ExpansionEdition $record): void
    {
        if ($record->wasChanged('image')) {
            $this->deleteAfterCommit($record, $record->getRawOriginal('image'));
        }
    }

    public function deleted(SeriesImage|ExpansionEdition $record): void
    {
        $this->deleteAfterCommit($record, $record->image);
    }

    private function deleteAfterCommit(SeriesImage|ExpansionEdition $record, ?string $path): void
    {
        if ($path === null) {
            return;
        }

        $connection = $record->getConnection();
        $connection->afterCommit(
            fn () => rescue(fn () => app(CatalogImages::class)->deleteUnused($path, $connection->getName())),
        );
    }
}
