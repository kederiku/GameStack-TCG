<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CreateCatalogRecord;

class CreateExpansion extends CreateCatalogRecord
{
    protected static string $resource = ExpansionResource::class;
}
