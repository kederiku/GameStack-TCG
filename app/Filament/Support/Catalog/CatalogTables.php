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
            ->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucune fiche')
            ->emptyStateDescription('Ajoutez une fiche ou ajustez les filtres pour afficher d’autres résultats.')
            ->recordActions([
                EditAction::make()->label('Modifier'),
                CatalogActions::delete(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([CatalogActions::deleteBulk()])->label('Actions groupées'),
            ]);
    }

    public static function name(string $label = 'Nom'): TextColumn
    {
        return TextColumn::make('name')->label($label)->weight(FontWeight::SemiBold)->wrap()->searchable()->sortable();
    }

    public static function key(string $field = 'key', string $label = 'Clé'): TextColumn
    {
        return TextColumn::make($field)->label($label)->searchable()->sortable()
            ->toggleable(isToggledHiddenByDefault: $field === 'key');
    }

    public static function visibility(): TextColumn
    {
        return TextColumn::make('is_visible')->label('Visibilité')->badge()
            ->formatStateUsing(fn (bool $state): string => $state ? 'Visible' : 'Masqué')
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
        return SelectFilter::make('game')->label('Jeu')->relationship($relationship, 'name')->searchable()->preload();
    }

    public static function catalogFilter(): SelectFilter
    {
        return SelectFilter::make('catalog')->label('Catalogue')->relationship('catalog', 'name')
            ->getOptionLabelFromRecordUsing(fn (Catalog $record): string => CatalogFields::catalogueLabel($record))
            ->searchable()->preload();
    }

    public static function seriesFilter(): SelectFilter
    {
        return SelectFilter::make('series')->label('Série')->relationship('series', 'name')
            ->getOptionLabelFromRecordUsing(fn (Series $record): string => "{$record->name} — ".CatalogFields::catalogueLabel($record->catalog))
            ->searchable()->preload();
    }

    public static function visibilityFilter(): TernaryFilter
    {
        return TernaryFilter::make('is_visible')->label('Visibilité')->placeholder('Toutes')
            ->trueLabel('Visibles')->falseLabel('Masquées')->native(false);
    }
}
