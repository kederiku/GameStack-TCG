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
            CatalogFields::language()->disabledOn('edit')
                ->helperText(__('admin.editions.language_help')),
            CatalogFields::name(__('admin.labels.edition-name'))->helperText(__('admin.editions.name_help')),
            TextInput::make('official_code')->label(__('admin.labels.official-code'))->maxLength(255)
                ->helperText(__('admin.editions.code_help')),
        ], [
            CatalogFields::enum('status', __('admin.labels.status'), CatalogLabels::statuses())->default('announced')
                ->helperText(__('admin.editions.status_help')),
            CatalogFields::visibility(),
        ], CatalogFields::image(__('admin.labels.edition-image'), __('admin.editions.image_help')), description: __('admin.editions.help'));
    }
}
