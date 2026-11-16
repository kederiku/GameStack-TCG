<?php

namespace App\Filament\Resources\Games\Tables;

use App\Filament\Support\Catalog\CatalogActions;
use App\Models\Game;
use App\Services\GameImages;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GamesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label(__('admin.labels.artwork'))
                    ->disk(fn (): string => GameImages::diskName())
                    ->visibility('public')
                    ->imageHeight(40)
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label(__('admin.labels.game'))
                    ->description(fn (Game $record): ?string => $record->publisher)
                    ->wrap()
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('admin.labels.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('publisher')
                    ->label(__('admin.labels.publisher'))
                    ->placeholder(__('admin.labels.not-listed'))
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('is_visible')
                    ->label(__('admin.labels.visibility'))
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? __('admin.labels.visible') : __('admin.labels.hidden'))
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (bool $state): Heroicon => $state ? Heroicon::OutlinedEye : Heroicon::OutlinedEyeSlash),
                TextColumn::make('updated_at')
                    ->label(__('admin.labels.updated-at'))
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()->label(__('admin.labels.edit')),
                CatalogActions::delete(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    CatalogActions::deleteBulk(),
                ]),
            ])
            ->emptyStateHeading(__('admin.labels.no-games'))
            ->emptyStateDescription(__('admin.games.empty_help'));
    }
}
