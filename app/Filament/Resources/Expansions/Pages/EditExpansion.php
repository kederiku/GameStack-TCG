<?php

namespace App\Filament\Resources\Expansions\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\EditCatalogRecord;
use App\Models\Expansion;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditExpansion extends EditCatalogRecord
{
    protected static string $resource = ExpansionResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        $expansion = $this->getRecord();
        assert($expansion instanceof Expansion);

        return [
            Action::make('catalog')->label(__('admin.labels.back-to-catalog'))->color('gray')->icon(Heroicon::OutlinedArrowUturnLeft)
                ->visible(fn (): bool => CatalogResource::canEdit($expansion->catalog))
                ->url(CatalogResource::getUrl('edit', ['record' => $expansion->catalog_id])),
            Action::make('series')->label(__('admin.labels.view-series-record'))->color('gray')->icon(Heroicon::OutlinedBookOpen)
                ->visible(fn (): bool => $expansion->series !== null && SeriesResource::canEdit($expansion->series))
                ->url(fn (): ?string => $expansion->series_id === null ? null : SeriesResource::getUrl('edit', ['record' => $expansion->series_id])),
            ...parent::getHeaderActions(),
        ];
    }
}
