<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\EditCatalogRecord;

class EditExpansion extends EditCatalogRecord
{
    protected static string $resource = ExpansionResource::class;
}
