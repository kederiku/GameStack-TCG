<?php

namespace App\Filament\Resources\Series\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Series;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('catalog.game'))
            ->columns([
                CatalogTables::name('Série'),
                CatalogTables::key(),
                TextColumn::make('catalog.name')->label('Catalogue / jeu')->searchable()->wrap()
                    ->description(fn (Series $record): string => $record->catalog->game->name),
                TextColumn::make('catalog.game.name')->label('Jeu')->searchable()->toggleable(isToggledHiddenByDefault: true),
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
