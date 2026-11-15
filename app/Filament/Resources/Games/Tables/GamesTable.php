<?php

namespace App\Filament\Resources\Games\Tables;

use App\Models\Game;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
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
                    ->label('Visuel')
                    ->disk('public')
                    ->visibility('public')
                    ->imageHeight(40)
                    ->visibleFrom('md'),
                TextColumn::make('name')
                    ->label('Jeu')
                    ->description(fn (Game $record): string => $record->slug)
                    ->weight(FontWeight::SemiBold)
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('publisher')
                    ->label('Éditeur')
                    ->placeholder('Non renseigné')
                    ->wrap()
                    ->visibleFrom('md'),
                TextColumn::make('is_visible')
                    ->label('Visibilité')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Visible' : 'Masqué')
                    ->color(fn (bool $state): string => $state ? 'success' : 'gray')
                    ->icon(fn (bool $state): Heroicon => $state ? Heroicon::OutlinedEye : Heroicon::OutlinedEyeSlash),
                TextColumn::make('updated_at')
                    ->label('Modifié le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make()->label('Modifier'),
                DeleteAction::make()->label('Supprimer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Supprimer la sélection'),
                ]),
            ])
            ->emptyStateHeading('Aucun jeu')
            ->emptyStateDescription('Créez un jeu pour commencer.');
    }
}
