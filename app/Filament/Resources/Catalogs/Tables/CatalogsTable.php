<?php

namespace App\Filament\Resources\Catalogs\Tables;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Catalog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CatalogsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name(__('admin.labels.catalog')),
            CatalogTables::key(),
            TextColumn::make('game.name')->label(__('admin.labels.game'))->searchable()->wrap()
                ->url(fn (Catalog $record): string => GameResource::getUrl('edit', ['record' => $record->game_id])),
            TextColumn::make('defaultLanguage.code')->label(__('admin.labels.fallback-language'))->badge()->searchable()->toggleable(isToggledHiddenByDefault: true),
            CatalogTables::visibility(),
        ])->filters([CatalogTables::gameFilter(), CatalogTables::visibilityFilter()]);
    }
}
