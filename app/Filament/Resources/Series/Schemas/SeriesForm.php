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
                ->helperText('Le terme utilisé par l’éditeur : série ou bloc.'),
            CatalogFields::name()->helperText('Ex. Écarlate et Violet. Ajoutez les traductions dans « Localisations » après création.'),
        ], [CatalogFields::visibility()], description: 'Une série ou un bloc regroupe plusieurs extensions d’un même catalogue. Ce niveau est facultatif : tous les TCG ne l’utilisent pas.', advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
