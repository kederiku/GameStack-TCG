<?php

namespace App\Filament\Support\Catalog;

use App\Actions\Catalog\SaveCatalogRecord;
use App\Models\CatalogRecord;
use App\Models\Game;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Throwable;

class CatalogActions
{
    /** @param array<string, mixed> $data */
    public static function save(CatalogRecord $record, array $data, string $statePath): CatalogRecord
    {
        if ($record->exists) {
            Gate::authorize('update', $record);
        } else {
            Gate::authorize('create', $record::class);
        }

        try {
            $saved = app(SaveCatalogRecord::class)->handle($record, $data);
        } catch (ValidationException $exception) {
            $messages = [];
            foreach ($exception->errors() as $field => $errors) {
                $messages[$statePath.'.'.$field] = $errors;
            }

            throw ValidationException::withMessages($messages);
        }

        if (! $record->exists) {
            return $saved;
        }

        $record->setRawAttributes($saved->getAttributes(), sync: true);
        $record->unsetRelations();

        return $record;
    }

    public static function delete(): DeleteAction
    {
        return DeleteAction::make()
            ->label(__('admin.labels.delete'))
            ->color('gray')
            ->outlined()
            ->modalHeading(__('admin.labels.delete-this-record'))
            ->modalSubmitActionLabel(__('admin.labels.delete'))
            ->modalCancelActionLabel(__('admin.labels.cancel'))
            ->successNotificationTitle(__('admin.labels.record-deleted'))
            ->using(static function (CatalogRecord|Game $record): ?bool {
                Gate::authorize('delete', $record);

                try {
                    return $record->delete();
                } catch (Throwable $exception) {
                    if (! $exception instanceof ValidationException) {
                        throw $exception;
                    }

                    Notification::make()->danger()->title($exception->getMessage())->send();

                    return false;
                }
            });
    }

    public static function deleteBulk(): DeleteBulkAction
    {
        return DeleteBulkAction::make()
            ->label(__('admin.labels.delete-selection'))
            ->modalHeading(__('admin.records.delete_selection_confirm'))
            ->modalSubmitActionLabel(__('admin.labels.delete'))
            ->modalCancelActionLabel(__('admin.labels.cancel'))
            ->successNotificationTitle(__('admin.labels.selection-deleted'))
            ->failureNotificationTitle(__('admin.records.partial_delete_failure'))
            ->authorizeIndividualRecords('delete')
            ->using(static function (DeleteBulkAction $action, Collection $records): void {
                Gate::authorize('deleteAny', $action->getModel());

                $reported = false;
                foreach ($records as $record) {
                    try {
                        $record->delete() || $action->reportBulkProcessingFailure();
                    } catch (ValidationException $exception) {
                        $message = $exception->getMessage();
                        $action->reportBulkProcessingFailure($message, $message);
                    } catch (Throwable $exception) {
                        $action->reportBulkProcessingFailure();

                        if (! $reported) {
                            report($exception);
                            $reported = true;
                        }
                    }
                }
            });
    }
}
