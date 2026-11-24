<?php

namespace App\Filament\Resources\Languages\Tables;

use App\Filament\Support\Catalog\CatalogTables;
use Filament\Tables\Table;

class LanguagesTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name(__('admin.labels.language')), CatalogTables::key('code', __('admin.labels.code')),
        ]);
    }
}
