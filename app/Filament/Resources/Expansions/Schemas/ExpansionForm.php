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
            CatalogFields::name()->helperText('Nom de référence de l’ensemble. Ses traductions d’affichage et ses éditions imprimées se renseignent séparément après création.'),
            CatalogFields::key(),
            CatalogFields::enum('type', 'Type', CatalogLabels::EXPANSION_TYPES)
                ->helperText('Choisissez la nature de l’ensemble : extension courante, spéciale, promotionnelle, ensemble de deck ou de réimpression. Un booster ou un display sera un produit scellé distinct.'),
        ], [CatalogFields::visibility(), CatalogFields::order()], description: 'Cette fiche décrit un ensemble de cartes. Après création, ajoutez ses éditions par langue, puis les sorties de chaque édition. « Localisations » sert uniquement à traduire sa présentation.');
    }
}
