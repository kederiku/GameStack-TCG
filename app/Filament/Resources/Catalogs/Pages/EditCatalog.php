<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Expansions\ExpansionResource;
use App\Filament\Resources\Games\GameResource;
use App\Filament\Resources\Series\SeriesResource;
use App\Filament\Support\Catalog\EditCatalogRecord;
use App\Models\Catalog;
use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

class EditCatalog extends EditCatalogRecord
{
    protected static string $resource = CatalogResource::class;

    /** @return list<Action> */
    protected function getHeaderActions(): array
    {
        $catalogue = $this->getRecord();
        assert($catalogue instanceof Catalog);

        return [
            Action::make('series')->label(__('admin.labels.view-series'))->icon(Heroicon::OutlinedBookOpen)
                ->visible(fn (): bool => SeriesResource::canAccess())
                ->url(SeriesResource::getUrl(parameters: ['filters' => ['catalog' => ['value' => $catalogue->id]]])),
            Action::make('expansions')->label(__('admin.labels.view-expansions'))->icon(Heroicon::OutlinedRectangleStack)
                ->visible(fn (): bool => ExpansionResource::canAccess())
                ->url(ExpansionResource::getUrl(parameters: ['filters' => ['catalog' => ['value' => $catalogue->id]]])),
            Action::make('game')->label(__('admin.labels.back-to-game'))->color('gray')->icon(Heroicon::OutlinedArrowUturnLeft)
                ->visible(fn (): bool => GameResource::canEdit($catalogue->game))
                ->url(GameResource::getUrl('edit', ['record' => $catalogue->game_id])),
            ...parent::getHeaderActions(),
        ];
    }
}
