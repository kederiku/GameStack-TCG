<?php

namespace App\Filament\Support\Catalog;

use App\Services\CatalogImages;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

abstract class LocalizationsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'localizations';

    protected static ?string $title = 'Localisations';

    protected static string $parentField;

    public function form(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::language()->disabledOn('edit'),
            CatalogFields::name('Nom localisé'),
            CatalogFields::description(),
        ], image: $this->imageField());
    }

    protected function imageField(): ?FileUpload
    {
        return null;
    }

    public function table(Table $table): Table
    {
        $columns = [
            CatalogTables::name('Nom localisé'),
            TextColumn::make('language.name')->label('Langue')->searchable(),
            TextColumn::make('language.code')->label('Code')->badge()->searchable(),
        ];

        if ($this->imageField() !== null) {
            $columns[] = ImageColumn::make('image')->label('Visuel')->disk(fn (): string => CatalogImages::diskName())
                ->visibility('public')->imageHeight(40);
        }

        return $table->columns($columns)->recordTitleAttribute('name')->searchPlaceholder('Rechercher')
            ->emptyStateHeading('Aucune localisation')
            ->headerActions([$this->createCatalogAction('Créer une localisation', static::$parentField)])
            ->recordActions([$this->editCatalogAction(), CatalogActions::delete()])
            ->defaultSort('name');
    }
}
