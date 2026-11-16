<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::game(),
            CatalogFields::language('default_language_id', __('admin.labels.default-language'))
                ->helperText(__('admin.catalogs.language_help')),
            CatalogFields::name()->helperText(__('admin.catalogs.name_help')),
            CatalogFields::description(),
        ], [CatalogFields::visibility()], description: __('admin.catalogs.help'), advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
