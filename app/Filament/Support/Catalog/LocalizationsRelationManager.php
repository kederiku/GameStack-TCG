<?php

namespace App\Filament\Support\Catalog;

use App\Services\CatalogImages;
use Filament\Forms\Components\FileUpload;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

abstract class LocalizationsRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'localizations';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.labels.localizations');
    }

    protected static string $parentField;

    public function form(Schema $schema): Schema
    {
        return CatalogFields::layout($schema, [
            CatalogFields::language()->disabledOn('edit')
                ->helperText(__('admin.localizations.language_help')),
            CatalogFields::name(__('admin.labels.localized-name'))->helperText(__('admin.localizations.name_help')),
            CatalogFields::description(),
        ], image: $this->imageField(), description: __('admin.localizations.help'));
    }

    protected function imageField(): ?FileUpload
    {
        return null;
    }

    public function table(Table $table): Table
    {
        $columns = [
            CatalogTables::name(__('admin.labels.localized-name')),
            TextColumn::make('language.name')->label(__('admin.labels.language'))->searchable(),
            TextColumn::make('language.code')->label(__('admin.labels.code'))->badge()->searchable(),
        ];

        if ($this->imageField() !== null) {
            $columns[] = ImageColumn::make('image')->label(__('admin.labels.artwork'))->disk(fn (): string => CatalogImages::diskName())
                ->visibility('public')->imageHeight(40);
        }

        return $table->columns($columns)->recordTitleAttribute('name')->searchPlaceholder(__('admin.labels.search'))
            ->description(__('admin.localizations.description'))
            ->emptyStateHeading(__('admin.labels.no-localizations'))
            ->emptyStateDescription(__('admin.localizations.empty_help'))
            ->headerActions([$this->createCatalogAction(__('admin.labels.create-a-localization'), static::$parentField)])
            ->recordActions([$this->editCatalogAction(), CatalogActions::delete()])
            ->defaultSort('name');
    }
}
