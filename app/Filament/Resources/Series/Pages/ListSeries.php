<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\ListCatalogRecords;
use Filament\Actions\CreateAction;

class ListSeries extends ListCatalogRecords
{
    protected static string $resource = SeriesResource::class;

    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Ajouter une série')
            ->url(fn (): string => SeriesResource::getUrl('create', array_filter([
                'catalog' => CatalogFields::positiveId($this->tableFilters['catalog']['value'] ?? null),
            ])))];
    }

    public function getSubheading(): string
    {
        return 'Les séries et blocs regroupent plusieurs extensions d’un même catalogue. Ce niveau est facultatif. Ouvrez une série pour ajouter ses noms et images dans différentes langues.';
    }
}
