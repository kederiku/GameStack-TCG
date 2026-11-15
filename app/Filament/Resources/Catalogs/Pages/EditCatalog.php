<?php

namespace App\Filament\Resources\Catalogs\Pages;

use App\Filament\Resources\Catalogs\CatalogResource;
use App\Filament\Resources\Games\GameResource;
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
            Action::make('game')->label('Retour au jeu')->icon(Heroicon::OutlinedArrowUturnLeft)
                ->visible(fn (): bool => GameResource::canEdit($catalogue->game))
                ->url(GameResource::getUrl('edit', ['record' => $catalogue->game_id])),
            ...parent::getHeaderActions(),
        ];
    }
}
