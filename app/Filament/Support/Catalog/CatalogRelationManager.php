<?php

namespace App\Filament\Support\Catalog;

use App\Models\CatalogRecord;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Components\Attributes\Unexposed;
use Illuminate\Support\Facades\Gate;

abstract class CatalogRelationManager extends RelationManager
{
    #[Unexposed]
    public function mount(): void
    {
        Gate::authorize('view', $this->getOwnerRecord());

        parent::mount();
    }

    protected function createCatalogAction(string $label, string $parentField): CreateAction
    {
        return CreateAction::make()
            ->label($label)
            ->modalHeading($label)
            ->modalSubmitActionLabel(__('admin.labels.create'))
            ->modalCancelActionLabel(__('admin.labels.cancel'))
            ->successNotificationTitle(__('admin.labels.record-created'))
            ->createAnother(false)
            ->using(function (CreateAction $action, array $data) use ($parentField): CatalogRecord {
                Gate::authorize('update', $this->getOwnerRecord());

                $record = $this->getRelationship()->getModel()->newInstance();
                assert($record instanceof CatalogRecord);

                return CatalogActions::save($record, [
                    ...$data,
                    $parentField => $this->getOwnerRecord()->getKey(),
                ], $this->actionStatePath($action));
            });
    }

    protected function editCatalogAction(): EditAction
    {
        return EditAction::make()
            ->label(__('admin.labels.edit'))
            ->modalHeading(__('admin.labels.edit-record'))
            ->modalSubmitActionLabel(__('admin.labels.save'))
            ->modalCancelActionLabel(__('admin.labels.cancel'))
            ->successNotificationTitle(__('admin.labels.record-saved'))
            ->using(function (EditAction $action, CatalogRecord $record, array $data): CatalogRecord {
                Gate::authorize('update', $this->getOwnerRecord());

                return CatalogActions::save($record, $data, $this->actionStatePath($action));
            });
    }

    private function actionStatePath(Action $action): string
    {
        return $this->getMountedActionSchema(mountedAction: $action)?->getStatePath() ?? '';
    }
}
