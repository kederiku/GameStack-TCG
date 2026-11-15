<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListExpansions extends ListCatalogRecords
{
    protected static string $resource = ExpansionResource::class;
}
