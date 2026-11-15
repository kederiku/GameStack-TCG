<?php

namespace App\Filament\Resources\Expansions\RelationManagers;

use App\Filament\Resources\ExpansionEditions\ExpansionEditionResource;
use App\Filament\Resources\ExpansionEditions\Schemas\ExpansionEditionForm;
use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogRelationManager;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\ExpansionEdition;
use App\Services\CatalogImages;
use Filament\Actions\EditAction;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EditionsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'editions';

    protected static ?string $title = 'Éditions';

    public function form(Schema $schema): Schema
    {
        return ExpansionEditionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')
            ->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucune édition')
            ->columns([
                ImageColumn::make('image')->label('Visuel')->disk(fn (): string => CatalogImages::diskName())
                    ->visibility('public')->imageHeight(40),
                CatalogTables::name('Édition'),
                TextColumn::make('language.code')->label('Langue')->badge()->searchable(),
                TextColumn::make('official_code')->label('Code officiel')->searchable(),
                CatalogTables::enum('status', 'État', CatalogLabels::STATUSES),
                CatalogTables::visibility(),
            ])
            ->defaultSort('name')
            ->headerActions([$this->createCatalogAction('Créer une édition', 'expansion_id')])
            ->recordActions([
                EditAction::make()->label('Modifier et gérer les sorties')
                    ->url(fn (ExpansionEdition $record): string => ExpansionEditionResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (ExpansionEdition $record): bool => ExpansionEditionResource::canEdit($record)),
                CatalogActions::delete(),
            ]);
    }
}
