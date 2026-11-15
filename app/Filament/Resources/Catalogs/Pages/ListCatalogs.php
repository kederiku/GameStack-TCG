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
        return 'Un catalogue rassemble les extensions d’une même version du jeu, par exemple Pokémon International ou Japonais. Parcours : jeu → catalogue → série facultative → extension → édition par langue → sortie par marché.';
    }

    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Créer')
            ->url(fn (): string => CatalogResource::getUrl('create', array_filter([
                'game' => $this->tableFilters['game']['value'] ?? null,
            ])))];
    }
}
