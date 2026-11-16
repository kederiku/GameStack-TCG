<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Support\Catalog\ListCatalogRecords;
use Filament\Actions\CreateAction;

class ListCatalogs extends ListCatalogRecords
{
    protected static string $resource = CatalogResource::class;

    public function getSubheading(): string
    {
        return __('admin.catalogs.list_help');
    }

    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('admin.labels.add-a-catalog'))
            ->url(fn (): string => CatalogResource::getUrl('create', array_filter([
                'game' => $this->tableFilters['game']['value'] ?? null,
            ])))];
    }
}
