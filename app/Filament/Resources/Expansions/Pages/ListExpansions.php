<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListExpansions extends ListCatalogRecords
{
    protected static string $resource = ExpansionResource::class;

    public function getSubheading(): string
    {
        return 'Une extension est un ensemble de cartes rattaché à un catalogue, avec ou sans série. Ouvrez sa fiche pour ajouter les traductions d’affichage et les éditions réellement imprimées par langue.';
    }
}
