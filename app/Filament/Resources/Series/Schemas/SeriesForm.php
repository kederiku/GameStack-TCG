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
            CatalogFields::enum('type', __('admin.labels.type'), CatalogLabels::seriesTypes())
                ->helperText(__('admin.series.type_help')),
            CatalogFields::name()->helperText(__('admin.series.name_help')),
        ], [CatalogFields::visibility()], description: __('admin.series.help'), advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
