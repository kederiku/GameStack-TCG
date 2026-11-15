<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListSeries extends ListCatalogRecords
{
    protected static string $resource = SeriesResource::class;

    public function getSubheading(): string
    {
        return 'Les séries et blocs regroupent plusieurs extensions d’un même catalogue. Ce niveau est facultatif. Ouvrez une série pour ajouter ses noms et images dans différentes langues.';
    }
}
