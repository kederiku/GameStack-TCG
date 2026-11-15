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
            CatalogFields::enum('type', 'Type', CatalogLabels::SERIES_TYPES)
                ->helperText('Reprenez le terme utilisé par l’éditeur : série pour Pokémon, bloc pour certains ensembles Magic. Les deux regroupent plusieurs extensions.'),
            CatalogFields::name()->helperText('Nom de référence, par exemple Écarlate et Violet. Les noms et images par langue se renseignent dans « Localisations » après création.'),
            CatalogFields::key(),
        ], [CatalogFields::visibility(), CatalogFields::order()], description: 'Une série ou un bloc regroupe plusieurs extensions d’un même catalogue. Ce niveau est facultatif : tous les TCG ne l’utilisent pas.');
    }
}
