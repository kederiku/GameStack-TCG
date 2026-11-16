<?php

namespace App\Filament\Support\Catalog;

use App\Models\Catalog;
use App\Models\Series;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CatalogTables
{
    public static function standard(Table $table): Table
    {
        return $table->defaultSort('name')
            ->searchPlaceholder(__('admin.labels.search'))
            ->emptyStateHeading(__('admin.labels.no-records'))
            ->emptyStateDescription(__('admin.records.empty_help'))
            ->recordActions([
                EditAction::make()->label(__('admin.labels.edit')),
                CatalogActions::delete(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([CatalogActions::deleteBulk()])->label(__('admin.labels.bulk-actions')),
            ]);
    }

    public static function name(?string $label = null): TextColumn
    {
        $label ??= __('admin.labels.name');

        return TextColumn::make('name')->label($label)->weight(FontWeight::SemiBold)->wrap()->searchable()->sortable();
    }

    public static function key(string $field = 'key', ?string $label = null): TextColumn
    {
        $label ??= __('admin.labels.key');

        return TextColumn::make($field)->label($label)->searchable()->sortable()
            ->toggleable(isToggledHiddenByDefault: $field === 'key');
    }

    public static function visibility(): TextColumn
    {
        return TextColumn::make('is_visible')->label(__('admin.labels.visibility'))->badge()
            ->formatStateUsing(fn (bool $state): string => $state ? __('admin.labels.visible') : __('admin.labels.hidden'))
            ->color(fn (bool $state): string => $state ? 'success' : 'gray');
    }

    /** @param array<string, string> $labels */
    public static function enum(string $field, string $label, array $labels): TextColumn
    {
        return TextColumn::make($field)->label($label)->badge()
            ->formatStateUsing(fn (string $state): string => $labels[$state] ?? $state);
    }

    public static function gameFilter(string $relationship = 'game'): SelectFilter
    {
        return SelectFilter::make('game')->label(__('admin.labels.game'))->relationship($relationship, 'name')->searchable()->preload();
    }

    public static function catalogFilter(): SelectFilter
    {
        return SelectFilter::make('catalog')->label(__('admin.labels.catalog'))->relationship('catalog', 'name')
            ->getOptionLabelFromRecordUsing(fn (Catalog $record): string => CatalogFields::catalogueLabel($record))
            ->searchable()->preload();
    }

    public static function seriesFilter(): SelectFilter
    {
        return SelectFilter::make('series')->label(__('admin.labels.series'))->relationship('series', 'name')
            ->getOptionLabelFromRecordUsing(fn (Series $record): string => "{$record->name} — ".CatalogFields::catalogueLabel($record->catalog))
            ->searchable()->preload();
    }

    public static function visibilityFilter(): TernaryFilter
    {
        return TernaryFilter::make('is_visible')->label(__('admin.labels.visibility'))->placeholder(__('admin.labels.all'))
            ->trueLabel(__('admin.labels.visible_plural'))->falseLabel(__('admin.labels.hidden_plural'))->native(false);
    }
}
