<?php

namespace App\Filament\Resources\Series\Tables;

use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Series;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SeriesTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['catalog.game', 'catalog.defaultLanguage']))
            ->columns([
                CatalogTables::name(__('admin.labels.series')),
                CatalogTables::key(),
                CatalogTables::relatedName('catalog', __('admin.labels.catalog-game'))->wrap()
                    ->description(fn (Series $record): string => CatalogLabels::name($record->catalog->game)),
                CatalogTables::relatedName('catalog.game', __('admin.labels.game'))->toggleable(isToggledHiddenByDefault: true),
                CatalogTables::enum('type', __('admin.labels.type'), CatalogLabels::seriesTypes()),
                CatalogTables::visibility(),
            ])->filters([
                CatalogTables::gameFilter('catalog.game'),
                CatalogTables::catalogFilter(),
                SelectFilter::make('type')->label(__('admin.labels.type'))->options(CatalogLabels::seriesTypes()),
                CatalogTables::visibilityFilter(),
            ]);
    }
}
