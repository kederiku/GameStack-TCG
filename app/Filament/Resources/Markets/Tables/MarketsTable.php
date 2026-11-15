<?php

namespace App\Filament\Resources\Markets\Tables;

use App\Filament\Support\Catalog\CatalogTables;
use Filament\Tables\Table;

class MarketsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name('Marché'), CatalogTables::key(),
        ]);
    }
}
