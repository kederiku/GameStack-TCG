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
                CatalogTables::name(__('admin.labels.expansion-or-set')),
                CatalogTables::key(),
                TextColumn::make('catalog.name')->label(__('admin.labels.catalog-game'))->searchable()->wrap()
                    ->description(fn (Expansion $record): string => $record->catalog->game->name),
                TextColumn::make('catalog.game.name')->label(__('admin.labels.game'))->searchable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('series.name')->label(__('admin.labels.series'))->searchable()->placeholder(__('admin.labels.no-series'))->wrap()->toggleable(),
                CatalogTables::enum('type', __('admin.labels.type'), CatalogLabels::expansionTypes())->toggleable(isToggledHiddenByDefault: true),
                CatalogTables::visibility(),
            ])->filters([
                CatalogTables::gameFilter('catalog.game'),
                CatalogTables::catalogFilter(),
                CatalogTables::seriesFilter(),
                SelectFilter::make('type')->label(__('admin.labels.type'))->options(CatalogLabels::expansionTypes()),
                CatalogTables::visibilityFilter(),
            ]);
    }
}
