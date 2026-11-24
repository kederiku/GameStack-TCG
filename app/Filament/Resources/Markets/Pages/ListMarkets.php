<?php

namespace App\Filament\Resources\Markets\Pages;

use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListMarkets extends ListCatalogRecords
{
    protected static string $resource = MarketResource::class;

    public function getSubheading(): string
    {
        return __('admin.markets.list_help');
    }
}
