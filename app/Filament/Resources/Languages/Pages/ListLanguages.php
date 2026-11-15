<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListLanguages extends ListCatalogRecords
{
    protected static string $resource = LanguageResource::class;
}
