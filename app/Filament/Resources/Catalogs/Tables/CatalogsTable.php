<?php

namespace App\Filament\Resources\Catalogs\Tables;

use App\Filament\Resources\Games\GameResource;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\Catalog;
use Filament\Tables\Table;

class CatalogsTable
{
    public static function configure(Table $table): Table
    {
        return CatalogTables::standard($table)->columns([
            CatalogTables::name(__('admin.labels.catalog')),
            CatalogTables::key(),
            CatalogTables::relatedName('game', __('admin.labels.game'))->wrap()
                ->url(fn (Catalog $record): string => GameResource::getUrl('edit', ['record' => $record->game_id])),
            CatalogTables::relatedName('defaultLanguage', __('admin.labels.fallback-language'))->badge()->toggleable(isToggledHiddenByDefault: true),
            CatalogTables::visibility(),
        ])->filters([CatalogTables::gameFilter(), CatalogTables::visibilityFilter()]);
    }
}
