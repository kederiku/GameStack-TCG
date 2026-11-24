<?php

namespace App\Filament\Resources\Series\RelationManagers;

use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\CatalogRelationManager;
use App\Filament\Support\Catalog\CatalogTables;
use App\Models\SeriesImage;
use App\Services\CatalogImages;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ImagesRelationManager extends CatalogRelationManager
{
    protected static string $relationship = 'images';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('admin.translation.images');
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            CatalogFields::language()->disabledOn('edit'),
            CatalogFields::image(__('admin.series.image_label'), __('admin.series.image_help'))->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            CatalogTables::relatedName('language', __('admin.labels.language')),
            ImageColumn::make('image')->label(__('admin.labels.artwork'))->disk(fn (): string => CatalogImages::diskName())->visibility('public'),
        ])->recordTitle(fn (SeriesImage $record): string => CatalogLabels::language($record->language))
            ->headerActions([$this->createCatalogAction(__('admin.translation.add_image'), 'series_id')])
            ->recordActions([$this->editCatalogAction(), CatalogActions::delete()]);
    }
}
