<?php

namespace App\Filament\Support\Catalog;

use App\Models\CatalogRecord;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Validation\ValidationException;

abstract class CreateCatalogRecord extends CreateRecord
{
    public static bool $formActionsAreSticky = true;

    /** @param array<string, mixed> $data */
    protected function handleRecordCreation(array $data): CatalogRecord
    {
        $model = static::getResource()::getModel();
        $record = new $model;
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
        return __('admin.record_titles.create', ['resource' => static::getResource()::getTitleCaseModelLabel()]);
    }

    public function getBreadcrumb(): string
    {
        return __('admin.labels.create');
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()->label(__('admin.labels.create'));
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->label(__('admin.labels.cancel'));
    }

    protected function getCreatedNotificationTitle(): string
    {
        return __('admin.labels.record-created');
    }

    public function canCreateAnother(): bool
    {
        return false;
    }
}
