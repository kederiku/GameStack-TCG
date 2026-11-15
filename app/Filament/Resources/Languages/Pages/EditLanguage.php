<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Support\Catalog\EditCatalogRecord;

class EditLanguage extends EditCatalogRecord
{
    protected static string $resource = LanguageResource::class;
}
