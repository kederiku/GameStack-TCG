<?php

namespace App\Filament\Resources\Languages\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LanguageForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::name()->helperText('Nom lisible dans les formulaires, par exemple Français ou Chinois traditionnel.'),
            TextInput::make('code')->label('Code de langue')->required()->maxLength(255)->disabledOn('edit')
                ->helperText('Code standard de langue : fr (français), ja (japonais), ko (coréen), zh-Hans (chinois simplifié), zh-Hant (chinois traditionnel). Non modifiable après création.'),
        ], description: 'Les langues servent aux traductions d’affichage et aux éditions imprimées. Elles sont indépendantes des pays où ces éditions sont commercialisées.');
    }
}
