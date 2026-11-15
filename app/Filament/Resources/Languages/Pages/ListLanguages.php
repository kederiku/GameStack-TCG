<?php

namespace App\Filament\Resources\Languages\Pages;

use App\Filament\Resources\Languages\LanguageResource;
use App\Filament\Support\Catalog\ListCatalogRecords;

class ListLanguages extends ListCatalogRecords
{
    protected static string $resource = LanguageResource::class;

    public function getSubheading(): string
    {
        return 'Les langues sont utilisées pour traduire les fiches et identifier la langue imprimée des éditions. Elles ne désignent pas les marchés : une édition anglaise peut être vendue dans plusieurs pays.';
    }
}
