<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CreateCatalogRecord;
use App\Models\Catalog;
use Livewire\Attributes\Url;

class CreateSeries extends CreateCatalogRecord
{
    protected static string $resource = SeriesResource::class;

    #[Url]
    public mixed $catalog = null;

    protected function fillForm(): void
    {
        parent::fillForm();
        $catalogId = CatalogFields::positiveId($this->catalog);
        if ($catalogId === null || ! Catalog::query()->whereKey($catalogId)->exists()) {
            return;
        }

        $this->data['catalog_id'] = $catalogId;
    }
}
