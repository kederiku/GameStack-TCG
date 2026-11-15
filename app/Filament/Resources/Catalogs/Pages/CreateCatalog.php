<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CreateCatalogRecord;
use Livewire\Attributes\Url;

class CreateCatalog extends CreateCatalogRecord
{
    protected static string $resource = CatalogResource::class;

    #[Url]
    public string|int|null $game = null;

    protected function fillForm(): void
    {
        parent::fillForm();

        $this->data['game_id'] = CatalogFields::positiveId($this->game);
    }
}
