<?php

namespace App\Filament\Support\Catalog;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

abstract class ListCatalogRecords extends ListRecords
{
    /** @return list<CreateAction> */
    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label(__('admin.labels.create'))];
    }
}
