<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListSeries extends ListCatalogRecords
{
    protected static string $resource = SeriesResource::class;
}
