<?php

namespace App\Filament\Resources\Series\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Series;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SeriesForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::catalogue(),
            CatalogFields::enum('type', __('admin.labels.type'), CatalogLabels::seriesTypes())
                ->helperText(__('admin.series.type_help')),
            ...TranslationFields::schema(Series::class),
            Section::make(__('admin.translation.reference'))->columnSpanFull()->schema([
                CatalogFields::name(__('admin.translation.reference_name')),
            ]),
        ], [CatalogFields::visibility()], description: __('admin.series.help'), advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
