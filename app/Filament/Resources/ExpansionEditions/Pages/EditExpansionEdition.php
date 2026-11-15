<?php

namespace App\Filament\Resources\ExpansionEditions\Pages;

use App\Filament\Resources\ExpansionEditions\ExpansionEditionResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\EditCatalogRecord;
use App\Models\ExpansionEdition;
use Filament\Actions\Action;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;

class EditExpansionEdition extends EditCatalogRecord
{
    protected static string $resource = ExpansionEditionResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('expansion')->label('Retour à l’extension')->icon(Heroicon::OutlinedArrowUturnLeft)
                ->url($this->expansionUrl()),
            CatalogActions::delete()->successRedirectUrl($this->expansionUrl()),
        ];
    }

    public function getSubheading(): string
    {
        $edition = $this->getRecord();
        assert($edition instanceof ExpansionEdition);

        return $edition->expansion->name.' — '.CatalogFields::catalogueLabel($edition->expansion->catalog);
    }

    /** @return array<string> */
    #[Unexposed]
    public function getBreadcrumbs(): array
    {
        $edition = $this->getRecord();
        assert($edition instanceof ExpansionEdition);

        return [
            ExpansionResource::getUrl() => 'Extensions et ensembles',
            $this->expansionUrl() => $edition->expansion->name,
            'Édition',
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url($this->expansionUrl());
    }

    protected function afterSave(): void
    {
        $this->refreshFormData(['image']);
    }

    private function expansionUrl(): string
    {
        $edition = $this->getRecord();
        assert($edition instanceof ExpansionEdition);

        return ExpansionResource::getUrl('edit', ['record' => $edition->expansion_id]);
    }
}
