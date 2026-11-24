<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\ListCatalogRecords;
use Filament\Actions\CreateAction;

class ListExpansions extends ListCatalogRecords
{
    protected static string $resource = ExpansionResource::class;

    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('admin.labels.add-an-expansion'))
            ->url(fn (): string => ExpansionResource::getUrl('create', array_filter([
                'catalog' => CatalogFields::positiveId($this->tableFilters['catalog']['value'] ?? null),
                'series' => CatalogFields::positiveId($this->tableFilters['series']['value'] ?? null),
            ])))];
    }

    public function getSubheading(): string
    {
        return __('admin.expansions.list_help');
    }
}
