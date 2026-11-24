<?php

namespace App\Filament\Support\Catalog;

use App\Models\Catalog;
use App\Models\Game;
use App\Models\Series;
use Closure;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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

        return TextColumn::make('name')->label($label)->weight(FontWeight::SemiBold)->wrap()
            ->state(fn (Model $record): string => CatalogLabels::name($record))
            ->searchable(query: CatalogLabels::searchName(...))->sortable(query: CatalogLabels::sortName(...));
    }

    public static function relatedName(string $relationship, string $label): TextColumn
    {
        return TextColumn::make($relationship.'.name')->label($label)
            ->state(function (Model $record) use ($relationship): ?string {
                $related = data_get($record, $relationship);

                return $related instanceof Model ? CatalogLabels::name($related) : null;
            })
            ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHas($relationship, fn (Builder $related) => CatalogLabels::searchName($related, $search)));
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
        return self::parentFilter('game', $relationship, __('admin.labels.game'), Game::query(), CatalogLabels::name(...));
    }

    public static function catalogFilter(): SelectFilter
    {
        return self::parentFilter('catalog', 'catalog', __('admin.labels.catalog'), Catalog::query()->with(['defaultLanguage', 'game']), CatalogFields::catalogueLabel(...));
    }

    public static function seriesFilter(): SelectFilter
    {
        return self::parentFilter('series', 'series', __('admin.labels.series'), Series::query()->with(['catalog.defaultLanguage', 'catalog.game']),
            fn (Series $record): string => CatalogLabels::name($record).' — '.CatalogFields::catalogueLabel($record->catalog));
    }

    /**
     * @template T of Model
     *
     * @param  Builder<T>  $options
     * @param  Closure(T): string  $format
     */
    private static function parentFilter(string $key, string $relationship, string $label, Builder $options, Closure $format): SelectFilter
    {
        return SelectFilter::make($key)->label($label)->relationship($relationship, 'name',
            function (Builder $query) use ($options): Builder {
                CatalogLabels::sortName($query, 'asc');

                return $query->orderBy($query->getModel()->getQualifiedKeyName())->with($options->getEagerLoads());
            })
            ->getOptionLabelFromRecordUsing($format)
            ->getSearchResultsUsing(function (string $search) use ($options, $format): array {
                $query = clone $options;
                CatalogLabels::searchName($query, $search);

                CatalogLabels::sortName($query, 'asc');

                return $query->orderBy($query->getModel()->getQualifiedKeyName())->limit(50)->get()->mapWithKeys(fn (Model $record): array => [$record->getKey() => $format($record)])->all();
            })->searchable()->preload();
    }

    public static function visibilityFilter(): TernaryFilter
    {
        return TernaryFilter::make('is_visible')->label(__('admin.labels.visibility'))->placeholder(__('admin.labels.all'))
            ->trueLabel(__('admin.labels.visible_plural'))->falseLabel(__('admin.labels.hidden_plural'))->native(false);
    }
}
