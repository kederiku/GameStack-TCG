<?php

namespace App\Filament\Resources\ExpansionEditions\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ExpansionEditionForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::language()->disabledOn('edit'),
            CatalogFields::name('Nom de l’édition'),
            TextInput::make('official_code')->label('Code officiel')->maxLength(255),
        ], [
            CatalogFields::enum('status', 'État', CatalogLabels::STATUSES)->default('announced'),
            CatalogFields::visibility(),
        ], CatalogFields::image('Image de l’édition'));
    }
}
