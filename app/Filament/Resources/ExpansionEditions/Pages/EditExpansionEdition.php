<?php

namespace App\Filament\Resources\ExpansionEditions\Pages;

use App\Filament\Resources\ExpansionEditions\ExpansionEditionResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Support\Catalog\CatalogActions;
use App\Filament\Support\Catalog\CatalogFields;
use App\Filament\Support\Catalog\CatalogLabels;
use App\Filament\Support\Catalog\EditCatalogRecord;
use App\Models\ExpansionEdition;
use Filament\Actions\Action;
use Filament\Support\Components\Attributes\Unexposed;
use Filament\Support\Icons\Heroicon;
use Livewire\Attributes\On;

class EditExpansionEdition extends EditCatalogRecord
{
    protected static string $resource = ExpansionEditionResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('expansion')->label(__('admin.labels.back-to-expansion'))->icon(Heroicon::OutlinedArrowUturnLeft)
                ->url($this->expansionUrl()),
            CatalogActions::delete()->successRedirectUrl($this->expansionUrl()),
        ];
    }

    public function getSubheading(): string
    {
        $edition = $this->getRecord();
        assert($edition instanceof ExpansionEdition);

        return CatalogLabels::name($edition->expansion).' — '.CatalogFields::catalogueLabel($edition->expansion->catalog);
    }

    /** @return array<string> */
    #[Unexposed]
    public function getBreadcrumbs(): array
    {
        $edition = $this->getRecord();
        assert($edition instanceof ExpansionEdition);

        return [
            ExpansionResource::getUrl() => __('admin.labels.expansions-and-sets'),
            $this->expansionUrl() => CatalogLabels::name($edition->expansion),
            __('admin.labels.edition'),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()->url($this->expansionUrl());
    }

    #[On('expansion-edition-status-changed')]
    public function refreshStatusAfterReleaseChange(): void
    {
        $this->getRecord()->refresh();
        $this->refreshFormData(['status']);
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
