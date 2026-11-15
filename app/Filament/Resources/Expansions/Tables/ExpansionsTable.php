<?php

namespace App\Filament\Resources\Expansions\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Expansion;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpansionsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with('catalog.game'))
            ->columns([
                CatalogTables::name('Extension ou ensemble'),
                CatalogTables::key(),
                TextColumn::make('catalog.name')->label('Catalogue / jeu')->searchable()->wrap()
                    ->description(fn (Expansion $record): string => $record->catalog->game->name),
                TextColumn::make('catalog.game.name')->label('Jeu')->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('series.name')->label('Série')->searchable()->placeholder('Sans série')->wrap()->toggleable(),
                CatalogTables::enum('type', 'Type', CatalogLabels::EXPANSION_TYPES)->toggleable(isToggledHiddenByDefault: true),
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
