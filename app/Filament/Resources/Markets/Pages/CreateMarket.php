<?php

namespace App\Filament\Resources\Markets\Pages;

use App\Filament\Resources\Markets\MarketResource;
use App\Filament\Support\Catalog\CreateCatalogRecord;

class CreateMarket extends CreateCatalogRecord
{
    protected static string $resource = MarketResource::class;
}
