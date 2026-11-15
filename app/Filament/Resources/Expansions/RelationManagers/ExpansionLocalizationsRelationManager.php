<?php

namespace App\Filament\Resources\Expansions\RelationManagers;

use App\Filament\Support\Catalog\LocalizationsRelationManager;

class ExpansionLocalizationsRelationManager extends LocalizationsRelationManager
{
    protected static string $parentField = 'expansion_id';
}
