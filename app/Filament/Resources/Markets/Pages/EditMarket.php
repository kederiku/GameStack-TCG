<?php

namespace App\Filament\Resources\Markets\Pages;

use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Support\Catalog\EditCatalogRecord;

class EditMarket extends EditCatalogRecord
{
    protected static string $resource = MarketResource::class;
}
