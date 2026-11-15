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
        return [CreateAction::make()->label('Ajouter une expansion')
            ->url(fn (): string => ExpansionResource::getUrl('create', array_filter([
                'catalog' => CatalogFields::positiveId($this->tableFilters['catalog']['value'] ?? null),
                'series' => CatalogFields::positiveId($this->tableFilters['series']['value'] ?? null),
            ])))];
    }

    public function getSubheading(): string
    {
        return 'Une extension est un ensemble de cartes rattaché à un catalogue, avec ou sans série. Ouvrez sa fiche pour ajouter les traductions d’affichage et les éditions réellement imprimées par langue.';
    }
}
