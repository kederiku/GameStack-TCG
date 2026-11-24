<?php

namespace App\Filament\Resources\Series;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\Series\Pages\CreateSeries;
use App\Filament\Resources\Series\Pages\EditSeries;
use App\Filament\Resources\Series\Pages\ListSeries;
use App\Filament\Resources\Series\RelationManagers\ImagesRelationManager;
use App\Filament\Resources\Series\Schemas\SeriesForm;
use App\Filament\Resources\Series\Tables\SeriesTable;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\Series;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** @extends resource<Series> */
class SeriesResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'series';

    protected static ?string $model = Series::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?int $navigationSort = 30;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return SeriesForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeriesTable::configure($table);
    }

    /** @return list<class-string<RelationManager>> */
    public static function getRelations(): array
    {
        return [
            ImagesRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => LocalizedPageRegistration::make(ListSeries::class, 'index'),
            'create' => LocalizedPageRegistration::make(CreateSeries::class, 'create'),
            'edit' => LocalizedPageRegistration::make(EditSeries::class, 'edit'),
        ];
    }
}
