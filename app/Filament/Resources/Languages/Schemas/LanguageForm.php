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
            CatalogFields::name(),
            TextInput::make('code')->label('Code de langue')->required()->maxLength(255)->disabledOn('edit')
                ->helperText('Code BCP 47, par exemple fr, ja ou zh-Hant.'),
        ]);
    }
}
