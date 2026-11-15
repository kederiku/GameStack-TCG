<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Support\Catalog\CreateCatalogRecord;

class CreateLanguage extends CreateCatalogRecord
{
    protected static string $resource = LanguageResource::class;
}
