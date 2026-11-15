<?php

namespace App\Filament\Resources\Markets\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Schemas\Schema;

class MarketForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::name()->helperText('Nom du territoire de commercialisation, par exemple France, Japon ou Monde.'),
        ], description: 'Un marché indique où une édition est commercialisée. Une même édition linguistique peut sortir sur plusieurs marchés, à des dates différentes.', advanced: [CatalogFields::key()]);
    }
}
