<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\CreateCatalogRecord;

class CreateSeries extends CreateCatalogRecord
{
    protected static string $resource = SeriesResource::class;
}
