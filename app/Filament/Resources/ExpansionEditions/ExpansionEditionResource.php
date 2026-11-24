<?php

namespace App\Filament\Resources\ExpansionEditions;

use App\Filament\Concerns\HasLocalizedRoutes;
use App\Filament\Resources\ExpansionEditions\Pages\EditExpansionEdition;
use App\Filament\Resources\ExpansionEditions\RelationManagers\ReleasesRelationManager;
use App\Filament\Resources\ExpansionEditions\Schemas\ExpansionEditionForm;
use App\Filament\Support\LocalizedPageRegistration;
use App\Models\ExpansionEdition;
use Filament\Resources\Pages\PageRegistration;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

/** @extends resource<ExpansionEdition> */
class ExpansionEditionResource extends Resource
{
    use HasLocalizedRoutes;

    protected static string $localizationKey = 'expansion-editions';

    protected static ?string $model = ExpansionEdition::class;

    protected static bool $shouldRegisterNavigation = false;

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
        return ['edit' => LocalizedPageRegistration::make(EditExpansionEdition::class, 'edit')];
    }
}
