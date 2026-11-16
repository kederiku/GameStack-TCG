<?php

namespace App\Filament\Resources\Series\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\EditCatalogRecord;
use App\Models\Series;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditSeries extends EditCatalogRecord
{
    protected static string $resource = SeriesResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        $series = $this->getRecord();
        assert($series instanceof Series);

        return [
            Action::make('expansions')->label(__('admin.labels.view-expansions'))->icon(Heroicon::OutlinedRectangleStack)
                ->visible(fn (): bool => ExpansionResource::canAccess())
                ->url(ExpansionResource::getUrl(parameters: ['filters' => [
                    'catalog' => ['value' => $series->catalog_id],
                    'series' => ['value' => $series->id],
                ]])),
            Action::make('catalog')->label(__('admin.labels.back-to-catalog'))->color('gray')->icon(Heroicon::OutlinedArrowUturnLeft)
                ->visible(fn (): bool => CatalogResource::canEdit($series->catalog))
                ->url(CatalogResource::getUrl('edit', ['record' => $series->catalog_id])),
            ...parent::getHeaderActions(),
        ];
    }
}
