<?php

namespace App\Filament\Support\Catalog;

use App\Models\CatalogRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

abstract class EditCatalogRecord extends EditRecord
{
    public static bool $formActionsAreSticky = true;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [CatalogActions::delete()];
    }

    /** @param array<string, mixed> $data */
    protected function handleRecordUpdate(Model $record, array $data): CatalogRecord
    {
        assert($record instanceof CatalogRecord);

        try {
            return CatalogActions::save($record, $data, 'data');
        } catch (ValidationException $exception) {
            // Domain validation runs after the schema, so reveal its errors too.
            $this->dispatch('form-validation-error', livewireId: $this->getId());

            throw $exception;
        }
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
