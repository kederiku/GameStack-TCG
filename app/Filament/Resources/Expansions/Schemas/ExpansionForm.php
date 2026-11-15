<?php

namespace App\Filament\Resources\Expansions\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExpansionForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::catalogue()->live()->afterStateUpdated(fn (Set $set) => $set('series_id', null)),
            CatalogFields::series(),
            CatalogFields::name(),
            CatalogFields::key(),
            CatalogFields::enum('type', 'Type', CatalogLabels::EXPANSION_TYPES),
        ], [CatalogFields::visibility(), CatalogFields::order()]);
    }
}
