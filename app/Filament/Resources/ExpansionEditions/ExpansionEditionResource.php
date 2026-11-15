<?php

namespace App\Filament\Resources\ExpansionEditions;

use App\Filament\Resources\ExpansionEditions\Pages\EditExpansionEdition;
use App\Filament\Resources\ExpansionEditions\RelationManagers\ReleasesRelationManager;
use App\Filament\Resources\ExpansionEditions\Schemas\ExpansionEditionForm;
use App\Models\ExpansionEdition;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

/** @extends resource<ExpansionEdition> */
class ExpansionEditionResource extends Resource
{
    protected static ?string $model = ExpansionEdition::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $modelLabel = 'édition';

    protected static ?string $pluralModelLabel = 'éditions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return ExpansionEditionForm::configure($schema);
    }

    /** @return list<class-string<RelationManager>> */
    public static function getRelations(): array
    {
        return [ReleasesRelationManager::class];
    }

    /** @return array<string, PageRegistration> */
    public static function getPages(): array
    {
        return ['edit' => EditExpansionEdition::route('/{record}/edit')];
    }
}
