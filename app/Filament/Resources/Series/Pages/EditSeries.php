<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\EditCatalogRecord;

class EditSeries extends EditCatalogRecord
{
    protected static string $resource = SeriesResource::class;
}
