<?php

namespace App\Filament\Resources\Markets\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Schemas\Schema;

class MarketForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [CatalogFields::name(), CatalogFields::key()]);
    }
}
