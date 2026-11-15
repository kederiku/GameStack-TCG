<?php

namespace App\Filament\Resources\Series;

use App\Filament\Resources\Series\Pages\CreateSeries;
use App\Filament\Resources\Series\Pages\EditSeries;
use App\Filament\Resources\Series\Pages\ListSeries;
use App\Filament\Resources\Series\RelationManagers\SeriesLocalizationsRelationManager;
use App\Filament\Resources\Series\Schemas\SeriesForm;
use App\Filament\Resources\Series\Tables\SeriesTable;
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
    protected static ?string $model = Series::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static ?string $modelLabel = 'série';

    protected static ?string $pluralModelLabel = 'séries';

    protected static ?string $navigationLabel = 'Séries';

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
            SeriesLocalizationsRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => ListSeries::route('/'),
            'create' => CreateSeries::route('/create'),
            'edit' => EditSeries::route('/{record}/edit'),
        ];
    }
}
