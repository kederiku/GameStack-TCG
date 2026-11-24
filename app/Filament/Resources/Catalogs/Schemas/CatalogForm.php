<?php

namespace App\Filament\Resources\Catalogs\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Catalog;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CatalogForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::game(),
            CatalogFields::language('default_language_id', __('admin.labels.default-language'))
                ->helperText(__('admin.catalogs.language_help')),
            ...TranslationFields::schema(Catalog::class),
            Section::make(__('admin.translation.reference'))->columnSpanFull()->schema([
                CatalogFields::name(__('admin.translation.reference_name')),
                CatalogFields::description('reference_description'),
            ]),
        ], [CatalogFields::visibility()], description: __('admin.catalogs.help'), advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
