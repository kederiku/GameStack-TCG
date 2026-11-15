<?php

namespace App\Filament\Resources\Series\RelationManagers;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\LocalizationsRelationManager;
use Filament\Forms\Components\FileUpload;

class SeriesLocalizationsRelationManager extends LocalizationsRelationManager
{
    protected static string $parentField = 'series_id';

    protected function imageField(): FileUpload
    {
        return CatalogFields::image('Image de la série dans cette langue', 'Logo ou visuel de la série dans la langue choisie. Sans image, aucun visuel d’une autre langue n’est repris.');
    }
}
