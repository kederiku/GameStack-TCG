<?php

namespace App\Filament\Resources\Expansions\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Expansion;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ExpansionsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['catalog.game', 'catalog.defaultLanguage', 'series.catalog.defaultLanguage']))
            ->columns([
                CatalogTables::name(__('admin.labels.expansion-or-set')),
                CatalogTables::key(),
                CatalogTables::relatedName('catalog', __('admin.labels.catalog-game'))->wrap()
                    ->description(fn (Expansion $record): string => CatalogLabels::name($record->catalog->game)),
                CatalogTables::relatedName('catalog.game', __('admin.labels.game'))->toggleable(isToggledHiddenByDefault: true),
                CatalogTables::relatedName('series', __('admin.labels.series'))->placeholder(__('admin.labels.no-series'))->wrap()->toggleable(),
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
