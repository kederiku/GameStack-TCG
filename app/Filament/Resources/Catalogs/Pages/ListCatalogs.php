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
        return 'Une version éditoriale du jeu, par exemple Pokémon International ou Japonais. Ouvrez un catalogue pour retrouver ses séries et extensions.';
    }

    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Ajouter un catalogue')
            ->url(fn (): string => CatalogResource::getUrl('create', array_filter([
                'game' => $this->tableFilters['game']['value'] ?? null,
            ])))];
    }
}
