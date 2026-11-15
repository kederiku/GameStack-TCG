<?php

namespace App\Filament\Resources\Series\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use Filament\Schemas\Schema;

class SeriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::catalogue(),
            CatalogFields::enum('type', 'Type', CatalogLabels::SERIES_TYPES),
            CatalogFields::name(),
            CatalogFields::key(),
        ], [CatalogFields::visibility(), CatalogFields::order()]);
    }
}
