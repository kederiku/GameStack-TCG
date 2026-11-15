<?php

namespace App\Filament\Support\Catalog;

use App\Models\CatalogRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

abstract class CreateCatalogRecord extends CreateRecord
{
    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): CatalogRecord
    {
        $model = static::getResource()::getModel();
        $record = new $model;
        assert($record instanceof CatalogRecord);

        return CatalogActions::save($record, $data, 'data');
    }

    public function getTitle(): string
    {
        return 'Créer · '.static::getResource()::getTitleCaseModelLabel();
    }

    public function getBreadcrumb(): string
    {
        return 'Créer';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label('Créer');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label('Annuler');
    }

    protected function getCreatedNotificationTitle(): string
    {
        return 'Fiche créée';
    }

    public function canCreateAnother(): bool
    {
        return false;
    }
}
