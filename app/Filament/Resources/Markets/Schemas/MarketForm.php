<?php

namespace App\Filament\Resources\Markets\Schemas;

use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\TranslationFields;
use App\Models\Market;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MarketForm
{
    public static function configure(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            ...TranslationFields::schema(Market::class),
            Section::make(__('admin.translation.reference'))->columnSpanFull()->schema([
                CatalogFields::name(__('admin.translation.reference_name')),
            ]),
        ], description: __('admin.markets.help'), advanced: [CatalogFields::key()]);
    }
}
