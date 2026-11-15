<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::game(),
            CatalogFields::language('default_language_id', 'Langue par défaut'),
            CatalogFields::name(),
            CatalogFields::key(),
            CatalogFields::description(),
        ], [CatalogFields::visibility(), CatalogFields::order()]);
    }
}
