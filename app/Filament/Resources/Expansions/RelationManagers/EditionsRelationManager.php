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
use Illuminate\Database\Eloquent\Model;

class EditionsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'editions';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.labels.editions');
    }

    public function form(Schema $schema): Schema
    {
        return ExpansionEditionForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return $table->recordTitleAttribute('name')
            ->description(__('admin.editions.description'))
            ->searchPlaceholder(__('admin.labels.search'))
            ->emptyStateHeading(__('admin.labels.no-editions'))
            ->emptyStateDescription(__('admin.editions.empty_help'))
            ->columns([
                ImageColumn::make('image')->label(__('admin.labels.artwork'))->disk(fn (): string => CatalogImages::diskName())
                    ->visibility('public')->imageHeight(40),
                CatalogTables::name(__('admin.labels.edition')),
                TextColumn::make('language.code')->label(__('admin.labels.language'))->badge()->searchable(),
                TextColumn::make('official_code')->label(__('admin.labels.official-code'))->searchable(),
                CatalogTables::enum('status', __('admin.labels.status'), CatalogLabels::statuses()),
                CatalogTables::visibility(),
            ])
            ->defaultSort('name')
            ->headerActions([$this->createCatalogAction(__('admin.labels.create-an-edition'), 'expansion_id')])
            ->recordActions([
                EditAction::make()->label(__('admin.editions.manage_releases'))
                    ->url(fn (ExpansionEdition $record): string => ExpansionEditionResource::getUrl('edit', ['record' => $record]))
                    ->visible(fn (ExpansionEdition $record): bool => ExpansionEditionResource::canEdit($record)),
                CatalogActions::delete(),
            ]);
    }
}
