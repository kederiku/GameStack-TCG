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
            CatalogFields::name()->helperText(__('admin.languages.name_help')),
            TextInput::make('code')->label(__('admin.labels.language-code'))->required()->maxLength(255)->disabledOn('edit')
                ->helperText(__('admin.languages.code_help')),
        ], description: __('admin.languages.help'));
    }
}
