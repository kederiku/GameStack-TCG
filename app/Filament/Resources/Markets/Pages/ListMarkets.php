<?php

namespace App\Filament\Resources\Markets\Pages;

use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListMarkets extends ListCatalogRecords
{
    protected static string $resource = MarketResource::class;

    public function getSubheading(): string
    {
        return 'Les marchés sont les territoires de commercialisation, par exemple France ou Japon. Ils servent à préciser les sorties d’une édition, avec leur date et leur canal de distribution.';
    }
}
