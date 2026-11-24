<?php

namespace App\Filament\Resources\Expansions\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Expansion;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class ExpansionForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::catalogue()->live()->afterStateUpdated(fn (Set $set) => $set('series_id', null)),
            CatalogFields::series(),
            ...TranslationFields::schema(Expansion::class),
            Section::make(__('admin.translation.reference'))->columnSpanFull()->schema([
                CatalogFields::name(__('admin.translation.reference_name')),
            ]),
            CatalogFields::enum('type', __('admin.labels.type'), CatalogLabels::expansionTypes())
                ->helperText(__('admin.expansions.type_help')),
        ], [CatalogFields::visibility()], description: __('admin.expansions.help'), advanced: [CatalogFields::key(), CatalogFields::order()]);
    }
}
