<?php

namespace App\Filament\Resources\Expansions;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\Expansions\Pages\CreateExpansion;
use App\Filament\Resources\Expansions\Pages\EditExpansion;
use App\Filament\Resources\Expansions\Pages\ListExpansions;
use App\Filament\Resources\Expansions\RelationManagers\EditionsRelationManager;
use App\Filament\Resources\Expansions\RelationManagers\RelationsRelationManager;
use App\Filament\Resources\Expansions\Schemas\ExpansionForm;
use App\Filament\Resources\Expansions\Tables\ExpansionsTable;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\Expansion;
use BackedEnum;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/** @extends resource<Expansion> */
class ExpansionResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'expansions';

    protected static ?string $model = Expansion::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 40;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ExpansionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ExpansionsTable::configure($table);
    }

    /** @return list<class-string<RelationManager>> */
    public static function getRelations(): array
    {
        return [
            EditionsRelationManager::class,
            RelationsRelationManager::class,
        ];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return [
            'index' => LocalizedPageRegistration::make(ListExpansions::class, 'index'),
            'create' => LocalizedPageRegistration::make(CreateExpansion::class, 'create'),
            'edit' => LocalizedPageRegistration::make(EditExpansion::class, 'edit'),
        ];
    }
}
