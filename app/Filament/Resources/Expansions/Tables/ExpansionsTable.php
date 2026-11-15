<?php

namespace App\Filament\Resources\Expansions\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ExpansionsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name('Extension ou ensemble'),
            CatalogTables::key(),
            TextColumn::make('catalog.name')->label('Catalogue')->searchable(),
            TextColumn::make('catalog.game.name')->label('Jeu')->searchable(),
            TextColumn::make('series.name')->label('Série')->searchable()->placeholder('Sans série'),
            CatalogTables::enum('type', 'Type', CatalogLabels::EXPANSION_TYPES),
            CatalogTables::visibility(),
        ])->filters([
            CatalogTables::gameFilter('catalog.game'),
            CatalogTables::catalogFilter(),
            CatalogTables::seriesFilter(),
            SelectFilter::make('type')->label('Type')->options(CatalogLabels::EXPANSION_TYPES),
            CatalogTables::visibilityFilter(),
        ]);
    }
}
