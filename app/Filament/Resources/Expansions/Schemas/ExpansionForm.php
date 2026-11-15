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
            CatalogFields::name()->helperText('Nom de référence. Traductions et éditions imprimées se complètent après création.'),
            CatalogFields::enum('type', 'Type', CatalogLabels::EXPANSION_TYPES)
                ->helperText('Nature de l’ensemble de cartes. Les boosters et displays sont des produits distincts.'),
        ], [CatalogFields::visibility()], description: 'Cette fiche décrit un ensemble de cartes. Après création, ajoutez ses éditions par langue, puis les sorties de chaque édition. « Localisations » sert uniquement à traduire sa présentation.', advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
