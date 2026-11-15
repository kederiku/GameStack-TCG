<?php

namespace App\Filament\Support\Catalog;

use App\Models\CatalogRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

abstract class EditCatalogRecord extends EditRecord
{
    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [CatalogActions::delete()];
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): CatalogRecord
    {
        assert($record instanceof CatalogRecord);

        return CatalogActions::save($record, $data, 'data');
    }

    public function getTitle(): string
    {
        return 'Modifier · '.static::getResource()::getRecordTitle($this->getRecord());
    }

    public function getBreadcrumb(): string
    {
        return 'Modifier';
    }

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()->label('Enregistrer');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Annuler');
    }

    protected function getSavedNotificationTitle(): string
    {
        return 'Fiche enregistrée';
    }
}
