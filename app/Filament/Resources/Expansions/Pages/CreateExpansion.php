<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CreateCatalogRecord;
use App\Models\Catalog;
use App\Models\Series;
use Livewire\Attributes\Url;

class CreateExpansion extends CreateCatalogRecord
{
    protected static string $resource = ExpansionResource::class;

    #[Url]
    public mixed $catalog = null;

    #[Url]
    public mixed $series = null;

    protected function fillForm(): void
    {
        parent::fillForm();
        $catalogId = CatalogFields::positiveId($this->catalog);
        if ($catalogId === null || ! Catalog::query()->whereKey($catalogId)->exists()) {
            return;
        }

        $this->data['catalog_id'] = $catalogId;
        $seriesId = CatalogFields::positiveId($this->series);
        if ($seriesId !== null && Series::query()->whereKey($seriesId)->where('catalog_id', $catalogId)->exists()) {
            $this->data['series_id'] = $seriesId;
        }
    }
}
