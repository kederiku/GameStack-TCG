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
            CatalogFields::language()->disabledOn('edit')
                ->helperText('Langue dans laquelle présenter la fiche. Une traduction par langue ; ce choix est définitif après création.'),
            CatalogFields::name('Nom localisé')->helperText('Nom à afficher dans cette langue. Traduire la présentation ne crée pas d’édition imprimée.'),
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
            ->description('Une localisation est une traduction de la présentation : nom, description et, pour une série, image. Elle ne prouve pas que des cartes existent dans cette langue.')
            ->emptyStateHeading('Aucune localisation')
            ->emptyStateDescription('Ajoutez les langues utiles à la présentation de cette fiche. Le nom de référence reste disponible sans traduction.')
            ->headerActions([$this->createCatalogAction('Créer une localisation', static::$parentField)])
            ->recordActions([$this->editCatalogAction(), CatalogActions::delete()])
            ->defaultSort('name');
    }
}
