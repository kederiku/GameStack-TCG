<?php

namespace App\Filament\Resources\Series\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name('Série'),
            CatalogTables::key(),
            TextColumn::make('catalog.name')->label('Catalogue')->searchable(),
            TextColumn::make('catalog.game.name')->label('Jeu')->searchable(),
            CatalogTables::enum('type', 'Type', CatalogLabels::SERIES_TYPES),
            CatalogTables::visibility(),
        ])->filters([
            CatalogTables::gameFilter('catalog.game'),
            CatalogTables::catalogFilter(),
            SelectFilter::make('type')->label('Type')->options(CatalogLabels::SERIES_TYPES),
            CatalogTables::visibilityFilter(),
        ]);
    }
}
